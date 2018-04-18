<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SendAlertEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $openid;
    protected $templateId;
    protected $messageId;
    protected $data;

    /**
     * SendAlertEvent constructor.
     * @param $openid
     * @param $templateId
     * @param $messageId
     * @param $data
     */
    public function __construct($openid, $templateId, $messageId, $data)
    {
        $this->openid     = $openid;
        $this->templateId = $templateId;
        $this->messageId  = $messageId;
        $this->data       = $data;
    }

    /**
     * @return mixed
     */
    public function getOpenid()
    {
        return $this->openid;
    }

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('send_alert');
    }
}
