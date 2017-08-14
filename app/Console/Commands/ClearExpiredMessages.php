<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use EasyWeChat\Foundation\Application as WeChatApplication;
use App\Swan;
use App\Models\SwanMessageModel;
use Carbon\Carbon;

class ClearExpiredMessages extends Command
{
    const SIGNATURE = 'swan:clear-expired-messages';
    const DESCRIPTION = 'Delete expired messages in database';
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
        $keepDays = env('SWAN_KEEP_MESSAGES_BEFORE_DAYS', 30);
        $limitDelete = env('SWAN_DELETE_MESSAGES_LIMIT_PER_TIME', 100);
        $messageModel = SwanMessageModel::createModel();
        $deleted = 0;
        $timeString = Carbon::today()->subDays($keepDays)->toDateTimeString();
        $timeObj = Swan::convertToDatabaseDatetimeString($timeString);

        $this->line("Now may delete messages created_at before {$timeString}");

        do {
            $nowDeleted = $messageModel->where('created_at', '<', $timeObj)
                ->limit($limitDelete)
                ->delete();

            $deleted += intval($nowDeleted);

            if ($nowDeleted) {
                $this->line("Deleted {$nowDeleted} messages this time");
            }
        } while ($nowDeleted);

        $this->line("Total deleted {$deleted} messages");
    }
}
