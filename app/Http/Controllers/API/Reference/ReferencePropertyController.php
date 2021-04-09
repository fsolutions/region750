<?php

namespace App\Http\Controllers\API\Reference;

use App\Models\Reference;
use Illuminate\Http\Request;
use App\Models\ReferenceProperty;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class ReferencePropertyController extends CrudController
{
    /**
     * ReferenceProperty constructor
     *
     * ReferencePropertyController constructor.
     * @param ReferenceProperty $model
     */
    public function __construct(ReferenceProperty $model)
    {
        $this->orderBy = [
            'column' => 'name',
            'type' => 'asc'
        ];

        parent::__construct($model);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // @TODO Move logic to services
        if (!empty(request('reference_id'))) {
            $reference_id = request('reference_id');
            // Check if reference_id is anybody parent?
            $references = Reference::where('parent_id', '=', $reference_id)->get(['id', 'name']);
            if (count($references) > 0) {
                $referencesWithKeys = [];
                $referencesIds = [];
                foreach ($references as $reference) {
                    $referencesWithKeys[$reference->id] = $reference->name;
                    $referencesIds[] = $reference->id;
                }
                $this->model = $this->model->whereIn('reference_id', $referencesIds)->get(['id as value', 'name as text', 'reference_id']);
                foreach ($this->model as $property) {
                    $property->reference_name = $referencesWithKeys[$property->reference_id];
                }
                $groupedProperties = $this->model->mapToGroups(function ($item, $key) {
                    return [$item['reference_name'] => $item];
                });

                $groupedProperties->toArray();

                $groupedPropertiesFinal = [];
                foreach ($groupedProperties as $groupName => $groupArray) {
                    $groupedPropertiesFinal[] = [
                        'label' => $groupName,
                        'options' => $groupArray
                    ];
                }

                return $groupedPropertiesFinal;
            } else {
                $this->model = $this->model->where('reference_id', '=', $reference_id)->get(['id as value', 'name as text']);
            }

            return $this->model;
        }
        if (!empty(request('all_references_group_by_reference_id'))) {
            $this->model = $this->model->get();

            $allProperties = [];
            foreach ($this->model as $property) {
                $allProperties[$property->reference_id][] = $property;
            }

            return $allProperties;
        }
        if (!empty(request('all_references'))) {
            $this->model = $this->model->get(['id as value', 'name as text']);

            $allProperties = [];
            foreach ($this->model as $property) {
                $allProperties[$property->value] = $property;
            }

            return $allProperties;
        }

        $result = parent::index();

        return $result;
    }

    /**
     * Store a newly created resource in storage.
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
     *  Form data validation
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
        $validator = Validator::make($this->formData, [
            'name'  => 'required|string|min:2',
            'reference_id' => 'exists:references,id',
        ]);

        return $validator;
    }
}
