<?php

namespace App\Http\Controllers\API\Contract;

use App\Models\Contract;
use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class EquipmentController extends CrudController
{
    /**
     * Equipment constructor
     *
     * EquipmentController constructor.
     * @param Equipment $model
     */
    public function __construct(Equipment $model)
    {
        $this->orderBy = [
            'column' => 'id',
            'type' => 'desc'
        ];

        parent::__construct($model);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $equip_contract_id = empty(request('equip_contract_id')) ? '' : trim(request('equip_contract_id'));

        $isClient = false;

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $isClient = true;
                    $userContracts = Contract::whereIn('contract_on_user_id', [$this->user->id])->get(['id'])->pluck('id')->toArray();
                    $this->model = $this->model->whereIn('equip_contract_id', $userContracts);
                    break;
            }
        }

        $this->filteringByContractId($equip_contract_id);


        $result = parent::index();

        if ($isClient) {
            array_pop($result['headers']);
        }

        return $result;
    }

    /**
     * Filtering contracts by equip_contract_id
     *
     * @param string $equip_contract_id
     */
    public function filteringByContractId($equip_contract_id = '')
    {
        if ($equip_contract_id != '') {
            $this->model = $this->model->whereIn('equip_contract_id', explode(",", $equip_contract_id));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = parent::show($id);

        return $result;
    }

    /**
     *  Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \App\Http\Controllers\Model|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if (!isset($this->formData['equip_user_id']) || $this->formData['equip_user_id'] == '') {
            $contract = Contract::findOrFail($this->formData['equip_contract_id']);
            $this->formData['equip_user_id'] = $contract->contract_on_user_id;
        }

        $this->model = parent::store($request);

        return $this->model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \App\Http\Controllers\Model|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->formData = $request->all();

        $this->model = $this->model::findOrFail($id);

        $validator = $this->validationFormData($this->model);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        parent::update($request, $id);

        return $this->model;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return parent::destroy($id);
    }

    /**
     * Form data validation
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
        $validator = Validator::make($this->formData, [
            // 'name'  => 'required|string|min:1'
        ]);

        return $validator;
    }
}
