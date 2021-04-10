<?php

namespace App\Bundles\Calendar;

use App\Models\Contract;
use App\Models\ContractTO;
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
        $contract_id = empty(request('contract_id')) ? '' : (int) request('contract_id');
        $to_status = empty(request('to_status')) ? '' : trim(request('to_status'));

        $this->items = ContractTO::all();

        // $this->items = $this->filteringByContractId($contract_id);
        // $this->items = $this->filteringByContractTOStatus($to_status);

        return $this->items;
    }

    /**
     * Filtering by contract id
     *
     * @param string $contract_id
     */
    public function filteringByContractId($contract_id = '')
    {
        if ($contract_id != '') {
            $this->model = $this->model->where('to_contract_id', $contract_id);
        }
    }

    /**
     * Filtering contracts TO by status
     * @TODO: REFACTOR
     *
     * @param string $status
     */
    public function filteringByContractTOStatus($to_status = '')
    {
        if ($to_status != '') {
            $contractsTO = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        contracts_to
                    WHERE 
                        to_status = :to_status
                    AND
                        deleted_at IS NULL',
                ['to_status' => $to_status]
            );

            $finishedContractTOList = [];

            foreach ($contractsTO as $contractTO) {
                $finishedContractTOList[] = $contractTO->id;
            }

            $this->model = $this->model->whereIn('id', $finishedContractTOList);
        }
    }
}
