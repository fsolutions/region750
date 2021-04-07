<?php

namespace App\Models;

use App\Models\Contract;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Bundles\Elasticsearch\OrderIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = OrderIndexConfigurator::class;

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
            'order_contract' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            // 'order_prescription' => [
            //     "type" =>  "text",
            //     "fields" => [
            //         "keyword" => [
            //             "type" => "keyword"
            //         ]
            //     ]
            // ],
            'master' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'order_start_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'order_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'order_status' => [
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
            'order_master_user_id' => [
                "type" => "long"
            ],
            'order_contract_id' => [
                "type" => "long"
            ],
            'order_prescription_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'order_contract_id',
        'order_prescription_id',
        'order_master_user_id',
        'order_start_datetime',
        'order_comment',
        'order_status'
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
                'master',
                'order_contract'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'master'
            ]
        ]
    ];

    /**
     * Action allows for roles
     *
     * @var array
     */
    protected $actionAllows = [
        'administrator' => [
            'create',
            'show',
            'edit',
            'delete'
        ],
        'client' => [
            'show'
        ],
        'all_roles' => [
            'create',
            'show',
            'edit'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'order_start_datetime',
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
            'label' => 'ID',
            'sortBy' => 'id',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'order_contract.contract_number',
            'sortBy' => 'order_contract.keyword',
            'label' => 'Номер договора',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'master.name',
            'sortBy' => 'master.keyword',
            'label' => 'Мастер на ТО',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'order_comment',
            'sortBy' => 'order_comment',
            'label' => 'Комментарий',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'order_start_datetime',
            'sortBy' => 'order_start_datetime',
            'label' => 'Дата ТО',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'order_status',
            'sortBy' => 'order_status',
            'label' => 'Статус',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => 'Дата добавления',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'actions',
            'label' => 'Действия',
            'stickyColumn' => true,
            'visible' => true
        ]
    ];

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function master()
    {
        return $this->hasOne(User::class, 'id', 'order_master_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Contract table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'order_contract_id')
            ->select(['id', 'contract_number', 'contract_address']);
    }

    /**
     * Prescription table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order_prescription()
    {
        return $this->hasOne(Prescription::class, 'id', 'order_prescription_id');
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
            'master' => isset($this->master->name) ? $this->master->name : '',
            'order_contract' => isset($this->order_contract->contract_number) ? $this->order_contract->contract_number : '',
            'order_status' => isset($this->order_status) ? $this->order_status : '',
            'order_comment' => isset($this->order_comment) ? $this->order_comment : '',
            'order_start_datetime' => isset($this->order_start_datetime) ? date('d.m.Y H:i', strtotime($this->order_start_datetime)) : '01.01.1900 00:00',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id,
            'order_master_user_id' => isset($this->order_master_user_id) ? $this->order_master_user_id : null,
            'order_contract_id' => isset($this->order_contract_id) ? $this->order_contract_id : null,
            'order_prescription_id' => isset($this->order_prescription_id) ? $this->order_prescription_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
