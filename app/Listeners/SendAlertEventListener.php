<?php

namespace App\Listeners;

use App\Events\SendAlertEvent;
use App\Swan;
use EasyWeChat\Foundation\Application as WeChatApplication;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAlertEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $weChatApp = null;

    public $connection = 'redis';
    public $queue      = 'send_alert';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->weChatApp = new WeChatApplication(Swan::loadEasyWeChatConfig());
    }

    /**
     * Handle the event.
     *
     * @param  SendAlertEvent $event
     * @return void
     */
    public function handle(SendAlertEvent $event)
    {
        $this->weChatApp->notice->to($event->getOpenid())
                                ->uses($event->getTemplateId())
                                ->withUrl(request()->root() . "/wechat/swan/detail/{$event->getMessageId()}")
                                ->withData($event->getData())
                                ->send();
        $this->delete();
    }
}
