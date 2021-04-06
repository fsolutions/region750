<?php

namespace App\Http\Controllers\API\Ticket;

use App\Http\Controllers\CrudController;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NotificationsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class TicketController extends CrudController
{
    /**
     * TicketController constructor.
     *
     * @param $model
     */
    public function __construct(Ticket $model)
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
        if (!$this->checkUserIsAdmin()) {
            $this->model = $this->model::where('creator_user_id', $this->user->id);
        }

        return parent::index();
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

        $this->sendNotificationAdmin($this->model->id);

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
        $this->checkAllowUser($id);

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
        $this->checkAllowUser($id);

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
        $this->formData['creator_user_id'] = $this->user->id;
    }

    /**
     * Form data validation.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData()
    {
        return Validator::make($this->formData, [
            'reference_type_ticket_id'  => 'required|exists:reference_properties,id',
            'description'  => 'required|min:3'
        ]);
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
     * @param $ticket_id
     * @return bool|void
     */
    private function checkAllowUser($ticket_id)
    {
        if ($this->checkUserIsAdmin()) {
            return true;
        }

        $result = $this->model::where('creator_user_id', $this->user->id)->where('id', $ticket_id);

        if ($result->count()) {
            return true;
        }

        return abort('404');
    }

    /**
     * Send notification for admin.
     *
     * @param int $ticket_id
     */
    private function sendNotificationAdmin(int $ticket_id)
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('slug', 'administrator');
        })->get();

        $parameters = [
            'ticket_id' => $ticket_id,
            'place' => ['bell']
        ];

        Notification::locale('ru')->send($users, new NotificationsUsers("Новое обращение №$ticket_id.", $parameters));
    }
}
