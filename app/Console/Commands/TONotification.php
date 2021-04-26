<?php

namespace App\Console\Commands;

use App\Models\ContractTO;
use Illuminate\Console\Command;
use App\Bundles\Notifications\SMSC;
use Illuminate\Support\Facades\Log;

class TONotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'to:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TO notificator';

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
     * @return int
     */
    public function handle()
    {
        $startDateMax = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "+1 month"));
        $toOS = ContractTO::whereBetween('to_start_datetime', [$startDateMax . " 00:00:00", $startDateMax . " 23:59:59"])
            ->whereNull('to_sms_sended')->with(['to_contract_for_user', 'to_contract'])->get();

        $sms = new SMSC();
        $companyName = env("APP_COMPANY_NAME");
        $companyPhone = env("APP_COMPANY_PHONE");

        foreach ($toOS as $to_key => $to) {
            $result = false;
            $message = 'Напоминаем вам, что в ближайший месяц, по договору №' . $to->to_contract->contract_number . ', необходимо произвести ТО счетчиков.
' . $companyName . ', ' . $companyPhone;
            $result = $sms->sendSMSNotify($to->to_contract_for_user->phone, $message);

            if ($result) {
                $this->info('TO SMS to USER: #' . $to->to_contract_for_user->id . ' sended successfull');
                $to->to_sms_sended = date("Y-m-d H:i:s");
                $to->save();
            } else {
                $this->error('TO SMS to USER: #' . $to->to_contract_for_user->id . ' sended successfull');
            }
        }

        $this->info(count($toOS) . ' sms sended');

        Log::channel('jobs')->info(count($toOS) . ' sms sended');
    }
}
