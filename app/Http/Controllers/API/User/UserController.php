<?php

namespace App\Http\Controllers\API\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Validator;

class UserController extends CrudController
{
    /**
     * User constructor
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Display a listing of the resource.
     *
     * @param array $loadArray
     * @return array
     */
    public function index()
    {
        $roles = empty(request('roles')) ? '' : trim(request('roles'));

        $this->filteringByRoles($roles);

        return parent::index();
    }

    /**
     * Filtering by roles
     *
     * @param string $roles
     */
    public function filteringByRoles($roles = '')
    {
        if ($roles != '') {
            $userIds = [];
            $roles = explode("||", $roles);
            $userIds = User::whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('slug', $roles);
            })->get(['id'])->pluck('id')->toArray();

            $this->model = $this->model->whereIn('id', $userIds);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->formData = $request->all();

        $validator = $this->validationFormData();
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->prepareFormData($request);
        $this->model = parent::store($request);

        $this->setUserRole();
        $this->model->load($this->modelLoads);

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
        $result = parent::show($id);

        return $result;
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

        $this->model = $this->model::findOrFail($id);

        $validator = $this->validationFormData($this->model->id);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->prepareFormData($request);

        parent::update($request, $id);
        $this->setUserRole(true);

        $this->model->load($this->modelLoads);

        return $this->model;
    }

    /**
     * @TODO: Move to prepareFormData
     * Set user role
     * @param bool $update
     */
    public function setUserRole(bool $update = false)
    {
        if (!isset($this->formData['role']) || $this->formData['role'] == '') {
            if (!$update) {
                $role = Role::where('slug', 'client')->first();
                $this->model->roles()->attach($role);
            }
        } else {
            $roles = Role::where('slug', $this->formData['role'])->get("id");
            $this->model->roles()->sync($roles);
        }
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
     * @param array $requestFormData Request form data
     * @param string $criteria    Validation criteria
     *
     * @return string
     */
    private function validationFormData($criteria = '')
    {
        $criteriaPhone = '';
        if ($criteria) {
            $criteriaPhone = ',phone,' . $criteria;
            $criteria = ',email,' . $criteria;
        }

        $validator = Validator::make($this->formData, [
            'name'  => 'required|string|min:2',
            'email' => 'required|email|unique:users' . $criteria,
            'phone' => 'required|string|max:50|unique:users' . $criteriaPhone,
        ]);

        return $validator;
    }

    /**
     * Prepare form data
     *
     * @param Request $request  Request
     *
     * @return array
     */
    private function prepareFormData(Request $request): array
    {
        if (isset($this->formData['phone']) && $this->formData['phone'] != "") {
            $this->formData['phone'] = preg_replace('![^0-9]+!', '', $this->formData['phone']);   //Чистим телефон
        }

        if (isset($this->formData['password']) && $this->formData['password'] != "") {
            $this->formData['password'] = bcrypt($this->formData['password']);
        }

        return $this->formData;
    }
}
