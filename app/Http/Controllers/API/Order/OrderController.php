<?php

namespace App\Http\Controllers\API\Order;

use App\Models\User;
use App\Models\Order;
use App\Models\History;
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
        $order_contract_id = empty(request('contract_id')) ? '' : (int) request('contract_id');
        $order_status = empty(request('order_status')) ? '' : (string) trim(request('order_status'));

        $isClient = false;

        foreach ($this->user->roles as $role) {
            switch ($role->slug) {
                case 'client':
                    $isClient = true;
                    $this->findMyOrders();
                    break;
            }
        }

        $this->filteringByContractId($order_contract_id);
        $this->filteringByOrderStatus($order_status);

        $this->model = parent::index();

        if ($isClient) {
            $this->prepareHeadersForClient();
        }

        return $this->model;
    }

    /**
     * Prepare headers for client
     *
     */
    public function prepareHeadersForClient()
    {
        array_pop($this->model['headers']);

        foreach ($this->model['headers'] as $key => $header) {
            if ($header['key'] === 'order_comment') {
                array_splice($this->model['headers'], $key, 1);
            }
        }
    }

    /**
     * Filtering only my orders
     *
     */
    public function findMyOrders()
    {
        $userContracts = Contract::whereIn('contract_on_user_id', [$this->user->id])->get(['id'])->pluck('id')->toArray();

        $orderIdsByContract = Order::whereIn('order_contract_id', $userContracts)->get(['id'])->pluck('id')->toArray();
        $orderIdsByUserId = Order::where('order_user_id', $this->user->id)->get(['id'])->pluck('id')->toArray();

        $this->model = $this->model->whereIn('id', $orderIdsByContract + $orderIdsByUserId);
    }

    /**
     * Filtering by contract id
     *
     * @param string $order_contract_id
     */
    public function filteringByContractId($order_contract_id = '')
    {
        if ($order_contract_id != '') {
            $this->model = $this->model->where('order_contract_id', $order_contract_id);
        }
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

        if ($this->checkUserIsAdmin()) {
            $this->sendNotificationClient($this->model->order_contract_id);
        } else {
            $this->sendNotificationManager($this->model->order_contract_id);
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
        $this->model = $this->model::findOrFail($id);

        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model->order_start_datetime = $this->formData['order_start_datetime'];
        $this->model->order_comment_for_user = $this->formData['order_comment_for_user'];

        if ($this->model->isDirty('order_comment_for_user')) {
            if ($this->checkUserIsAdmin()) {
                $this->sendNotificationClient($this->model->order_contract_id, 'update');
            }
        }

        $this->model = parent::update($request, $id);

        return $this->model;
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

        $this->model = Order::find($id);

        $this->sendNotificationClient($this->model->order_contract_id, "delete");

        return Order::destroy($id);
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
        if ($this->checkUserIsAdmin()) {
        } else {
            $this->formData['order_user_id'] = $this->user->id;
        }

        return $this->formData;
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
            if (
                $role['slug'] === 'administrator'
                || $role['slug'] === 'manager'
                || $role['slug'] === 'master'
                || $role['slug'] === 'intern'
            ) {
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
     * Send notification for manager.
     *
     * @param int $contract_id
     * @param bool $update
     */
    private function sendNotificationManager($contract_id, $update = false)
    {
        if ($contract_id) {
            $contract = Contract::find($contract_id);
        }

        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['administrator', 'manager']);
        })->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/orders",
            'linkText' => 'Открыть обращения',
            'order_id' => $this->model->id
        ];

        $orderDescription = $this->model->order_description != "" ? $this->model->order_description : "Отстствует";

        if ($update) {
            Notification::locale('ru')->send($users, new NotificationsUsers("Обновления в обращении по договору №$contract->contract_number.", $parameters));
        } else {
            if ($contract_id) {
                Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрировано новое обращение по договору №$contract->contract_number.", $parameters));
                History::addNew([
                    'operation_name' => "Зарегистрировано новое обращение по договору №$contract->contract_number (ID: " . $this->model->id . "). Содержимое: " . $orderDescription,
                    'model_name' => get_class(new Order()),
                    'model_id' => $this->model->id,
                    'contract_id' => $contract_id,
                    'user_id' => $this->user->id
                ]);
            } else {
                Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрировано новое обращение без договора. От пользователя с ID: " . $this->model->order_user_id, $parameters));

                History::addNew([
                    'operation_name' => "Зарегистрировано новое обращение без договора с ID: " . $this->model->id . ". Содержимое: " . $orderDescription,
                    'model_name' => get_class(new Order()),
                    'model_id' => $this->model->id,
                    'user_id' => $this->model->order_user_id
                ]);
            }
        }
    }

    /**
     * Send notification for client.
     *
     * @param int $contract_id
     * @param bool $update
     */
    private function sendNotificationClient($contract_id, $operation = '')
    {
        if ($contract_id) {
            $contract = Contract::find($contract_id);
            $users = User::where('id', $contract->contract_on_user_id)->get();
        } else {
            $users = User::where('id', $this->model->order_user_id)->get();
        }

        $parameters = [
            'place' => ['bell'],
            'link' => "/history",
            'linkText' => 'Открыть историю',
            'order_id' => $this->model->id
        ];

        $orderDescription = $this->model->order_description != "" ? $this->model->order_description : "Отстствует";

        if ($operation == 'update') {
            Notification::locale('ru')->send($users, new NotificationsUsers("Оставлен комментарий в обращении (ID: " . $this->model->id . ") по договору №$contract->contract_number: <em>" . $this->model->order_comment_for_user . "</em>", $parameters));

            History::addNew([
                'operation_name' => "Оставлен комментарий в обращении (ID: " . $this->model->id . "): «" . $this->model->order_comment_for_user . "»",
                'model_name' => get_class(new Order()),
                'model_id' => $this->model->id,
                'contract_id' => $contract_id,
                'user_id' => $this->user->id
            ]);
        } else if ($operation == 'delete') {
            if ($contract_id) {
                Notification::locale('ru')->send($users, new NotificationsUsers("Удалено обращение (ID: " . $this->model->id . ") по договору №$contract->contract_number", $parameters));

                History::addNew([
                    'operation_name' => "Удалено обращение (ID: " . $this->model->id . "). Содержимое: " . $orderDescription,
                    'model_name' => get_class(new Order()),
                    'model_id' => $this->model->id,
                    'contract_id' => $contract_id,
                    'user_id' => $this->user->id
                ]);
            } else {
                Notification::locale('ru')->send($users, new NotificationsUsers("Удалено обращение с ID: " . $this->model->id, $parameters));

                History::addNew([
                    'operation_name' => "Удалено обращение с ID: " . $this->model->id . ". Содержимое: " . $orderDescription,
                    'model_name' => get_class(new Order()),
                    'model_id' => $this->model->id,
                    'user_id' => $this->model->order_user_id
                ]);
            }
        } else {
            Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрировано новое обращение по договору №$contract->contract_number.", $parameters));

            History::addNew([
                'operation_name' => "Зарегистрировано новое обращение (ID: " . $this->model->id . ")",
                'model_name' => get_class(new Order()),
                'model_id' => $this->model->id,
                'contract_id' => $contract_id,
                'user_id' => $this->user->id
            ]);
        }
    }
}
