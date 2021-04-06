<?php

namespace App\Http\Controllers\API\Contract;

use App\Models\User;
use App\Models\Contract;
use App\Models\ContractTO;
use Illuminate\Http\Request;
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
        return parent::index();
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

        $this->sendNotificationClient($this->model->to_contract_id, true);

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
     * Send notification for admin.
     *
     * @param int $contract_id
     */
    private function sendNotificationClient(int $contract_id, $update = false)
    {
        $contract = Contract::find($contract_id);
        $users = User::where('id', $contract->contract_on_user_id)->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/contracts",
            'linkText' => 'Открыть договор'
        ];

        if ($update) {
            Notification::locale('ru')->send($users, new NotificationsUsers("Изменения в назначенном ТО по договору №$contract_id.", $parameters));
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Назначена дата проведения ТО по договору №$contract_id.", $parameters));
        }
    }
}
