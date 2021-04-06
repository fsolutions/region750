<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\CrudController;
use App\Models\UserDocumentAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserDocumentAgreementController extends CrudController
{
    /**
     * UserDocumentAgreementController constructor.
     *
     * @param $model
     */
    public function __construct(UserDocumentAgreement $model)
    {
        parent::__construct($model);
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

        $validator = $this->validationFormData('required');

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->prepareFormData();

        return parent::store($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = $this->model::findOrFail($id);

        return parent::update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
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
    private function validationFormData($required = null)
    {
        return Validator::make($this->formData, [
            'user_id' => ['exists:users,id', $required],
            'document_id' => ['exists:documents,id', $required],
            'document_status_id' => ['exists:peference_properties,id']
        ]);
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
        if (!isset($this->formData['document_status_id']))
        {
            $this->formData['document_status_id'] = 308; // на согласовании
        }
    }
}
