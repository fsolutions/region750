<?php

namespace App\Models;

use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceProperty extends Model
{
    use HasFactory, ModelGettersTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'reference_properties';

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'name',
        'sortDirection' => 'asc'
    ];

    /**
     * Fields for head table.
     *
     * @var array
     */
    protected $tableHeaders = [
        ["key" => 'id', "label" => 'ID', "sortable" => true, "sortDirection" => 'desc'],
        ["key" => 'name', "label" => 'Свойство', "sortable" => true, "sortDirection" => 'asc'],
        ["key" => 'reference_id', "label" => 'Содержится в справочнике', "sortable" => true, "sortDirection" => 'desc'],
        ["key" => 'need_reference', "label" => 'Треубет значения - свойство', "sortable" => false, "sortDirection" => 'desc'],
        ["key" => 'ordering_number', "label" => 'Порядковый номер сортировки', "sortable" => true, "sortDirection" => 'desc'],
        ["key" => 'actions', "label" => 'Действия']
    ];

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
        'name',
        'reference_id',
        'need_reference',
        'ordering_number'
    ];

    protected $loads = [
        'index' => [
            'all_roles' => [
            ],
        ],
        'other_actions' => [
            'all_roles' => [
            ]
        ]
    ];

    /**
     * Reference table relationship One To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reference()
    {
        return $this->hasOne(Reference::class, 'id', 'reference_id');
    }

    /**
     * Orders table relationship Many To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_categories', 'reference_category_id', 'order_id');
    }
}
