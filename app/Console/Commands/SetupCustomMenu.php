<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;

class SetupCustomMenu extends Command
{
    const SIGNATURE   = 'swan:setup-custom-menu {menu?}';
    const DESCRIPTION = 'Setup WeChat custom menu, be careful!';
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
        $this->showCurrentMenu();

        $menu = [
            [
                'name' => 'Key',
                'type' => 'click',
                'key'  => 'key',
            ],
        ];

        if ($this->hasArgument('menu') && $this->argument('menu') !== NULL) {
            $tmpMenu = json_decode($this->argument('menu'), true);

            if (!$tmpMenu) {
                $this->line("Invalid menu config provided.");
                return;
            }

            $menu = $tmpMenu;
        }

        $this->line("New menu config:");
        $this->line('<fg=red>' . $this->toFormattedJSONString($menu) . '</>');

        $this->weChatApp->menu->add($menu);

        $this->showCurrentMenu();
    }

    protected function toFormattedJSONString($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function showCurrentMenu()
    {
        $currentMenu = $this->weChatApp->menu->all()->toArray();
        $currentMenuStr = '';
        if (isset($currentMenu['menu']['button'])) {
            $currentMenuStr = $this->toFormattedJSONString($currentMenu['menu']['button']);
        }

        $this->line("Current menu(JSON):\n<fg=green>{$currentMenuStr} </>");
        $this->line("Current menu(JSON for copy):\n<fg=magenta>" . json_encode($currentMenu['menu']['button'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . " </>");
    }
}
