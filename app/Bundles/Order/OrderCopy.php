<?php


namespace App\Bundles\Order;


use App\Models\Order;
use App\Models\OrderService;
use App\Models\OrderServiceProperty;
use DateTime;

class OrderCopy
{
    /**
     * Copy order.
     *
     * @param $order_id
     * @return mixed
     */
    public static function orderCopy($order_id)
    {
        // original order
        $order = Order::findOrFail($order_id);

        // change fields original order
        $order->reference_close_reason_id = null;
        $order->close_comment = null;
        $order->reference_status_id = 359;
        $order->reference_order_type_id = 1;
        $order->receive_datetime = null;
        $order->processing_end_datetime = null;

        // add copy order
        $copyOrder = Order::create($order->toArray());

        // copy order_categories
        $copyOrder->categories()->attach($order['set_categories_ids']);

        // copy order services
        $copyOrder->services()->attach($order['set_services_ids'], ['reference_property_status_id' => 303]);

        // services original order
        $orderServices = OrderService::where('order_id', $order->id)->get();

        $copyOrderServicesProperties = [];
        foreach ($orderServices as $index => $service) {

            // get service property by order_service_id
            $serviceProperty = OrderServiceProperty::where('order_service_id', $service['id'])->get()->toArray();

            // get ids copy order_services
            $copyOrderServiceId = OrderService::select('id')
                ->where('order_id', $copyOrder->id)
                ->where('reference_service_id', $service['reference_service_id'])
                ->first();

            $copyOrderServicesProperties = [];
            foreach ($serviceProperty as $key => $property) {

                // change order_service_id
                $property['order_service_id'] = $copyOrderServiceId->id;

                // validate service property
                $property['value'] = self::validateValueOrderServiceProperty($property['value']);

                $copyOrderServicesProperties[$key] = array_filter($property,
                    function ($key) {
                        return !in_array($key, ['id', 'reference_service_property_name', 'reference_service_property_ordering_number']);
                    },
                    ARRAY_FILTER_USE_KEY);
            }

            // add properties
            OrderServiceProperty::insert($copyOrderServicesProperties);
        }

        $loads = $copyOrder->getLoads();

        return $copyOrder->load($loads['all_roles']);
    }

    /**
     * Validate and clear if order service property is time or date.
     *
     * @param $value
     * @return |null
     */
    private static function validateValueOrderServiceProperty($value)
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);

        if ($date && $date->format('Y-m-d') === $value) {
            return null;
        } else {
            $time = DateTime::createFromFormat('H:i', $value);
            if ($time && $time->format('H:i') === $value) {
                return null;
            }
        }

        return $value;
    }
}
