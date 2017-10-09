<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;
use App\Models\SwanMessageModel;
use App\Models\SwanMessageMongoModel;

class Report extends Command
{
    const SIGNATURE   = 'swan:report';
    const DESCRIPTION = 'SWAN report';
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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('<fg=green>===================> SWAN Report <===================</>');
        $messageModel = SwanMessageModel::createModel();
        $sent = $messageModel->count();
        $sentStart = $messageModel->min('created_at') . '';
        $sentEnd = $messageModel->max('created_at') . '';

        if ($messageModel instanceof SwanMessageMongoModel) {
            $sentStart /= 1000;
            $sentEnd /= 1000;
        }

        $sentStart = date('Y-m-d H:i:s', $sentStart);
        $sentEnd = date('Y-m-d H:i:s', $sentEnd);

        $this->line("Sent: <fg=red>{$sent}</> from [{$sentStart}] to [{$sentEnd}]");
        $this->line('');

        for ($daysBefore = 0; $daysBefore < 7; ++$daysBefore) {

            if ($daysBefore == 0) {
                $carbonTime = Carbon::now();
            } else {
                $carbonTime = Carbon::today()->subDays($daysBefore - 1);
            }

            $timeString = $carbonTime->toDateTimeString();
            $timeObj = Swan::convertToDatabaseDatetimeString($timeString);

            if ($daysBefore == 0) {
                $carbonTimeFrom = Carbon::today();
            } else {
                $carbonTimeFrom = Carbon::today()->subDays($daysBefore);
            }

            $timeStringFrom = $carbonTimeFrom->toDateTimeString();
            $timeObjFrom = Swan::convertToDatabaseDatetimeString($timeStringFrom);
            $sentInDay = $messageModel->where('created_at', '>', $timeObjFrom)
                                      ->where('created_at', '<=', $timeObj)
                                      ->count();

            $this->line("{$carbonTime->toDateString()} sent: {$sentInDay}");
        }

    }
}
