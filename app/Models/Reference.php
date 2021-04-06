<?php

namespace App\Models;

use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reference extends Model
{
    use HasFactory, ModelGettersTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'references';

    /**
     * Action allows for roles
     *
     * @var array
     */
    protected $actionAllows = [
        'all_roles' => [
            'create',
            'show',
            'edit',
            'delete'
        ]
    ];

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'parent_id'
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'properties',
                'orders',
            ],
        ],
        'other_actions' => [
            'all_roles' => [
                'properties',
                'orders',
            ]
        ]
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'id',
        'sortDirection' => 'desc'
    ];

    /**
     * ReferenceProperty relationship One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function properties()
    {
        return $this->hasMany(ReferenceProperty::class, 'reference_id');
    }

    /**
     * Orders table relationship Many To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_services', 'reference_service_id')
            ->wherePivotNull('deleted_at');
    }
}
