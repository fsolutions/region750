<?php

namespace App\Http\Controllers\API\Address;

use App\Models\Flat;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class FlatController extends CrudController
{
    /**
     * Flat constructor
     *
     * FlatController constructor.
     * @param Flat $model
     */
    public function __construct(Flat $model)
    {
        $this->orderBy = [
            'column' => 'name',
            'type' => 'asc'
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
        $region_id = empty(request('region_id')) ? '' : (int) request('region_id');
        $city_id = empty(request('city_id')) ? '' : (int) request('city_id');
        $street_id = empty(request('street_id')) ? '' : (int) request('street_id');
        $house_id = empty(request('house_id')) ? '' : (int) request('house_id');

        $this->filteringByRegion($region_id);
        $this->filteringByCity($city_id);
        $this->filteringByStreet($street_id);
        $this->filteringByHouse($house_id);

        return parent::index();
    }

    /**
     * Filtering by region
     *
     * @param string $region_id
     */
    public function filteringByRegion($region_id = '')
    {
        if ($region_id != '') {
            $this->model = $this->model->where('region_id', $region_id);
        }
    }

    /**
     * Filtering by city
     *
     * @param string $city_id
     */
    public function filteringByCity($city_id = '')
    {
        if ($city_id != '') {
            $this->model = $this->model->where('city_id', $city_id);
        }
    }

    /**
     * Filtering by street
     *
     * @param string $street_id
     */
    public function filteringByStreet($street_id = '')
    {
        if ($street_id != '') {
            $this->model = $this->model->where('street_id', $street_id);
        }
    }

    /**
     * Filtering by house
     *
     * @param string $house_id
     */
    public function filteringByHouse($house_id = '')
    {
        if ($house_id != '') {
            $this->model = $this->model->where('house_id', $house_id);
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
        $messages = array(
            'required' => ':attribute не задан. Заполните форму до конца. Выберите область, город, улицу, дом, и впишите номер квартиры, либо поставьте прочерк, если номер квартиры отсутствует.'
        );

        $customAttributes = [
            'name' => 'Номер квартиры',
        ];

        $validator = Validator::make($this->formData, [
            'name'  => 'required|string|min:1'
        ], $messages, $customAttributes);

        return $validator;
    }
}
