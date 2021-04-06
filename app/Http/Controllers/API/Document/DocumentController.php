<?php

namespace App\Http\Controllers\API\Document;

use App\Http\Controllers\CrudController;
use App\Models\Document;
use App\Models\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends CrudController
{
    /**
     * CompanyController constructor.
     *
     * @param Document $model
     */
    public function __construct(Document $model)
    {
        parent::__construct($model);

        $this->formData['path'] = 'uploads/files/';
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $creator_user_id = empty(request('creator_user_id')) ? '' : (int) request('creator_user_id');
        $order_id = empty(request('order_id')) ? '' : (int) request('order_id');
        $order_service_id = empty(request('order_service_id')) ? '' : (int) request('order_service_id');
        $company_id = empty(request('company_id')) ? '' : (int) request('company_id');
        $finance_id = empty(request('finance_id')) ? '' : (int) request('finance_id');
        $reference_document_type_ids = empty(request('reference_document_type_ids')) ? '' : request('reference_document_type_ids');
        $ticket_id = empty(request('ticket_id')) ? '' : request('ticket_id');

        // @TODO STUPID FILTERING REWORK
        $this->filteringByCreatorId($creator_user_id);
        $this->filteringByOrderId($order_id);
        $this->filteringByOrderServiceId($order_service_id);
        $this->filteringByCompanyId($company_id);
        $this->filteringByFinanceId($finance_id);
        $this->filteringByReferenceDocumentTypeStringOfId($reference_document_type_ids);
        $this->filteringByTicketsOfId($ticket_id);

        return parent::index();
    }

    /**
     * Filtering documents by creator_user_id
     *
     * @param $creator_user_id
     */
    public function filteringByCreatorId($creator_user_id) {
        if ($creator_user_id != '') {
            $this->model = $this->model->where('creator_user_id', $creator_user_id);
        }
    }

    /**
     * Filtering documents by order_id
     *
     * @param $order_id
     */
    public function filteringByOrderId($order_id) {
        if ($order_id != '') {
            $this->model = $this->model->where('order_id', $order_id);
        }
    }

    /**
     * Filtering documents by order_service_id
     *
     * @param $order_service_id
     */
    public function filteringByOrderServiceId($order_service_id) {
        if ($order_service_id != '') {
            $this->model = $this->model->where('order_service_id', $order_service_id);
        }
    }

    /**
     * Filtering documents by company_id
     *
     * @param $company_id
     */
    public function filteringByCompanyId($company_id) {
        if ($company_id != '') {
            $this->model = $this->model->where('company_id', $company_id);
        }
    }

    /**
     * Filtering documents by finance_id
     *
     * @param $finance_id
     */
    public function filteringByFinanceId($finance_id) {
        if ($finance_id != '') {
            $this->model = $this->model->where('finance_id', $finance_id);
        }
    }

    /**
     * Filtering documents by reference type ids
     *
     * @param $reference_document_type_ids
     */
    public function filteringByReferenceDocumentTypeStringOfId($reference_document_type_ids) {
        if ($reference_document_type_ids != '') {
            $this->model = $this->model->whereIn('reference_document_type_id', explode(",", $reference_document_type_ids));
        }
    }

    /**
     * Filtering documents by tickets type id
     *
     * @param $ticket_id
     */
    public function filteringByTicketsOfId($ticket_id) {
        if ($ticket_id != '') {
            $this->model = $this->model->where('ticket_id', $ticket_id);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        $this->formData += $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->prepareFormData();

        if (isset($this->formData['files'])) {
            $this->model = $this->multiUploadFiles();
        } else {
            $this->formData['path'] = $this->uploadFile();
            $this->model = parent::store($request);
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
        $this->formData = $request->only(['name', 'description', 'reference_document_type_id', 'date_of_document']);

        $this->model = Document::findOrFail($id);

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
        parent::destroy($id);
    }

    /**
     *  Prepare form data
     */
    private function prepareFormData()
    {
        if (isset($this->formData['files']) && is_array($this->formData['files'])) {
            $this->formData['folder'] = true;
        } else {
            if (!isset($this->formData['name'])) {
                $this->formData['name'] = $this->getOriginalFileName($this->formData['file']);
            }
        }

        if (isset($this->formData['order_id'])) {
            if (isset($this->formData['order_service_id'])) {
                $service_slug = OrderService::where('id', $this->formData['order_service_id'])
                    ->where('order_id', $this->formData['order_id'])
                    ->firstOrFail();
                $this->formData['path'] .= 'orders/' . $this->formData['order_id'] . '/' . $service_slug->reference->slug;
            } else {
                $this->formData['path'] .= 'orders/' . $this->formData['order_id'];
            }
        } elseif (isset($this->formData['company_id'])) {
            $this->formData['path'] .= 'companies/' . $this->formData['company_id'];
        } elseif(isset($this->formData['ticket_id'])) {
            $this->formData['path'] .= 'tickets/' . $this->formData['ticket_id'];
        } else {
            $this->formData['path'] .= 'users/' . $this->user->id;
        }

        $this->formData['creator_user_id'] = $this->user->id;
    }

    /**
     * This form validate
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
//        isset($this->formData['order_id']) ? $required = 'required' : $required = null;
        isset($this->formData['files']) ? $files = 'required' : $files = null;
        isset($this->formData['file']) ? $file = 'required' : $file = null;

        return Validator::make($this->formData, [
            'name' => ['max:255'],
            'file' => ['file', $file],
            'files' => ['array', $files],
            'description' => ['max:255'],
            'reference_document_type_id' => ['exists:reference_properties,id']
//            'order_id' => 'exists:orders,id',
//            'order_service_id' => ['exists:order_services,id', $required],
//            'company_id' => 'exists:companies,id',
//            'finance_id' => 'exists:reference_properties,id',
//            'creator_user_id' => 'exists:reference_properties,id',
            ]);
    }

    /**
     * Store array files.
     *
     * @return array
     */
    private function multiUploadFiles() {

        $documents = [];
        $pathToFile = $this->formData['path'];

        foreach ($this->formData['files'] as $file) {
            $this->formData['name'] = $this->getOriginalFileName($file);
            $this->formData['path'] = $this->uploadFile($pathToFile, $file);
            $store = Document::create($this->formData);
            $documents[] = $store->load(['creator:id,name,email']);
        }

        return $documents;
    }

    /**
     * Get original name file.
     *
     * @param $file
     * @return string
     */
    private function getOriginalFileName($file) {
        return stristr($file->getClientOriginalName(), '.', true);
    }

    /**
     * Upload file to storage.
     *
     * @param null $path
     * @param null $file
     * @return false|string
     */
    private function uploadFile($path = null, $file = null) {

        if ($path == null) {
            $path = $this->formData['path'];
        }

        if ($file == null) {
            $file = $this->formData['file'];
        }

        return Storage::putFile($path, $file);
    }
}
