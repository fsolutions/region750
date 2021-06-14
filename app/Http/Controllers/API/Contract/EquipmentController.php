<?php

namespace App\Http\Controllers\API\Contract;

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
        $equip_contract_id = empty(request('equip_contract_id')) ? '' : (int) request('equip_contract_id');

        $this->filteringByContractId($equip_contract_id);

        $result = parent::index();

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
            $this->model = $this->model->where('equip_contract_id', $equip_contract_id);
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
