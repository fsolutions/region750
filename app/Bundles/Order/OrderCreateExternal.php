<?php


namespace App\Bundles\Order;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderCreateExternal
{
    /**
     * Create order.
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $commentRequest = $request->all();

        $comment = '';
        foreach ($commentRequest as $key => $field) {
            if ($key != 'FORM_NAME' || $key != 'CALL_MAIL_TO') {
                $comment .= $this->getRealFieldNamesByAlias($key) . $field . "\r\n";
            }
        }
        $order = [
            'creator_user_id' => 1,
            'temp_name' => $commentRequest['FORM_NAME'],
            'short_order' => true,
            'reference_order_type_id' => 1,
            'reference_status_id' => 359,
            'reference_sources_id' => 23,
            'comment' => $comment,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return Order::insert($order);
    }

    public function getRealFieldNamesByAlias($alias) {
        $name = '';

        switch ($alias) {
            case "CALL_NAME":
            case "CONS_NAME":
                $name = "Имя: ";
                break;
            case "CALC_PHONE":
            case "CALL_PHONE":
            case "CONS_PHONE":
                $name = "Телефон: ";
                break;
            case "CALC_EMAIL":
            case "CONS_EMAIL":
                $name = "E-mail: ";
                break;
            case "CALC_TABLE":
                $name = "";
                break;
            case "CALL_MESSAGE":
            case "CONS_MESSAGE":
                $name = "Сообщение: ";
                break;
            case "PRICE_FROM":
                $name = "Откуда: ";
                break;
            case "PRICE_TO":
                $name = "Куда: ";
                break;
            case "PRICE_TYPE":
                $name = "Тип груза: ";
                break;
            case "PRICE_WEIGHT":
                $name = "Вес, кг: ";
                break;
            case "PRICE_CONTACT":
                $name = "Телефон: ";
                break;
            case "PRICE_PRODUCT":
            case "CONS_PRODUCT":
                $name = "Услуга: ";
                break;
            case "CALL_DATE":
                $name = "Когда перезвонить: ";
                break;
        }

        return $name;
    }
}
