<?php

namespace App\Http\Controllers\API\Notification;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\CrudController;

class NotificationController extends CrudController
{
    /**
     * NotificationController constructor.
     *
     * @param Notification $model
     */
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all notifications(reads/no reads)
     *
     * @return mixed
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $this->model = Notification::where('notifiable_id', $userId)
            ->whereNull('read_at');

        $notifications = parent::index();

        foreach ($notifications['data'] as $key => $notification) {
            $notifications['data'][$key]['data'] = json_decode($notification['data']);
        }

        return $notifications;
    }

    /**
     * Get all active notifications
     *
     * @return mixed
     */
    public function getUnread()
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications;

        return $notifications;
    }

    /**
     * Read notification by id
     *
     * @param $id
     * @return mixed
     */
    public function readById($id)
    {
        $userId = auth()->user()->id;

        Notification::where('id', $id)
            ->where('notifiable_id', $userId)
            ->update(['read_at' => Carbon::now()]);
    }

    public function readForAllOrders()
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications;
        $place = empty(request('place')) ? '' : request('place');

        foreach ($notifications as $notification) {
            if (
                isset($notification->data['parameters']['order_id']) &&
                $notification->data['parameters']['place'] &&
                in_array($place, $notification->data['parameters']['place'])
            ) {
                $notification->update(['read_at' => Carbon::now()]);
            }
        }
    }

    /**
     * Read all notifications by services id
     *
     * @param $id
     * @return mixed
     */
    public function readByServiceReferenceId($id)
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications;

        foreach ($notifications as $notification) {
            if (isset($notification->data['parameters']['service_id']) && $notification->data['parameters']['service_id'] == $id) {
                $notification->update(['read_at' => Carbon::now()]);
            }
        }
    }

    /**
     * Read all finance requests notifications by creator user id
     *
     * @param $id
     * @return mixed
     */
    public function readFinanceRequestsByCreatorUserId($id)
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications;

        foreach ($notifications as $notification) {
            if (isset($notification->data['parameters']['creator_user_id']) && $notification->data['parameters']['creator_user_id'] == $id) {
                $notification->update(['read_at' => Carbon::now()]);
            }
        }
    }

    /**
     * Read all active notifications
     */
    public function readAll()
    {
        $user = auth()->user();

        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Delete notification by id
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function deleteById($id)
    {
        return parent::destroy($id);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $user = auth()->user();

        $user->notifications()->delete();
    }
}
