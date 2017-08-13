<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/8/8
 * Time: 下午10:14
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use EasyWeChat\Foundation\Application as WeChatApplication;
use Validator;
use App\Swan;
use App\Models\SwanKeyOpenidMapModel;
use App\Models\SwanMessageModel;

class WeChatController extends BaseController
{
    /**
     * @var WeChatApplication $weChatApp
     */
    protected $weChatApp = null;

    function __construct()
    {
        $this->weChatApp = new WeChatApplication(Swan::loadEasyWeChatConfig());
    }

    public function serve()
    {
        return $this->weChatApp->server->serve();
    }

    public function myKey()
    {
        $swanUser = session(Swan::SESSION_KEY_SWAN_USER);

        if (!$swanUser) {
            session([
                Swan::SESSION_KEY_OAUTH_TARGET_URL => Swan::MY_KEY_URL
            ]);
            return $this->weChatApp->oauth->redirect();
        }

        $swanKeyOpenidMapModel = new SwanKeyOpenidMapModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['openid' => $swanUser['id']])->first();

        if (!$swanKeyOpenidMap) {
            $swanKeyOpenidMap = new SwanKeyOpenidMapModel();
            $swanKeyOpenidMap->key = Swan::generatePushKey();
            $swanKeyOpenidMap->openid = $swanUser['id'];

            if (!$swanKeyOpenidMap->save()) {
                return view('swan/mykey', [
                    'key'        => '未能获取推送key，请重试',
                    'updated_at' => 'N/A',
                    'retry_url'  => request()->fullUrl(),
                ]);
            }
        }

        $weChatUserInfo = $this->weChatApp->user->get($swanUser['id']);

        if (!$weChatUserInfo) {
            return view('swan/mykey', [
                'key'        => '未能获取用户信息，请重试',
                'updated_at' => 'N/A',
                'retry_url'  => request()->fullUrl(),
            ]);
        }

        if (!$weChatUserInfo->subscribe) {
            return view('swan/subscribe_first', [
                'subscribe_url' => env('WECHAT_SUBSCRIBE_URL'),
                'subscribe_qrcode_url' => env('WECHAT_SUBSCRIBE_QRCODE_URL')
            ]);
        }

        session([
            Swan::SESSION_KEY_OAUTH_TARGET_URL => '',
            Swan::SESSION_KEY_PUSH_KEY => $swanKeyOpenidMap->key,
        ]);

        return view('swan/mykey', [
            'key'        => $swanKeyOpenidMap->key,
            'updated_at' => $swanKeyOpenidMap->updated_at,
        ]);
    }

    public function swanOauthBaseScopeCallback()
    {
        $swanUser = $this->weChatApp->oauth->user();

        if (!$swanUser) {
            return response('未能获取用户信息', 500);
        }

        session([
            Swan::SESSION_KEY_SWAN_USER => $swanUser->toArray()
        ]);

        $targetUrl = session(Swan::SESSION_KEY_OAUTH_TARGET_URL);

        if (!$targetUrl) {
            $targetUrl = '/';
        }

        return redirect($targetUrl);
    }

    public function send($key)
    {
        $requestData = request()->all();
        $requestData['key'] = $key;

        $validator = Validator::make($requestData, [
            'key' => 'required|between:1,255',
            'text' => 'required|between:1,255',
            'desp' => 'between:1,64000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Failed to pass validator'
            ])->setStatusCode(403);
        }

        $swanKeyOpenidMapModel = new SwanKeyOpenidMapModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['key' => $requestData['key']])->first();

        if (!$swanKeyOpenidMap) {
            return response()->json([
                'error' => 'No such user exists'
            ])->setStatusCode(404);
        }

        $templateId = Swan::getWeChatTemplateId();

        if (!$templateId) {
            return response()->json([
                'error' => 'No template available'
            ])->setStatusCode(500);
        }

        $sendText =  $requestData['text'];

        if (isset($requestData['desp']) && $requestData['desp']) {
            $sendText .= "\n内容：" . mb_strimwidth($requestData['desp'],
                    0,
                    env('SWAN_DESP_BRIEF_LENGTH', 50),
                    '...');
        }

        $data = [
            'text' => $sendText,
        ];

        $swanMessage = new SwanMessageModel();
        $swanMessage->openid = $swanKeyOpenidMap->openid;
        $swanMessage->text = $requestData['text'];
        $swanMessage->desp = isset($requestData['desp']) && $requestData['desp'] ? $requestData['desp'] : '';

        if (!$swanMessage->save()) {
            return response()->json([
                'error' => 'Failed to save message'
            ])->setStatusCode(500);
        }

        $messageId = $swanMessage->id;

        if (!$this->weChatApp->notice->to($swanKeyOpenidMap->openid)
            ->uses($templateId)
            ->withUrl(request()->root() . "/wechat/swan/detail/{$messageId}")
            ->withData($data)
            ->send()) {
            return response()->json([
                'error' => 'Failed to request WeChat API'
            ])->setStatusCode(500);
        }

        return response()->json([
            'msg' => 'SUCCESS'
        ]);
    }

    public function detail($id)
    {
        $requestData = request()->all();
        $requestData['id'] = $id;

        $validator = Validator::make($requestData, [
            'id' => 'required|between:1,255',
        ]);

        if ($validator->fails()) {
            return view('swan/detail', [
                'text' => '参数错误',
            ]);
        }

        $swanUser = session(Swan::SESSION_KEY_SWAN_USER);

        if (!$swanUser) {
            session([
                Swan::SESSION_KEY_OAUTH_TARGET_URL => request()->fullUrl()
            ]);
            return $this->weChatApp->oauth->redirect();
        }

        $swanMessageModel = new SwanMessageModel();
        $swanMessage = $swanMessageModel->where([
            'id' => $requestData['id'],
            'openid' => $swanUser['id'],
        ])->first();

        if (!$swanMessage) {
            return view('swan/detail', [
                'text' => '没有这条消息',
            ]);
        }

        $swanMessage['desp'] = preg_replace_callback("#(<script>.+?</script>)#", function ($mat) {
            return htmlentities($mat[1]);
        }, $swanMessage['desp']);

        $converter = new \League\CommonMark\CommonMarkConverter();

        return view('swan/detail', [
            'text' => htmlentities($swanMessage['text']),
            'desp' => $converter->convertToHtml($swanMessage['desp']),
        ]);
    }

    public function userinfo()
    {
        $swanUser = session(Swan::SESSION_KEY_SWAN_USER);

        if (!$swanUser) {
            return 'No session data';
        }

        $userinfo = $this->weChatApp->user->get($swanUser['id']);
        var_dump($userinfo);
    }

    public function logout()
    {
        session()->flush();
    }
}