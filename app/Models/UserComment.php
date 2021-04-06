<?php

namespace App\Models;

use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserComment extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait;

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'user_comments';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'comment',
        'parent_comment_id',
        'creator_user_id',
        'document_id',
        'order_id',
        'ticket_id'
    ];

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
                'user',
                'document',
                'order',
                'tickets'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'user',
                'document',
                'order',
                'tickets'
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
    protected $tableHeaders = [];

    /**
     * Relationships user table One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'creator_user_id', 'id');
    }

    /**
     * Comment has one parent comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent_comment()
    {
        return $this->hasOne(UserComment::class, 'parent_comment_id', 'id');
    }

    /**
     * Relationships documents table One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    /**
     * Relationships orders table One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Relationships tickets table One To Many (inverse)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tickets()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
