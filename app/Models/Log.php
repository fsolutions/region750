<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    /**
     * Disabled updated_at.
     */
    const UPDATED_AT = null;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $timestamps = [
        'created_at'
    ];

    /**
     * Customize format.
     *
     * @var array
     */
    // protected $casts = [
    //     'created_at' => 'datetime:d.m.Y H:i',
    // ];

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'operation_name',
        'user_id',
        'model_name',
        'model_id',
        'fields_value',
    ];

    /**
     * Add new record logs table.
     *
     * @param array $fields
     */
    public static function addNewLog(array $fields)
    {
        Log::create($fields);
    }

    /**
     * User rekationships One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
