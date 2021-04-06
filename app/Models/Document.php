<?php

namespace App\Models;

use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait;

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'folder',
        'name',
        'description',
        'order_id',
        'reference_document_type_id',
        'date_of_document',
        'creator_user_id',
        'ticket_id'
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'documents';

    /**
     * Customize format.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
        'deleted_at' => 'datetime:d.m.Y H:i',
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'order',
                'type',
                'creator',
                'ticket'
            ],
        ],
        'other_actions' => [
            'all_roles' => [
                'order',
                'type',
                'creator',
                'ticket'
            ]
        ]
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
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'id',
        'sortDirection' => 'desc'
    ];

    /**
     * Fields for head table.
     *
     * @var array
     */
    protected $tableHeaders = [
        ['key' => 'id', 'label' => 'ID', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'name', 'label' => 'Наименование', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'description', 'label' => 'Описание', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'creator.name', 'label' => 'Добавлено', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'reference_document_type_id', 'label' => 'Тип документа', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'type.name', 'label' => 'Тип документа', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'created_at', 'label' => 'Дата добавления', 'sortable' => true, 'sortDirection' => 'desc'],
        ['key' => 'actions', 'label' => 'Действия']
    ];

    /**
     * PropertyReference table relationships One to Mane (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(ReferenceProperty::class, 'reference_document_type_id');
    }

    /**
     * Orders table relationships One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Users table relationships One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'creator_user_id');
    }

    /**
     * Tickets table relationships One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
