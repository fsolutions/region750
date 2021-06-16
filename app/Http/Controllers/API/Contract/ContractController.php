<?php

namespace App\Http\Controllers\API\Contract;

use App\Models\User;
use App\Models\History;
use App\Models\Contract;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ContractController extends CrudController
{
    /**
     * ContractController constructor.
     *
     * @param $model
     */
    public function __construct(Contract $model)
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
        $status = empty(request('status')) ? '' : (string) trim(request('status'));

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $this->model = $this->model->whereIn('contract_on_user_id', [$this->user->id]);
                    break;
            }
        }

        $this->filteringByContractStatus($status);

        return parent::index();
    }

    /**
     * Filtering contracts by status
     * @TODO: REFACTOR
     *
     * @param string $status
     */
    public function filteringByContractStatus($status = '')
    {
        if ($status != '') {
            $contracts = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        contracts
                    WHERE 
                        status = :status
                    AND
                        deleted_at IS NULL',
                ['status' => $status]
            );

            $finishedContractList = [];

            foreach ($contracts as $contract) {
                $finishedContractList[] = $contract->id;
            }

            $this->model = $this->model->whereIn('id', $finishedContractList);
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

        if ($this->checkUserIsAdmin()) {
            $this->sendNotificationClient($this->model->id);
        } else {
            $this->sendNotificationManager($this->model->id);
        }

        History::addNew([
            'operation_name' => "Добавлен новый договор с номером " . $this->model->contract_number . " (ID: " . $this->model->id . "), по адресу: " . $this->model->contractRealaddress,
            'model_name' => get_class(new Contract()),
            'model_id' => $this->model->id,
            'contract_id' => $this->model->id,
            'user_id' => $this->user->id
        ]);

        if (isset($this->formData['preparedEquipment'])) {
            $this->saveEquipmentOfContract();
            $this->model->preparedEquipment = [];
            $this->model->equipment = $this->model->equipment();
        }

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
        // $this->checkAllowUser($id);

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
        // $this->checkAllowUser($id);

        $this->model = $this->model::findOrFail($id);

        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = parent::update($request, $id);

        if ($this->checkUserIsAdmin()) {
            $this->sendNotificationClient($this->model->id, true);
        } else {
            $this->sendNotificationManager($this->model->id, true);
        }

        History::addNew([
            'operation_name' => "Обновлен договор с номером " . $this->model->contract_number . " (ID: " . $this->model->id . ").",
            'model_name' => get_class(new Contract()),
            'model_id' => $this->model->id,
            'contract_id' => $this->model->id,
            'user_id' => $this->user->id
        ]);

        if (isset($this->formData['preparedEquipment'])) {
            $this->saveEquipmentOfContract();
            $this->model->preparedEquipment = [];
            $this->model->equipment = $this->model->equipment();
        }

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

        $contract = Contract::find($id);

        History::addNew([
            'operation_name' => "Удален договор с номером " . $contract->contract_number . " (ID: " . $contract->id . ").",
            'model_name' => get_class(new Contract()),
            'model_id' => $contract->id,
            'contract_id' => $contract->id,
            'user_id' => $this->user->id
        ]);

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
        if (!isset($this->formData['creator_user_id'])) {
            $this->formData['creator_user_id'] = $this->user->id;
        }
        if (!isset($this->formData['contract_on_user_id'])) {
            $this->formData['contract_on_user_id'] = $this->user->id;
        }
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
     * Save equiipment of contract
     */
    public function saveEquipmentOfContract()
    {
        foreach ($this->formData['preparedEquipment'] as $index => $equip) {
            $this->formData['preparedEquipment'][$index]['equip_user_id'] = $this->model->contract_on_user_id;
            $this->formData['preparedEquipment'][$index]['equip_contract_id'] = $this->model->id;
        }

        Equipment::where('equip_contract_id', $this->model->id)->delete();

        foreach ($this->formData['preparedEquipment'] as $equip) {
            $newEquip = Equipment::create($equip);
        }
    }

    /**
     * @return bool
     */
    private function checkUserIsAdmin()
    {
        foreach ($this->userRoles->toArray() as $role) {
            if (
                $role['slug'] === 'administrator'
                || $role['slug'] === 'manager'
                || $role['slug'] === 'master'
                || $role['slug'] === 'intern'
            ) {
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

        $result = $this->model::where('creator_user_id', $this->user->id)->where('id', $contract_id);

        if ($result->count()) {
            return true;
        }

        return abort('404');
    }

    /**
     * Send notification for manager.
     *
     * @param int $contract_id
     * @param bool $update
     */
    private function sendNotificationManager(int $contract_id, $update = false)
    {
        $contract = Contract::find($contract_id);
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['administrator', 'manager']);
        })->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/contracts",
            'linkText' => 'Открыть Договоры',
            'order_id' => $this->model->id
        ];

        if ($update) {
            Notification::locale('ru')->send($users, new NotificationsUsers("Обновления в договоре №$contract->contract_number.", $parameters));
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрирован новый договор №$contract->contract_number.", $parameters));
        }
    }

    /**
     * Send notification for client.
     *
     * @param int $contract_id
     * @param bool $update
     */
    private function sendNotificationClient(int $contract_id, $update = false)
    {
        $contract = Contract::find($contract_id);
        $users = User::where('id', $contract->contract_on_user_id)->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/contracts",
            'linkText' => 'Открыть Договоры',
            'order_id' => $this->model->id
        ];

        if ($update) {
            Notification::locale('ru')->send($users, new NotificationsUsers("Обновления в договоре №$contract->contract_number.", $parameters));
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрирован новый договор №$contract->contract_number.", $parameters));
        }
    }
}
