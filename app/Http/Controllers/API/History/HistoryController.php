<?php

namespace App\Http\Controllers\API\History;

use App\Models\User;
use App\Models\History;
use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;

class HistoryController extends CrudController
{
    /**
     * HistoryController constructor.
     *
     * @param $model
     */
    public function __construct(History $model)
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
        $contract_id = empty(request('contract_id')) ? '' : (int) request('contract_id');

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $userContracts = Contract::whereIn('contract_on_user_id', [$this->user->id])->get(['id'])->pluck('id')->toArray();
                    $this->model = $this->model->whereIn('contract_id', $userContracts)->orWhere('user_id', $this->user->id);
                    break;
            }
        }

        $this->filteringByContractId($contract_id);

        $result = parent::index();

        return $result;
    }

    /**
     * Filtering by contract id
     *
     * @param string $contract_id
     */
    public function filteringByContractId($contract_id = '')
    {
        if ($contract_id != '') {
            $this->model = $this->model->where('contract_id', $contract_id);
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

        $this->model = parent::store($request);

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
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
}
