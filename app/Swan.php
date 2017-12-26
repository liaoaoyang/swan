<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/8/13
 * Time: 上午10:45
 */


namespace App;

use App\Utils\AutoResponses\KeywordKey as AutoResponseKey;
use App\Utils\AutoResponses\KeywordStart as AutoResponseStart;
use App\Utils\AutoResponses\KeywordStop as AutoResponseStop;
use App\Utils\AutoResponses\KeywordKey as AutoResponseDefault;

class Swan
{
    const TABLE_SWAN_KEY_OPENID_MAP = 'swan_key_openid_map';
    const TABLE_SWAN_MESSAGE        = 'swan_message';

    const SESSION_KEY_SWAN_USER        = 'swan_user';
    const SESSION_KEY_OAUTH_TARGET_URL = 'swan_oauth_target_url';
    const SESSION_KEY_PUSH_KEY         = 'swan_push_key';

    const OAUTH_BASE_CALLBACK_URL = '/wechat/swan/oauth/base/callback';
    const MY_KEY_URL              = '/wechat/swan/mykey';
    const API_SEND_URL            = '/wechat/swan/{key}.send';
    const DETAIL_URL              = '/wechat/swan/detail/{id}';

    private static $autoResponses = [];

    public static function loadEasyWeChatConfig()
    {
        self::reloadDotEnv();

        $options = [
            'debug'  => true,
            'app_id' => env('WECHAT_APP_ID'),
            'secret' => env('WECHAT_APP_SECRET'),
            'token'  => env('WECHAT_TOKEN'),
            'oauth'  => [
                'scopes'   => array_map('trim', explode(',', env('WECHAT_OAUTH_SCOPES', 'snsapi_base'))),
                'callback' => env('WECHAT_OAUTH_CALLBACK', Swan::OAUTH_BASE_CALLBACK_URL),
            ],
            'log'    => [
                'level' => env('EASY_WECHAT_LOG_LEVEL', 'error'),
                'file'  => env('EASY_WECHAT_LOG_PATH', '/tmp/easywechat.log'),
            ],
        ];

        return $options;
    }

    public static function buildAutoResponseChain($forced = false)
    {
        if ($forced || !self::$autoResponses) {
            self::$autoResponses[] = new AutoResponseKey();
            self::$autoResponses[] = new AutoResponseStart();
            self::$autoResponses[] = new AutoResponseStop();
            self::$autoResponses[] = new AutoResponseDefault();
        }

        return self::$autoResponses;
    }

    public static function getWeChatTemplateId()
    {
        self::reloadDotEnv();

        return env('WECHAT_TEMPLATE_ID');
    }

    public static function reloadDotEnv($path = null)
    {
        if (null === $path) {
            $path = base_path();
        }

        $dotEnv = new \Dotenv\Dotenv($path);
        $dotEnv->load();
    }

    /**
     * Generate push key by openssl_random_pseudo_bytes()
     *
     * @param int $keyLength
     * @param int $randomBytes
     * @return bool|mixed|string
     */
    public static function generatePushKey($keyLength = 20, $randomBytes = 10)
    {
        $pushKey = base64_encode(openssl_random_pseudo_bytes($randomBytes));
        $pushKey = preg_replace("#[/+=]#", '', $pushKey);
        $pushKey = substr($pushKey, 0, $keyLength);

        while (strlen($pushKey) < $keyLength) {
            $randomChars = [
                chr(rand(0, 9) + ord('0')),
                chr(rand(0, 25) + ord('a')),
                chr(rand(0, 25) + ord('A')),
            ];
            $pushKey = $pushKey . $randomChars[rand(0, 2)];
        }

        return $pushKey;
    }

    public static function getDatabaseIDColumnName()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ('mysql' == $dbConnection) {
            return 'id';
        } else if ('mongodb' == $dbConnection) {
            return '_id';
        }

        return 'id';
    }

    public static function buildSendData($requestData)
    {
        $sendData = [];

        $textFieldKey = env('SWAN_TEMPLATE_TEXT_KEY', 'keyword1');
        $sendData[$textFieldKey] = isset($requestData['text']) ? $requestData['text'] : '无';
        $despFieldKey = env('SWAN_TEMPLATE_DESP_KEY', 'remark');
        $sendData[$despFieldKey] = '';

        if (isset($requestData['desp']) && $requestData['desp']) {
            $sendData[$despFieldKey] = mb_strimwidth($requestData['desp'],
                0,
                env('SWAN_DESP_BRIEF_LENGTH', 50),
                '...');
        }

        $autoFillTimeKeys = explode(',', env('SWAN_TEMPLATE_AUTO_FILL_TIME_KEYS', ''));

        foreach ($autoFillTimeKeys as $autoFillTimeKey) {
            $sendData[$autoFillTimeKey] = date('Y-m-d H:i:s');
        }

        return $sendData;
    }

    public static function convertToDatabaseDatetimeString($dateTimeString)
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ('mongodb' == $dbConnection) {
            return new \DateTime($dateTimeString);
        }

        return $dateTimeString;
    }

    /**
     * 请求消息基本属性
     *
     * $message->ToUserName    接收方帐号（该公众号 ID）
     * $message->FromUserName  发送方帐号（OpenID, 代表用户的唯一标识）
     * $message->CreateTime    消息创建时间（时间戳）
     * $message->MsgId         消息 ID（64位整型）
     *
     * @param \EasyWeChat\Foundation\Application $weChatApp
     * @param $message
     * @return string
     */
    public static function autoResponseNewFollow($weChatApp, $message)
    {
        return urldecode(env('SWAN_WECHAT_NEW_SUBSCRIBE_RESPONSE_TEXT_URLENCODE', urlencode('欢迎关注我')));
    }

    /**
     * 文本
     *
     * $message->MsgType  text
     * $message->Content  文本消息内容
     *
     * @param \EasyWeChat\Foundation\Application $weChatApp
     * @param $message
     * @return string
     */
    public static function autoResponseKeywords($weChatApp, $message)
    {
        if ($message->MsgType != 'text') {
            return '';
        }

        self::buildAutoResponseChain();

        $autoResponseText = '';

        foreach (self::$autoResponses as $autoResponse) {
            /**
             * @var \App\Utils\AutoResponses\DefaultResponse $autoResponse
             */
            $autoResponseText = $autoResponse->getResponse($weChatApp, $message);

            if ($autoResponseText !== false) {
                break;
            }
        }

        return $autoResponseText;
    }
}
