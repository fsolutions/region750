<?php

namespace App\Listeners;

use App\Bundles\Socket\PusherSocket;
use App\Events\NewComment;
use App\Models\UserDocumentAgreement;
use App\Notifications\NotificationsUsers;
use Illuminate\Support\Facades\Notification;
use ZMQ;
use ZMQContext;

class PushNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewComment  $event
     * @return void
     */
    public function handle(NewComment $event)
    {
        $comment = $event->comment->toArray();

        if (isset($comment['document_id']))
        {
            $category = "userDocumentAgreements{$comment['document_id']}";
            $field = 'document_id';
            $message =  'Новый комментарий (согласование документа).';

            $users = UserDocumentAgreement::where($field, '=', $comment[$field])->with('user')->get();

            foreach ($users as $key => $user)
            {
                $users[$key] = $user->user;
            }

            Notification::locale('ru')->send($users, new NotificationsUsers($message));

        } elseif (isset($comment['order_id'])) {

            $category = "userOrderAgreements{$comment['order_id']}";

        } elseif (isset($comment['order_service_id'])) {

            $category = "userOrderServiceAgreements{$comment['order_service_id']}";
        }

        PusherSocket::sendToDataServer(['category' => $category, 'data' => $comment]);
    }
}
