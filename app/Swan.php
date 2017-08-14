<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2017/8/13
 * Time: 上午10:45
 */


namespace App;

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

    public static function buildSendText($requestData)
    {
        if (!isset($requestData['text']) || !$requestData['text']) {
            return '';
        }

        $sendText = $requestData['text'];

        if (isset($requestData['desp']) && $requestData['desp']) {
            $sendText .= "\n内容：" . mb_strimwidth($requestData['desp'],
                    0,
                    env('SWAN_DESP_BRIEF_LENGTH', 50),
                    '...');
        }

        return $sendText;
    }
}
