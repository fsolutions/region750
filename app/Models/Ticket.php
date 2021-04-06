<?php

namespace App\Models;

use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Bundles\Elasticsearch\TicketIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = TicketIndexConfigurator::class;

    /**
     * Mapping
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [

            // table fields
            'id' => [
                "type" => "long"
            ],
            'creator' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'type' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'description' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'created_at' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],

            // other fields
            'id_search' => [
                "type" => "keyword"
            ],
            'creator_user_id' => [
                "type" => "long"
            ],
            'reference_type_ticket_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'creator_user_id',
        'reference_type_ticket_id',
        'description'
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
                'screenshots',
                'creator',
                'type'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'screenshots',
                'creator',
                'type'
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
        [
            'key' => 'id',
            'label' => 'Номер',
            'sortBy' => 'id',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'creator.name',
            'sortBy' => 'creator.keyword',
            'label' => 'Пользователь',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'type_ticket.name',
            'sortBy' => 'type.keyword',
            'label' => 'Пользователь',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'description',
            'sortBy' => 'description.keyword',
            'label' => 'Описание',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => 'Дата',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ]
    ];

    /**
     * Documents table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function screenshots()
    {
        return $this->hasMany(Document::class, 'ticket_id', 'id')
            ->select(['id', 'name', 'ticket_id', 'path', 'folder']);
    }

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'creator_user_id')
            ->select(['id', 'name', 'email']);
    }

    /**
     * Reference_properties table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'reference_type_ticket_id')
            ->select(['id', 'name']);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $relationships = $this->getLoads('other_actions');

        $this->load($relationships['all_roles']);

        $tableFields = [
            'id' => $this->id,
            'creator' => isset($this->creator->name) ? $this->creator->name : '',
            'type' => isset($this->type->name) ? $this->type->name : '',
            'description' => isset($this->description) ? $this->description : '',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id,
            'creator_user_id' => isset($creator_user_id) ? $creator_user_id : null,
            'reference_type_ticket_id' => isset($reference_type_ticket_id) ? $reference_type_ticket_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
