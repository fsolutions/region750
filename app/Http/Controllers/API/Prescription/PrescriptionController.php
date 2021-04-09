<?php

namespace App\Http\Controllers\API\Prescription;

use App\Models\User;
use App\Models\History;
use App\Models\Contract;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class PrescriptionController extends CrudController
{
    /**
     * PrescriptionController constructor.
     *
     * @param $model
     */
    public function __construct(Prescription $model)
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
        $prescription_contract_id = empty(request('contract_id')) ? '' : (int) request('contract_id');
        $prescription_status = empty(request('prescription_status')) ? '' : (string) trim(request('prescription_status'));

        $isClient = false;

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $isClient = true;
                    $userContracts = Contract::whereIn('contract_on_user_id', [$this->user->id])->get(['id'])->pluck('id')->toArray();
                    $this->model = $this->model->whereIn('prescription_contract_id', $userContracts);
                    break;
            }
        }

        $this->filteringByContractId($prescription_contract_id);
        $this->filteringByPrescriptionStatus($prescription_status);

        $result = parent::index();

        if ($isClient) {
            array_pop($result['headers']);
        }

        return $result;
    }

    /**
     * Filtering by contract id
     *
     * @param string $prescription_contract_id
     */
    public function filteringByContractId($prescription_contract_id = '')
    {
        if ($prescription_contract_id != '') {
            $this->model = $this->model->where('prescription_contract_id', $prescription_contract_id);
        }
    }

    /**
     * Filtering prescriptions by status
     * @TODO: REFACTOR
     *
     * @param string $prescription_status
     */
    public function filteringByPrescriptionStatus($prescription_status = '')
    {
        if ($prescription_status != '') {
            $prescriptions = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        prescriptions
                    WHERE 
                        prescription_status = :prescription_status
                    AND
                        deleted_at IS NULL',
                ['prescription_status' => $prescription_status]
            );

            $finishedPrescriptionList = [];

            foreach ($prescriptions as $prescription) {
                $finishedPrescriptionList[] = $prescription->id;
            }

            $this->model = $this->model->whereIn('id', $finishedPrescriptionList);
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

        $this->sendNotificationClient($this->model->prescription_contract_id);

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

        // $this->sendNotificationClient($this->model->prescription_contract_id, true);

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
     * @return \Illuminate\Prescriptions\Validation\Validator
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
     * @param $prescription_id
     * @return bool|void
     */
    private function checkAllowUser($prescription_id)
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
    private function sendNotificationClient(int $contract_id, $update = false)
    {
        $contract = Contract::find($contract_id);
        $users = User::where('id', $contract->contract_on_user_id)->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/prescriptions",
            'linkText' => 'Открыть предписания',
            'prescription_id' => $this->model->id
        ];

        if ($update) {
            Notification::locale('ru')->send($users, new NotificationsUsers("Изменения в предписании по договору №$contract->contract_number.", $parameters));
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Новое предписание по договору №$contract->contract_number.", $parameters));

            History::addNew([
                'operation_name' => "Зарегистрировано новое предписание (ID: " . $this->model->id . ")",
                'model_name' => get_class(new Prescription()),
                'model_id' => $this->model->id,
                'contract_id' => $contract_id,
                'user_id' => $this->user->id
            ]);
        }
    }
}
