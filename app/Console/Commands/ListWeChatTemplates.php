<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;

class ListWeChatTemplates extends Command
{
    const SIGNATURE = 'swan:list-templates';
    const DESCRIPTION = 'List all WeChat response templates';
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
        $templates = $this->weChatApp->notice->getPrivateTemplates()->toArray();

        if (!isset($templates['template_list'])) {
            $this->line('There is no template');
            return;
        }

        $this->line('There are ' . (!isset($templates['template_list']) ? 0 : count($templates['template_list'])) .
            ' templates');

        foreach ($templates['template_list'] as $id => $template)
        {
            $id += 1;
            /*
             * {
             *   "template_id": "PW5gQKHtcxeuik--WbNyZQ5jSrSO01FwWZrFU_YGWlk",
             *   "title": "标准通知",
             *   "primary_industry": "",
             *   "deputy_industry": "",
             *   "content": "{{msg.DATA}}",
             *   "example": ""
             * }
             */
            $this->line("模板[{$id}]");
            $this->line("ID:\t{$template['template_id']}");
            $this->line("标题:\t{$template['title']}");
            $this->line("模板:\t{$template['content']}");
            $this->line("==========");
        }
    }
}
