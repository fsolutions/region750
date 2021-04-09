<?php

namespace App\Observers;

use App\Models\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UniversalObserver
{
    /**
     * Filds for add logs table.
     *
     * @var array
     */
    public $fields = [];

    /**
     * UniversalObserver constructor.
     */
    public function __construct()
    {
        $this->fields = [
            'id' => Str::uuid(),
            'user_id' => Auth::user()->id,
        ];
    }

    /**
     * Handle the company "created" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function created($model)
    {
        $this->fields += [
            'operation_name' => 'created',
            'model_id' => $model->id,
            'model_name' => get_class($model),
            'fields_value' => json_encode($model->toArray(), JSON_UNESCAPED_UNICODE)
        ];
    }

    /**
     * Handle the company "updated" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function updated($model)
    {
        $this->fields += [
            'operation_name' => 'updated',
            'model_id' => $model->id,
            'model_name' => get_class($model),
            'fields_value' => json_encode($model->getChanges(), JSON_UNESCAPED_UNICODE)
        ];
    }

    /**
     * Handle the company "deleted" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function deleted($model)
    {
        $this->fields += [
            'operation_name' => 'deleted',
            'model_id' => $model->id,
            'model_name' => get_class($model),
            'fields_value' => null
        ];
    }

    /**
     * UniversalObserver __destructor.
     */
    public function __destruct()
    {
        //  Log::addNewLog($this->fields);
    }
}
