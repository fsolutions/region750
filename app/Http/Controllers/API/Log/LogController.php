<?php

namespace App\Http\Controllers\API\Log;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Order;
use App\Models\OrderCategory;
use App\Models\OrderService;
use App\Models\OrderServiceProperty;
use App\Models\UserService;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * logs
     *
     * @var
     */
    private $logs;

    /**
     * Log
     *
     * @var
     */
    private $log;

    /**
     * Model
     *
     * @var
     */
    private $model;

    /**
     * Key array for delete
     *
     * @var array
     */
    private $disabled = [];

    /**
     * Get all logs
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $request->only('id', 'model');

        $this->logs = Log::where('model_name', $data['model'])
            ->where('model_id', $data['id'])
            ->with('user')
            ->get();

        $this->fieldsValueJsonDecode();

        return $this->logs;
    }

    /**
     * Give logs for order
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function order(Request $request)
    {
        $orderId = $request->get('id');

        $logsOrder = Log::where('model_id', '=', $orderId)
            ->where('model_name', '=', 'App\Models\Order')
            ->with('user')
            ->select('id', 'user_id', 'operation_name', 'created_at', 'fields_value')
            ->get()->toArray();

        foreach ($logsOrder as $logOrder) {
            $logOrder['name'] = 'Обращение';
            $this->logs[] = $logOrder;
        }

        $orderCategories = OrderCategory::withTrashed()
            ->where('order_id', '=', $orderId)
            ->with('reference')
            ->get()->toArray();

        foreach ($orderCategories as $category) {
            $logsCategories = Log::where('model_id', '=', $category['id'])
                ->where('model_name', '=', 'App\Models\OrderCategory')
                ->with('user')
                ->select('id', 'user_id', 'operation_name', 'created_at')
                ->get()->toArray();

            foreach ($logsCategories as $logCategory) {
                $logCategory['name'] = $category['reference']['name'] . ' (категория)';
                $this->logs[] = $logCategory;
            }
        }

        $orderServices = OrderService::withTrashed()
            ->where('order_id', '=', $orderId)
            ->with('reference')
            ->get()->toArray();

        foreach ($orderServices as $service) {
            $logsService = Log::where('model_id', '=', $service['id'])
                ->where('model_name', '=', 'App\Models\OrderService')
                ->with('user')
                ->select('id', 'user_id', 'operation_name', 'created_at')
                ->get()->toArray();

            foreach ($logsService as $logService) {
                $logService['name'] = $service['reference']['name'] . ' (услуга)';
                $this->logs[] = $logService;
            }

            $userServicesIds = UserService::where('service_id', '=', $service['id'])->get('id')->toArray();

            foreach ($userServicesIds as $userServiceId) {
                $logsUserService = Log::where('model_id', '=', $userServiceId['id'])
                    ->where('model_name', '=', 'App\Models\UserService')
                    ->with('user')
                    ->select('id', 'user_id', 'operation_name', 'created_at', 'fields_value')
                    ->get()->toArray();

                foreach ($logsUserService as $logUserService) {
                    $logUserService['name'] = 'Менеджер (исполнитель)';
                    $this->logs[] = $logUserService;
                }
            }

            $properties = OrderServiceProperty::where('order_service_id', '=', $service['id'])
                ->whereNotNull('value')
                ->get()->toArray();

            foreach ($properties as $property) {
                $logsProperties = Log::where('model_id', '=', $property['id'])
                    ->where('model_name', '=', 'App\Models\OrderServiceProperty')
                    ->with('user')
                    ->select('id', 'user_id', 'operation_name', 'created_at', 'fields_value')
                    ->get()->toArray();

                foreach ($logsProperties as $logProperty) {
                    $logProperty['name'] = 'Свойство';
                    $logProperty['reference_service_property_name'] = $property['reference_service_property_name'];
                    $logProperty['order_service_name'] = $service['reference']['name'];
                    $this->logs[] = $logProperty;
                }
            }
        }

        $this->fieldsValueJsonDecode();

        $this->logs = collect($this->logs);

        $this->logs = $this->logs->sortByDesc('created_at');

        return $this->logs->values()->all();
    }

    /**
     * Logs field value json decode
     */
    private function fieldsValueJsonDecode()
    {

        foreach ($this->logs as $key => $this->log) {

            if (isset($this->log['fields_value'])) {
                $this->log['fields_value'] = (array) json_decode($this->log['fields_value']);
                $this->log['fields_value'] = $this->cleaningFieldsValue($this->log['fields_value']);

                switch ($this->log['name']) {
                    case 'Обращение':
                        $this->model = new Order();
                        $this->disabled = $this->model->getAppends();
                        $this->updateFieldsValueToOrder();
                        $this->log['fields_value'] = $this->cleaningFieldsValue($this->log['fields_value'], [
                            'company_id',
                            'creator_user_id',
                            'reference_status_id',
                            'reference_sources_id',
                            'sales_manager_user_id',
                            'reference_order_type_id',
                        ]);
                        break;
                    case 'Менеджер (исполнитель)':
                        $this->model = new UserService();
                        $this->updateFieldsValueToUserServices();
                        break;
                    case 'Свойство':
                        $this->log['fields_value']['reference_service_property_name'] = $this->log['reference_service_property_name'];
                        $this->log['fields_value']['order_service_name'] = $this->log['order_service_name'];
                        break;
                }

                $this->logs[$key]['fields_value'] = $this->log['fields_value'];
            }
        }
    }

    /**
     * Update fields value to UserService log
     */
    private function updateFieldsValueToUserServices()
    {
        if (isset($this->log['fields_value']['user_id'])) {
            $relation = $this->getRelation('user_id', 'user');
            $this->log['fields_value']['user_name'] = $relation->name;
            $this->log['fields_value']['service_name'] = '1';
        }

        if (isset($this->log['fields_value']['service_id'])) {
            $relation = $this->getRelation('service_id', 'service');
            $relation->load('reference');
            $relation = $this->getRelation('service_id', 'service');
            $this->log['fields_value']['service_name'] = $relation->reference->name;
        }
    }

    /**
     * Update fields value to order log
     */
    private function updateFieldsValueToOrder()
    {
        if (isset($this->log['fields_value']['reference_status_id'])) {
            $relation = $this->getRelation('reference_status_id', 'status');
            $this->log['fields_value']['reference_status_name'] = $relation->name;
        }

        if (isset($this->log['fields_value']['creator_user_id'])) {
            $relation = $this->getRelation('creator_user_id', 'creator');
            $this->log['fields_value']['creator_user_name'] = $relation->name;
            $this->log['fields_value']['creator_user_position'] = $relation->position;
        }

        if (isset($this->log['fields_value']['reference_order_type_id'])) {
            $relation = $this->getRelation('reference_order_type_id', 'type');
            $this->log['fields_value']['reference_order_type_name'] = $relation->name;
        }

        if (isset($this->log['fields_value']['sales_manager_user_id'])) {
            $relation = $this->getRelation('sales_manager_user_id', 'sales_manager');
            $this->log['fields_value']['sales_manager_user_name'] = $relation->name;
            $this->log['fields_value']['sales_manager_user_position'] = $relation->position;
        }

        if (isset($this->log['fields_value']['reference_sources_id'])) {
            $relation = $this->getRelation('reference_sources_id', 'source');
            $this->log['fields_value']['reference_sources_name'] = $relation->name;
        }

        if (isset($this->log['fields_value']['company_id'])) {
            $relation = $this->getRelation('company_id', 'company');
            $this->log['fields_value']['company_name'] = $relation->name;
        }
    }

    /**
     * Get relation
     *
     * @param $field_name
     * @param $relation_name
     * @return mixed
     */
    private function getRelation($field_name, $relation_name)
    {
        $this->model->$field_name = $this->log['fields_value'][$field_name];
        $this->model->load($relation_name);
        return $this->model->$relation_name;
    }

    /**
     * Сleaning fields_value to logs
     *
     * @param array $array
     * @return array
     */
    private function cleaningFieldsValue(array $array, array $disabled = [])
    {
        $disabled = array_merge($this->disabled, $disabled);

        return array_filter(
            $array,
            function ($key) use ($disabled) {
                return !in_array($key, $disabled);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
