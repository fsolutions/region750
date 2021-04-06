<?php

namespace App\Http\Controllers\API\User;

use App\Events\NewComment;
use App\Http\Controllers\CrudController;
use App\Models\UserComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserCommentController extends CrudController
{
    /**
     * UserCommentController constructor.
     *
     * @param $model
     */
    public function __construct(UserComment $model)
    {
        parent::__construct($model);
    }

    /**
     * Display a listing of the resource.
     *
     * @param null $field
     * @param null $id
     * @return array
     */
    public function index($field = null, $id = null)
    {
        switch ($field)
        {
            case 'order':
                $field = 'order_id';
                break;
            case 'document':
                $field = 'document_id';
                break;
            case 'service':
                $field = 'order_service_id';
                break;
            case 'tickets':
                $field = 'ticket_id';
                break;
            default:
                return abort(404);
        }

        $this->model = $this->model::where($field, '=', $id);

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

        $validator = $this->validationFormData('required');

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->prepareFormData();

        $this->model = parent::store($request);

        event(new NewComment($this->model));

        return $this->model;
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
        $this->formData = $request->all();

        $validator = $this->validationFormData();

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $this->model = $this->model::findOrFail($id);

        $this->checkAllowUser();

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
        return parent::destroy($id);
    }

    /**
     * Form data validation
     *
     * @param null $required
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validationFormData($required = null)
    {
        return Validator::make($this->formData, [
            'comment' => [$required],
            'document_id' => ['exists:documents,id'],
            'parent_comment_id'  => 'exists:user_comments,id',
            'order_service_id' => 'exists:order_services,id',
            'order_id' => 'exists:orders,id',
            'ticket_id' => 'exists:tickets,id'
        ]);
    }

    /**
     * Prepare form data
     *
     * @return void
     */
    private function prepareFormData()
    {
        $this->formData['creator_user_id'] = $this->user->id;
    }

    /**
     * Check allow show/update company for users.
     *
     * @param $model
     */
    private function checkAllowUser()
    {
        if($this->model->creator_user_id !== $this->user->id) {
            return abort(404);
        }
    }
}
