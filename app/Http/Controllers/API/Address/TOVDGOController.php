<?php

namespace App\Http\Controllers\API\Address;

use App\Models\TOVDGO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class TOVDGOController extends CrudController
{
    /**
     * TO VGKO constructor
     *
     * TOVDGOController constructor.
     * @param TOVDGO $model
     */
    public function __construct(TOVDGO $model)
    {
        $this->orderBy = [
            'column' => 'vgko_date_of_work',
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
        $vgko_region_id = empty(request('vgko_region_id')) ? '' : (int) request('vgko_region_id');
        $vgko_city_id = empty(request('vgko_city_id')) ? '' : (int) request('vgko_city_id');
        $vgko_street_id = empty(request('vgko_street_id')) ? '' : (int) request('vgko_street_id');
        $vgko_house_id = empty(request('vgko_house_id')) ? '' : (int) request('vgko_house_id');
        $vgko_status = empty(request('vgko_status')) ? '' : request('vgko_status');

        $this->filteringByRegion($vgko_region_id);
        $this->filteringByCity($vgko_city_id);
        $this->filteringByStreet($vgko_street_id);
        $this->filteringByHouse($vgko_house_id);
        $this->filteringByStatus($vgko_status);

        return parent::index();
    }

    /**
     * Filtering by region
     *
     * @param string $vgko_region_id
     */
    public function filteringByRegion($vgko_region_id = '')
    {
        if ($vgko_region_id != '') {
            $this->model = $this->model->where('vgko_region_id', $vgko_region_id);
        }
    }

    /**
     * Filtering by city
     *
     * @param string $vgko_city_id
     */
    public function filteringByCity($vgko_city_id = '')
    {
        if ($vgko_city_id != '') {
            $this->model = $this->model->where('vgko_city_id', $vgko_city_id);
        }
    }

    /**
     * Filtering by street
     *
     * @param string $vgko_street_id
     */
    public function filteringByStreet($vgko_street_id = '')
    {
        if ($vgko_street_id != '') {
            $this->model = $this->model->where('vgko_street_id', $vgko_street_id);
        }
    }

    /**
     * Filtering by house
     *
     * @param string $vgko_house_id
     */
    public function filteringByHouse($vgko_house_id = '')
    {
        if ($vgko_house_id != '') {
            $this->model = $this->model->where('vgko_house_id', $vgko_house_id);
        }
    }

    /**
     * Filtering by status
     *
     * @param string $vgko_status
     */
    public function filteringByStatus($vgko_status = '')
    {
        if ($vgko_status != '') {
            $to_vgko = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        to_vgko
                    WHERE 
                        vgko_status = :vgko_status',
                ['vgko_status' => $vgko_status]
            );

            $finishedTOList = [];

            foreach ($to_vgko as $to) {
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
