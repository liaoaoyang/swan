<?php

namespace App\Console\Commands;

use App\Models\SwanKeyOpenidMapModel;
use Illuminate\Console\Command;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;

class ListWeChatUsers extends Command
{
    const USER_INFO_PRINT_FORMAT = '%-24s%-32s%-10s';
    const SIGNATURE              = 'swan:list-wechat-users {page?} {pageSize?}';
    const DESCRIPTION            = 'List WeChat users by page / pageSize';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::SIGNATURE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = self::DESCRIPTION;

    /**
     * @var WeChatApplication $weChatApp
     */
    protected $weChatApp = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->weChatApp = new WeChatApplication(Swan::loadEasyWeChatConfig());
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $page = $this->argument('page');
        $pageSize = $this->argument('pageSize');

        $page = $page ? $page : 1;
        $pageSize = $pageSize ? $pageSize : 10;
        $pageSize = $pageSize > 20 ? 20 : $pageSize;

        $model = SwanKeyOpenidMapModel::createModel();
        $results = $model->offset(($page - 1) * $pageSize)->limit($pageSize)->get()->toArray();

        $this->line(sprintf(self::USER_INFO_PRINT_FORMAT . "\n", 'KEY', 'OPENID', '昵称'));
        foreach ($results as $result) {
            try {
                $user = $this->weChatApp->user->get($result['openid']);

                if (!isset($user->nickname)) {
                    $user->nickname = "<fg=red>用户不存在</>";
                }
            } catch (\Exception $e) {
                $user = new \stdClass();
                $user->nickname = "<fg=magenta>未知用户</>";
            }

            $this->line(sprintf(self::USER_INFO_PRINT_FORMAT, $result['key'], $result['openid'], $user->nickname));
        }
    }
}
