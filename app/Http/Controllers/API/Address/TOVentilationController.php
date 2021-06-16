<?php

namespace App\Http\Controllers\API\Address;

use Illuminate\Http\Request;
use App\Models\TOVentilation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class TOVentilationController extends CrudController
{
    /**
     * TO Ventilation constructor
     *
     * TOVentilationController constructor.
     * @param TOVentilation $model
     */
    public function __construct(TOVentilation $model)
    {
        $this->orderBy = [
            'column' => 'ventilation_date_of_work',
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
        $ventilation_region_id = empty(request('ventilation_region_id')) ? '' : (int) request('ventilation_region_id');
        $ventilation_city_id = empty(request('ventilation_city_id')) ? '' : (int) request('ventilation_city_id');
        $ventilation_street_id = empty(request('ventilation_street_id')) ? '' : (int) request('ventilation_street_id');
        $ventilation_house_id = empty(request('ventilation_house_id')) ? '' : (int) request('ventilation_house_id');
        $ventilation_status = empty(request('ventilation_status')) ? '' : request('ventilation_status');

        $this->filteringByRegion($ventilation_region_id);
        $this->filteringByCity($ventilation_city_id);
        $this->filteringByStreet($ventilation_street_id);
        $this->filteringByHouse($ventilation_house_id);
        $this->filteringByStatus($ventilation_status);

        return parent::index();
    }

    /**
     * Filtering by region
     *
     * @param string $ventilation_region_id
     */
    public function filteringByRegion($ventilation_region_id = '')
    {
        if ($ventilation_region_id != '') {
            $this->model = $this->model->where('ventilation_region_id', $ventilation_region_id);
        }
    }

    /**
     * Filtering by city
     *
     * @param string $ventilation_city_id
     */
    public function filteringByCity($ventilation_city_id = '')
    {
        if ($ventilation_city_id != '') {
            $this->model = $this->model->where('ventilation_city_id', $ventilation_city_id);
        }
    }

    /**
     * Filtering by street
     *
     * @param string $ventilation_street_id
     */
    public function filteringByStreet($ventilation_street_id = '')
    {
        if ($ventilation_street_id != '') {
            $this->model = $this->model->where('ventilation_street_id', $ventilation_street_id);
        }
    }

    /**
     * Filtering by house
     *
     * @param string $ventilation_house_id
     */
    public function filteringByHouse($ventilation_house_id = '')
    {
        if ($ventilation_house_id != '') {
            $this->model = $this->model->where('ventilation_house_id', $ventilation_house_id);
        }
    }

    /**
     * Filtering by status
     *
     * @param string $ventilation_status
     */
    public function filteringByStatus($ventilation_status = '')
    {
        if ($ventilation_status != '') {
            $to_ventilation = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        to_ventilation
                    WHERE 
                        ventilation_status IN (:ventilation_status)',
                ['ventilation_status' => $ventilation_status]
            );

            $finishedTOList = [];

            foreach ($to_ventilation as $to) {
                $finishedTOList[] = $to->id;
            }

            $this->model = $this->model->whereIn('id', $finishedTOList);
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
