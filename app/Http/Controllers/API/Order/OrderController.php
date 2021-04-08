<?php

namespace App\Http\Controllers\API\Order;

use App\Models\User;
use App\Models\Order;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CrudController;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class OrderController extends CrudController
{
    /**
     * OrderController constructor.
     *
     * @param $model
     */
    public function __construct(Order $model)
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
        $order_status = empty(request('order_status')) ? '' : (string) trim(request('order_status'));

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $userContracts = Contract::whereIn('contract_on_user_id', [$this->user->id])->get(['id'])->pluck('id')->toArray();
                    $this->model = $this->model->whereIn('order_contract_id', [$userContracts]);
                    break;
            }
        }

        $this->filteringByOrderStatus($order_status);

        return parent::index();
    }

    /**
     * Filtering orders by status
     * @TODO: REFACTOR
     *
     * @param string $order_status
     */
    public function filteringByOrderStatus($order_status = '')
    {
        if ($order_status != '') {
            $orders = DB::select(
                'SELECT DISTINCT
                        id
                    FROM
                        orders
                    WHERE 
                        order_status = :order_status
                    AND
                        deleted_at IS NULL',
                ['order_status' => $order_status]
            );

            $finishedOrderList = [];

            foreach ($orders as $order) {
                $finishedOrderList[] = $order->id;
            }

            $this->model = $this->model->whereIn('id', $finishedOrderList);
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

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = parent::store($request);

        // $this->sendNotificationAdmin($this->model->id);

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
        $this->model = $this->model::findOrFail($id);

        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

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
        $this->checkAllowUser($id);

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

    /**
     * Form data validation.
     *
     * @return \Illuminate\Orders\Validation\Validator
     */
    private function validationFormData()
    {
        return Validator::make($this->formData, []);
    }

    /**
     * @return bool
     */
    private function checkUserIsAdmin()
    {
        foreach ($this->userRoles->toArray() as $role) {
            if ($role['slug'] === 'administrator') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check is admin.
     *
     * @param $order_id
     * @return bool|void
     */
    private function checkAllowUser($order_id)
    {
        if ($this->checkUserIsAdmin()) {
            return true;
        }

        return abort('404');
    }

    /**
     * Send notification for admin.
     *
     * @param int $order_id
     */
    private function sendNotificationAdmin(int $order_id)
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('slug', 'manager');
        })->get();

        $parameters = [
            'order_id' => $order_id,
            'place' => ['bell']
        ];

        Notification::locale('ru')->send($users, new NotificationsUsers("Новый договор добавлен в систему №$order_id.", $parameters));
    }
}
