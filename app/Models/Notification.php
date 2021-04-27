<?php

namespace App\Models;

use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory, ModelGettersTrait;

    /**
     * Type primary key
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Increments off
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Action allows for roles
     *
     * @var array
     */
    protected $actionAllows = [];

    /**
     * Default params for sort
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'read_at',
        'sortDirection' => 'desc'
    ];

    /**
     * Load relationship
     *
     * @var array
     */
    protected $loads = [];

    /**
     * Fields for head table.
     *
     * @var array
     */
    protected $tableHeaders = [];

    /**
     * Customize format
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
        'read_at' => 'datetime:d.m.Y H:i',
    ];
}
