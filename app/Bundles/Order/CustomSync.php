<?php


namespace App\Bundles\Order;


use App\Models\OrderCategory;
use App\Models\OrderService;
use App\Models\UserService;

class CustomSync
{
    /**
     *
     *
     * @param $orderId
     * @param $setServicesIds
     */
    public static function orderServicesSync($orderId, $setServicesIds)
    {
        foreach ($setServicesIds as $key => $serviceId) {
            OrderService::withTrashed()->updateOrCreate(
                ['order_id' => $orderId, 'reference_service_id' => $serviceId],
                ['deleted_at' => null, 'reference_property_status_id' => 303]
            );
        }

        $activeServices = OrderService::where('order_id', $orderId)->get();

        $activeServicesIds = [];

        foreach ($activeServices as $key => $service) {
            $activeServicesIds[$service->id] = $service->reference_service_id;
        }

        $disableServicesIds = array_diff($activeServicesIds, $setServicesIds);

        $disableServicesIds = array_keys($disableServicesIds);

        OrderService::destroy($disableServicesIds);
    }

    /**
     * Sync to OrderCategory
     *
     * @param $orderId
     * @param $setCategoriesIds
     */
    public static function orderCategorySync($orderId, $setCategoriesIds)
    {
        foreach ($setCategoriesIds as $key => $categoryId) {
            OrderCategory::withTrashed()->updateOrCreate(
                ['order_id' => $orderId, 'reference_category_id' => $categoryId],
                ['deleted_at' => null]
            );
        }

        $activeCategories = OrderCategory::where('order_id', $orderId)->get();

        $activeCategoriesIds = [];

        foreach ($activeCategories as $key => $category) {
            $activeCategoriesIds[$category->id] = $category->reference_category_id;
        }

        $disableCategoriesIds = array_diff($activeCategoriesIds, $setCategoriesIds);

        $disableCategoriesIds = array_keys($disableCategoriesIds);

        OrderCategory::destroy($disableCategoriesIds);
    }

    /**
     * Sync to UserService
     *
     * @param $user_id
     * @param $service_id
     */
    public static function userServicesSync($set_users_id, $service_id)
    {
        foreach ($set_users_id as $key => $user_id) {
            UserService::withTrashed()->updateOrCreate(
                ['user_id' => $user_id, 'service_id' => $service_id],
                ['deleted_at' => null]
            );
        }

        $userService = UserService::where('service_id', $service_id)->get();

        $activeUserServiceIds = [];

        foreach ($userService as $key => $service) {
            $activeUserServiceIds[$service->id] = $service->user_id;
        }

        $disableUserServiceIds = array_diff($activeUserServiceIds, $set_users_id);

        $disableUserServiceIds = array_keys($disableUserServiceIds);

        UserService::destroy($disableUserServiceIds);
    }
}
