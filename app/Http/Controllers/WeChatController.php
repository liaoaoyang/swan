<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/8/8
 * Time: 下午10:14
 */

namespace App\Http\Controllers;

use App\Events\SendAlertEvent;
use App\Utils\MyWXTAuth;
use EasyWeChat\Core\Exceptions\InvalidArgumentException;
use Illuminate\Routing\Controller as BaseController;
use EasyWeChat\Foundation\Application as WeChatApplication;
use Validator;
use Log;
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
        $weChatApp = $this->weChatApp;

        try {
            $weChatApp->server->setMessageHandler(function ($message) use ($weChatApp) {
                switch ($message->MsgType) {
                    case 'text':
                        return Swan::autoResponseKeywords($weChatApp, $message);

                    case 'event':
                        return Swan::autoResponseEvent($weChatApp, $message);

                    default:
                        return Swan::autoResponseNewFollow($weChatApp, $message);
                }
            });
        } catch (InvalidArgumentException $e) {
            Log::error($e->getMessage());
        }
    }

    public function serve()
    {
        $response = $this->weChatApp->server->serve();
        $response->send();
    }

    public function myKey()
    {
        $swanUser = session(Swan::SESSION_KEY_SWAN_USER);

        if (!$swanUser) {
            session([
                Swan::SESSION_KEY_OAUTH_TARGET_URL => Swan::MY_KEY_URL,
            ]);
            return $this->weChatApp->oauth->redirect();
        }

        $swanKeyOpenidMap = SwanKeyOpenidMapModel::getKey($this->weChatApp, $swanUser['id']);

        if ($swanKeyOpenidMap === SwanKeyOpenidMapModel::RESPONSE_GET_KEY_FAILED_TO_SAVE) {
            return view('swan/mykey', [
                'key'        => '未能获取推送key，请重试',
                'updated_at' => 'N/A',
                'retry_url'  => request()->fullUrl(),
            ]);
        } else if ($swanKeyOpenidMap === SwanKeyOpenidMapModel::RESPONSE_GET_KEY_NO_USER_INFO) {
            return view('swan/mykey', [
                'key'        => '未能获取用户信息，请重试',
                'updated_at' => 'N/A',
                'retry_url'  => request()->fullUrl(),
            ]);
        } else if ($swanKeyOpenidMap === SwanKeyOpenidMapModel::RESPONSE_GET_KEY_NOT_SUBSCRIBE) {
            return view('swan/subscribe_first', [
                'subscribe_url'        => env('WECHAT_SUBSCRIBE_URL'),
                'subscribe_qrcode_url' => env('WECHAT_SUBSCRIBE_QRCODE_URL'),
            ]);
        }

        session([
            Swan::SESSION_KEY_OAUTH_TARGET_URL => '',
            Swan::SESSION_KEY_PUSH_KEY         => $swanKeyOpenidMap->key,
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
            Swan::SESSION_KEY_SWAN_USER => $swanUser->toArray(),
        ]);

        if (MyWXTAuth::getBackUrl() && MyWXTAuth::getOnceEncryptSecret()) {
            $swanUserArray = $swanUser->toArray();
            $responseUrl = MyWXTAuth::generateResponseUrl([
                'id' => $swanUserArray['id'],
            ]);
            Log::info('From WX oauth callback, WXTAuth response url: ' . $responseUrl);

            return redirect($responseUrl);
        }

        $targetUrl = session(Swan::SESSION_KEY_OAUTH_TARGET_URL);

        if (!$targetUrl) {
            $targetUrl = '/';
        }

        return redirect($targetUrl);
    }

    public function send($key, $async = false)
    {
        $requestData = request()->all();
        $requestData['key'] = $key;

        $validator = Validator::make($requestData, [
            'key'  => 'required|between:1,255',
            'text' => 'required|between:1,255',
            'desp' => 'between:1,64000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Failed to pass validator',
            ])->setStatusCode(403);
        }

        if (!$async && isset($requestData['async']) && $requestData['async']) {
            $async = true;
        }

        $swanKeyOpenidMapModel = SwanKeyOpenidMapModel::createModel();
        $swanKeyOpenidMap = $swanKeyOpenidMapModel->where(['key' => $requestData['key']])->first();

        if (!$swanKeyOpenidMap) {
            return response()->json([
                'error' => 'No such user exists',
            ])->setStatusCode(404);
        }

        if ($swanKeyOpenidMap->status != SwanKeyOpenidMapModel::STATUS_ENABLED) {
            return response()->json([
                'error' => 'User disabled push',
            ])->setStatusCode(403);
        }

        $templateId = Swan::getWeChatTemplateId();

        if (!$templateId) {
            return response()->json([
                'error' => 'No template available',
            ])->setStatusCode(500);
        }

        $data = Swan::buildSendData($requestData);

        $swanMessage = SwanMessageModel::createModel();
        $swanMessage->openid = $swanKeyOpenidMap->openid;
        $swanMessage->text = $requestData['text'];
        $swanMessage->desp = isset($requestData['desp']) && $requestData['desp'] ? $requestData['desp'] : '';
        $swanMessage->request_ip = request()->getClientIp();

        if (!$swanMessage->save()) {
            return response()->json([
                'error' => 'Failed to save message',
            ])->setStatusCode(500);
        }

        $messageId = $swanMessage->id;

        if ($async) {
            if (!event(new SendAlertEvent($swanKeyOpenidMap->openid, $templateId, $messageId, $data))) {
                return response()->json([
                    'error' => 'Failed to queue the task',
                ])->setStatusCode(500);
            }
        } else {
            if (!$this->weChatApp->notice->to($swanKeyOpenidMap->openid)
                                         ->uses($templateId)
                                         ->withUrl(request()->root() . "/wechat/swan/detail/{$messageId}")
                                         ->withData($data)
                                         ->send()) {
                return response()->json([
                    'error' => 'Failed to request WeChat API',
                ])->setStatusCode(500);
            }
        }

        return response()->json([
            'msg' => 'SUCCESS',
        ]);
    }

    public function asyncSend($key)
    {
        return $this->send($key, true);
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
                Swan::SESSION_KEY_OAUTH_TARGET_URL => request()->fullUrl(),
            ]);
            return $this->weChatApp->oauth->redirect();
        }

        $swanMessageModel = SwanMessageModel::createModel();
        $swanMessage = $swanMessageModel->where([
            Swan::getDatabaseIDColumnName() => $requestData['id'],
            'openid'                        => $swanUser['id'],
        ])->first();

        if (!$swanMessage) {
            return view('swan/detail', [
                'text' => '没有这条消息',
            ]);
        }

        // XSS prevention
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
        if ('production' == env('APP_ENV', 'production')) {
            return response('N/A');
        }

        $swanUser = session(Swan::SESSION_KEY_SWAN_USER);

        if (!$swanUser) {
            return 'No session data';
        }

        $userinfo = $this->weChatApp->user->get($swanUser['id']);

        return view('swan/userinfo', [
            'infos' => $userinfo,
        ]);
    }

    public function logout()
    {
        session()->flush();

        return view('swan/logout');
    }

    public function wxtauth()
    {
        if (MyWXTAuth::handleAuthRequest()) {
            $swanUser = session(Swan::SESSION_KEY_SWAN_USER);
            $scope = request('scope', env('SWAN_DEFAULT_WECHAT_OAUTH_SCOPE', MyWXTAuth::WECHAT_OAUTH_SCOPE_SNSAPI_BASE));

            if (!$swanUser) {
                $oauthObj = $this->weChatApp->oauth;

                if (!in_array($scope, [MyWXTAuth::WECHAT_OAUTH_SCOPE_SNSAPI_BASE, MyWXTAuth::WECHAT_OAUTH_SCOPE_SNSAPI_USERINFO])) {
                    return view('swan/exception');
                }

                $oauthObj->scopes([$scope]);

                return $oauthObj->redirect();
            }

            $swanUser = is_array($swanUser) ? $swanUser : json_decode($swanUser, true);

            $responseData = [
                'openid'   => $swanUser['id'],
            ];

            if ($scope == MyWXTAuth::WECHAT_OAUTH_SCOPE_SNSAPI_USERINFO && isset($swanUser['original'])) {
                $responseData = $swanUser['original'];
            }

            $responseUrl = MyWXTAuth::generateResponseUrl($responseData);

            Log::info('From WXTAuth Server session, WXTAuth response url: ' . $responseUrl);
            return redirect($responseUrl);
        }

        return view('swan/exception');
    }
}