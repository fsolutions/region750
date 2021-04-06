<?php

namespace App\Http\Controllers\API\Order;

use App\Bundles\Order\CustomSync;
use App\Bundles\Order\OrderCopy;
use App\Http\Controllers\CrudController;
use App\Models\CompanyEmployee;
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderService;
use App\Models\User;
use App\Notifications\NotificationsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class OrderController extends CrudController
{
    /**
     * Order constructor
     *
     * OrderController constructor.
     * @param Order $model
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
        $reference_order_type_id = empty(request('reference_order_type_id')) ? '' : (int) request('reference_order_type_id');
        $only_creator_user_orders = empty(request('only_creator_user_orders')) ? false : (bool) request('only_creator_user_orders');
        $only_this_company_orders = empty(request('only_this_company_orders')) ? '' : (int) request('only_this_company_orders');
        $reference_sources_id = empty(request('reference_sources_id')) ? '' : (int) request('reference_sources_id');

        //        foreach ($this->user->role as $key => $role) {
        //            switch ($role) {
        //                case 'client':
        //                    $this->model = $this->model->where('creator_user_id', $this->user->id);
        //                    break;
        //            }
        //        }

        $this->filteringByOrderStatus($reference_order_type_id);
        //        $this->filteringByCreatorUserId($this->user->id, $only_creator_user_orders);
        $this->filteringByMemberingUserId($this->user->id, $only_creator_user_orders);
        $this->filteringByCompanyId($only_this_company_orders);

        // Anti website filter disable
        if ($reference_order_type_id != '' || $only_creator_user_orders != '') {
            $this->filteringBySourceId($reference_sources_id);
        }

        return parent::index();
    }

    /**
     * Filtering orders by status
     *
     * @param string $reference_order_type_id
     */
    public function filteringByOrderStatus($reference_order_type_id = '')
    {
        if ($reference_order_type_id != '') {
            $this->model = $this->model->where('reference_order_type_id', $reference_order_type_id);
        }
    }

    /**
     * Filtering orders by creator_user_id
     *
     * @param $creator_user_id
     * @param bool $needFiltering
     */
    public function filteringByCreatorUserId($creator_user_id, $needFiltering = false)
    {
        if ($needFiltering) {
            $this->model = $this->model->where('creator_user_id', $creator_user_id);
        }
    }

    /**
     * Filtering orders by user_id membering in orders and services
     *
     * @param $user_id
     * @param bool $needFiltering
     */
    public function filteringByMemberingUserId($user_id, $needFiltering = false)
    {
        if ($needFiltering) {
            $userServices = DB::select(
                'SELECT DISTINCT
                                                    order_services.order_id as id
                                                FROM
                                                    order_services
                                                        INNER JOIN
                                                            user_services
                                                        ON
                                                            user_services.user_id = :user_id
                                                                AND
                                                            order_services.id = user_services.service_id
                                                             AND
                                                            user_services.deleted_at IS NULL
                                                WHERE 
                                                    order_services.deleted_at IS NULL',
                ['user_id' => $user_id]
            );
            $userCreatorOrders = DB::select(
                'SELECT
                                                    orders.id
                                                FROM
                                                    orders
                                                WHERE 
                                                    orders.creator_user_id = :user_id',
                ['user_id' => $user_id]
            );

            $finishedOrderList = [];

            foreach (array_merge($userServices, $userCreatorOrders) as $order) {
                $finishedOrderList[] = $order->id;
            }

            $this->model = $this->model->whereIn('id', $finishedOrderList);
        }
    }

    /**
     * Filtering orders by company id
     *
     * @param string $only_this_company_orders
     */
    public function filteringByCompanyId($only_this_company_orders = '')
    {
        if ($only_this_company_orders != '') {
            $this->model = $this->model->where('company_id', $only_this_company_orders);
        }
    }

    /**
     * Filtering orders by source id from orders table property (not from company!)
     *
     * @param string $reference_sources_id
     */
    public function filteringBySourceId($reference_sources_id = '')
    {
        if ($reference_sources_id != '') {
            $this->model = $this->model->where('reference_sources_id', $reference_sources_id);
        }
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
        $this->formData['creator_user_id'] = $this->user->id;

        $this->checkAndCreateNewEmployee();

        $this->model = parent::store($request);

        sleep(1);

        if (isset($this->formData['set_categories_ids'])) {

            foreach ($this->formData['set_categories_ids'] as $referenceCategoryId) {
                OrderCategory::create([
                    'order_id' => $this->model->id,
                    'reference_category_id' => $referenceCategoryId
                ]);
            }
        }

        if (isset($this->formData['set_services_ids'])) {

            foreach ($this->formData['set_services_ids'] as $referenceServiceId) {
                OrderService::create([
                    'order_id' => $this->model->id,
                    'reference_service_id' => $referenceServiceId,
                    'reference_property_status_id' => 303
                ]);
            }
        }
        $this->model->save();

        return $this->model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \App\Http\Controllers\Model|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->formData = $request->all();

        $this->model = $this->model::findOrFail($id);
        $previous_reference_order_type_id = $this->model->getOriginal('reference_order_type_id');

        $this->checkAndCreateNewEmployee();

        parent::update($request, $id);

        if (isset($this->formData['reference_order_type_id']) && $this->formData['reference_order_type_id'] == 2 && $previous_reference_order_type_id != $this->formData['reference_order_type_id']) {
            $this->sendNotificationHeadManager($id);
        }

        if (isset($this->formData['set_categories_ids'])) {
            CustomSync::orderCategorySync($this->formData['id'], $this->formData['set_categories_ids']);
        }

        if (isset($this->formData['set_services_ids'])) {
            CustomSync::orderServicesSync($this->formData['id'], $this->formData['set_services_ids']);
        }

        $this->model->save();

        return $this->model;
    }

    /**
     *  Check if employee selected or we need create new one and use it
     */
    public function checkAndCreateNewEmployee()
    {
        if (
            !isset($this->formData['company_employee_id'])
            && isset($this->formData['company_id']) && $this->formData['company_id'] != ''
            && $this->formData['company_employee']['lead_fio'] != ''
            && $this->formData['company_employee']['lead_phone'] != ''
        ) {
            $newEmployee = CompanyEmployee::create([
                'company_id' => $this->formData['company_id'],
                'creator_user_id' => $this->formData['creator_user_id'],
                'lead_fio' => $this->formData['company_employee']['lead_fio'],
                'lead_email' => $this->formData['company_employee']['lead_email'],
                'lead_phone' => $this->formData['company_employee']['lead_phone'],
            ]);

            $this->formData['company_employee_id'] = $newEmployee->id;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = parent::show($id);

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return parent::destroy($id);
    }

    /**
     * Create copy order and order_services, order_service_properties.
     *
     * @param $order_id
     * @return mixed
     */
    public function orderCopy($order_id)
    {
        return OrderCopy::orderCopy($order_id);
    }

    /**
     * Form data validation
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
        $validator = Validator::make($this->formData, [
            //            'name'  => 'required|string|min:2',
        ]);

        return $validator;
    }

    /**
     * Get orders by service id
     * @TODO Need Refactor and Move out
     *
     * @param int $id
     * @return mixed
     */
    public function getOrdersByServiceId(int $id)
    {
        $this->modelHeaders = $this->prepareSpecialHeaders($this->modelHeaders);

        $this->modelActionAllows = $this->prepareSpecialActions($this->modelActionAllows['all_roles']);

        $orderServices = $this->getOrderServicesListByServiceId($id);

        $orderIds = $orderServices->pluck('order_id')->toArray();

        $this->model = parent::elasticSearch((new Order()), request('q'), $orderIds);

        $this->model = $this->model->where("reference_order_type_id", "=", 2); // only in Postavka status

        $orders = parent::index();

        $orders = $this->addServiceStatusToOrders($orders, $orderServices);
        $orders = $this->addServiceUsersToOrders($orders, $orderServices);

        return $orders;
    }

    /**
     * Get list of OrderService elements
     * @param int $order_services_id
     * @return mixed
     */
    public function getOrderServicesListByServiceId(int $order_services_id)
    {
        $only_my_orders = empty(request('only_my_orders')) ? false : (bool) request('only_my_orders');

        $orderServices = OrderService::where('reference_service_id', $order_services_id);

        if ($this->user->canShowAll() && !$only_my_orders) {
            $orderServices = $orderServices->with(['users' => function ($query) {
                $query->where('user_id', $this->user->id);
            }]);
        }

        $orderServices = $orderServices->get(['id', 'order_id', 'reference_property_status_id']);

        $orderServices->load(['status', 'users']);

        return $orderServices;
    }

    /**
     * DELETE action from action allows
     * @TODO Maybe move to bundles and make universal
     * @param array $actionAllows
     * @return array
     */
    private function prepareSpecialActions(array $actionAllows)
    {
        $actionAllows = array_filter($actionAllows, function ($actionValue, $actionKey) {
            if ($actionValue == 'show') {
                return $actionValue;
            }
        }, ARRAY_FILTER_USE_BOTH);

        return array_values($actionAllows);
    }

    /**
     * Shorting table output for services
     * @TODO Maybe move to bundles and make universal
     * @param array $headers
     * @return array
     */
    private function prepareSpecialHeaders(array $headers)
    {
        $headers = array_filter($headers, function ($headerValue, $actionKey) {
            if (
                $headerValue['key'] != 'type.name' &&
                $headerValue['key'] != 'source.name' &&
                $headerValue['key'] != 'company.inn' &&
                $headerValue['key'] != 'company.status.name' &&
                $headerValue['key'] != 'receive_datetime_cast' &&
                $headerValue['key'] != 'processing_end_datetime_cast'
            ) {
                return $headerValue;
            }
        }, ARRAY_FILTER_USE_BOTH);

        $tempFirstHeader = array_shift($headers);
        $tempSecondHeader = array_shift($headers);
        array_unshift($headers, [
            'key' => 'service_status.name',
            'sortBy' => 'service_status.keyword',
            'label' => 'Статус услуги',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ]);
        array_unshift($headers, [
            'key' => 'service_users.name',
            'sortBy' => 'service_users.keyword',
            'label' => 'Ответственный',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ]);
        array_unshift($headers, $tempSecondHeader);
        array_unshift($headers, $tempFirstHeader);

        return array_values($headers);
    }

    /**
     * Add status of service to orders list
     *
     * @param array $orders
     * @param array $orderServices
     * @return array
     */
    private function addServiceStatusToOrders(array $orders, $orderServices)
    {
        $orderServices = $orderServices->toArray();
        foreach ($orders['data'] as $key => $order) {
            $order_id = $order['id'];

            $service_status = array_filter($orderServices, function ($orderService) use ($order_id) {
                if ($orderService['order_id'] == $order_id) {
                    return $orderService['status'];
                }
            });
            $service_status = array_values($service_status);

            if (count($service_status)) {
                $orders['data'][$key]['service_status'] = $service_status[0]['status'];
            }
        }

        return $orders;
    }

    /**
     * Add users of service to orders list
     *
     * @param array $orders
     * @param array $orderServices
     * @return array
     */
    private function addServiceUsersToOrders(array $orders, $orderServices)
    {
        $orderServices = $orderServices->toArray();
        foreach ($orders['data'] as $key => $order) {
            $order_id = $order['id'];

            $service_users = array_filter($orderServices, function ($orderService) use ($order_id) {
                if ($orderService['order_id'] == $order_id) {
                    return $orderService['users'];
                }
            });
            $service_users = array_values($service_users);

            if (count($service_users)) {
                $orders['data'][$key]['service_users'] = $service_users[0]['users'][0]; // Берем пока только первого юзера
            }
        }

        return $orders;
    }

    /**
     * Send notification head managers, when order_type == 2
     * @param int $id
     */
    protected function sendNotificationHeadManager(int $id)
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('slug', 'LIKE', "%head-manager");
        })->get();

        $parameters = [
            'order_id' => $id,
            'place' => ['left_menu']
        ];

        Notification::locale('ru')->send($users, new NotificationsUsers("Обращение $id переведен в поставку.", $parameters));
    }
}
