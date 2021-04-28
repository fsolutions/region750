<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use App\Models\Contract;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Bundles\Elasticsearch\PrescriptionIndexConfigurator;

class Prescription extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = PrescriptionIndexConfigurator::class;

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
            'prescription_number' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'prescription_contract' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'master' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'prescription_start_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'prescription_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'prescription_status' => [
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
            'prescription_master_user_id' => [
                "type" => "long"
            ],
            'prescription_contract_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'prescriptions';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'prescription_number',
        'prescription_contract_id',
        'prescription_master_user_id',
        'prescription_start_datetime',
        'prescription_comment',
        'prescription_status'
    ];

    /**
     * Customize format.
     *
     * @var array
     */
    // protected $casts = [
    //     'created_at' => 'datetime:d.m.Y H:i',
    //     'updated_at' => 'datetime:d.m.Y H:i',
    //     'deleted_at' => 'datetime:d.m.Y H:i',
    // ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'master',
                'prescription_contract',
                'prescription_order'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'master',
                'prescription_contract',
                'prescription_order',
                'to_contract_for_user'
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
            // 'show'
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
        'sortBy' => 'prescription_start_datetime',
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
            'key' => 'prescription_contract.contract_number',
            'sortBy' => 'prescription_contract.keyword',
            'label' => 'Номер договора',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'prescription_number',
            'sortBy' => 'prescription_number.keyword',
            'label' => 'Номер предисания',
            'stickyColumn' => true,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'prescription_comment',
            'sortBy' => 'prescription_comment',
            'label' => 'Содержимое предписания',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'master.name',
            'sortBy' => 'master.keyword',
            'label' => 'Составитель предписания',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'prescription_start_datetime',
            'sortBy' => 'prescription_start_datetime',
            'label' => 'Дата к исполнению',
            'sticky' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'prescription_status',
            'sortBy' => 'prescription_status',
            'label' => 'Статус',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => 'Дата добавления',
            'stickyColumn' => false,
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
        return $this->hasOne(User::class, 'id', 'prescription_master_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Contract table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function prescription_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'prescription_contract_id')
            ->select(['id', 'contract_number', 'contract_address']);
    }

    /**
     * Order table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function prescription_order()
    {
        return $this->hasOne(Order::class, 'order_prescription_id', 'id');
    }

    /**
     * User table relationships hasOneThrough
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOneThrough
     */
    public function to_contract_for_user()
    {
        return $this->hasOneThrough(User::class, Contract::class, 'id', 'id', 'prescription_contract_id', 'contract_on_user_id')
            ->select(['users.id', 'users.name', 'users.phone']);
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
            'prescription_number' => $this->prescription_number,
            'master' => isset($this->master->name) ? $this->master->name : '',
            'prescription_contract' => isset($this->prescription_contract->contract_number) ? $this->prescription_contract->contract_number : '',
            'prescription_status' => isset($this->prescription_status) ? $this->prescription_status : '',
            'prescription_comment' => isset($this->prescription_comment) ? $this->prescription_comment : '',
            'prescription_start_datetime' => isset($this->prescription_start_datetime) ? date('d.m.Y H:i', strtotime($this->prescription_start_datetime)) : '01.01.1900 00:00',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id,
            'prescription_master_user_id' => isset($this->prescription_master_user_id) ? $this->prescription_master_user_id : null,
            'prescription_contract_id' => isset($this->prescription_contract_id) ? $this->prescription_contract_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
