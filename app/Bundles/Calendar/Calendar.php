<?php

namespace App\Bundles\Calendar;

use App\Models\TOVDGO;
use App\Models\Contract;
use App\Models\ContractTO;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class Calendar
{
    /**
     * Now user instance
     *
     * @var User
     */
    public $user;

    /**
     * Items for output
     *
     * @var Array
     */
    public $items;

    /**
     * Calendar constructor.
     *
     * @param $model
     */
    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $arrayOfStatuses = [
            'Запланировано' => '',
            'Проведено' => 'success',
            'Отменено' => 'danger',
            'Перенесено' => 'warning',
        ];
        $to_status = empty(request('to_status')) ? '' : trim(request('to_status'));
        $start_date = empty(request('start_date')) ? '' : trim(request('start_date'));
        $end_date = empty(request('end_date')) ? '' : trim(request('end_date'));

        if ($start_date != '' && $end_date != '') {
            $contractTO = ContractTO::whereBetween('to_start_datetime', [$start_date, $end_date])
                ->with(['to_contract', 'master', 'to_contract_for_user'])
                ->get([
                    'contracts_to.id',
                    'contracts_to.to_start_datetime as startDate',
                    'contracts_to.to_start_datetime as endDate',
                    'contracts_to.to_contract_id',
                    'contracts_to.to_master_user_id',
                    'contracts_to.to_status'
                ]);

            foreach ($contractTO as $key => &$item) {
                $item['title'] = 'ТО по договору №' . $item->to_contract->contract_number . ' (' . $item->to_status . ')';
                $item['classes'] = $arrayOfStatuses[$item->to_status];
                $item['showable'] = true;
                $item['model'] = 'ContractTO';
            }

            // $to_vdgo = TOVDGO::whereBetween('vgko_date_of_work', [$start_date, $end_date])
            //     ->with(['vgko_master', 'vgko_house'])
            //     ->get([
            //         'to_vgko.id',
            //         'to_vgko.vgko_date_of_work as startDate',
            //         'to_vgko.vgko_date_of_work as endDate',
            //         'to_vgko.vgko_master_user_id',
            //         'to_vgko.vgko_house_id',
            //         'to_vgko.vgko_status'
            //     ]);

            // foreach ($to_vdgo as $key => &$item) {
            //     $item['title'] = 'ТО-ВДГО (' . $item->vgko_status . ')';
            //     $item['classes'] = $arrayOfStatuses[$item->vgko_status];
            //     $item['showable'] = true;
            //     $item['model'] = 'TOVDGO';
            // }

            $prescriptions = Prescription::whereBetween('prescription_start_datetime', [$start_date, $end_date])
                ->with(['prescription_contract', 'master', 'to_contract_for_user'])
                ->get([
                    'prescriptions.id',
                    'prescriptions.prescription_start_datetime as startDate',
                    'prescriptions.prescription_start_datetime as endDate',
                    'prescriptions.prescription_contract_id',
                    'prescriptions.prescription_number',
                    'prescriptions.prescription_status'
                ]);

            foreach ($prescriptions as $key => &$item) {
                $item['title'] = 'Предписание №' . $item->prescription_number . ' (' . $item->prescription_status . ')';
                $item['classes'] = $arrayOfStatuses[$item->prescription_status];
                $item['showable'] = true;
                $item['model'] = 'Prescription';
            }

            $this->items = array_merge($prescriptions->toArray(), $contractTO->toArray());
            // $this->items = array_merge($prescriptions->toArray(), $contractTO->toArray(), $to_vdgo->toArray());
        } else {
            return abort('404');
        }

        return $this->items;
    }
}
