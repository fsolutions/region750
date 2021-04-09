<?php

namespace App\Http\Controllers\API\Contract;

use App\Models\User;
use App\Models\History;
use App\Models\Contract;
use App\Models\ContractTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ContractTOController extends CrudController
{
    /**
     * ContractTOController constructor.
     *
     * @param $model
     */
    public function __construct(ContractTO $model)
    {
        parent::__construct($model);
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

        $this->filteringByContractId($contract_id);
        $this->filteringByContractTOStatus($to_status);

        return parent::index();
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->formData = $request->all();

        $this->prepareFormData();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = parent::store($request);

        $this->sendNotificationClient($this->model->to_contract_id);

        History::addNew([
            'operation_name' => "Добавлено событие ТО-ВКГО (ID: " . $this->model->id . ")",
            'model_name' => get_class(new ContractTO()),
            'model_id' => $this->model->id,
            'contract_id' => $this->model->to_contract_id,
            'user_id' => $this->user->id
        ]);

        return $this->model;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return parent::show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Controllers\Model
     */
    public function update(Request $request, $id)
    {
        $this->model = $this->model::findOrFail($id);

        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = parent::update($request, $id);

        // $this->sendNotificationClient($this->model->to_contract_id, "update");

        // History::addNew([
        //     'operation_name' => "Отредактировано событие ТО-ВКГО (ID: " . $id . ")",
        //     'model_name' => get_class(new ContractTO()),
        //     'model_id' => $id,
        //     'contract_id' => $this->model->to_contract_id,
        //     'user_id' => $this->user->id
        // ]);

        return $this->model;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->checkAllowUser($id);

        $contract = ContractTO::find($id);

        History::addNew([
            'operation_name' => "Удалено событие ТО-ВКГО (ID: " . $id . ")",
            'model_name' => get_class(new ContractTO()),
            'model_id' => $id,
            'contract_id' => $contract->to_contract_id,
            'user_id' => $this->user->id
        ]);

        $this->sendNotificationClient($contract->to_contract_id, "delete");

        return $this->model::destroy($id);
    }

    /**
     * Prepare form data
     *
     * @param Request $request  Request
     *
     * @return array
     */
    private function prepareFormData()
    {
    }

    /**
     * Form data validation.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
        return Validator::make($this->formData, []);
    }

    /**
     * @return bool
     */
    private function checkUserIsAdmin()
    {
        foreach ($this->userRoles->toArray() as $role) {
            if ($role['slug'] === 'administrator') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is admin.
     *
     * @param $contract_id
     * @return bool|void
     */
    private function checkAllowUser($contract_id)
    {
        if ($this->checkUserIsAdmin()) {
            return true;
        }

        return abort('404');
    }

    /**
     * Send notification for client.
     *
     * @param int $contract_id
     */
    private function sendNotificationClient(int $contract_id, $operation = '')
    {
        $contract = Contract::find($contract_id);
        $users = User::where('id', $contract->contract_on_user_id)->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/contracts",
            'linkText' => 'Открыть договор',
            'contract_id' => $contract_id
        ];

        if ($operation == 'update') {
            Notification::locale('ru')->send($users, new NotificationsUsers("Изменения в назначенном ТО по договору №$contract->contract_number.", $parameters));
        } else if ($operation == 'delete') {
            Notification::locale('ru')->send($users, new NotificationsUsers("Удалено назначенное ТО по договору №$contract->contract_number. Ожидайте подробностей в ближайшее время.", $parameters));
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Назначена дата проведения ТО по договору №$contract->contract_number.", $parameters));
        }
    }
}
