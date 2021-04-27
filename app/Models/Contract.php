<?php

namespace App\Models;

use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Bundles\Elasticsearch\ContractIndexConfigurator;

class Contract extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = ContractIndexConfigurator::class;

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
            'contract_on_user' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'contract_to_last' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'contract_address' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'contract_number' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'status' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'contract_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'contract_start_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
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
            'contract_on_user_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'contracts';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'creator_user_id',
        'contract_on_user_id',
        'contract_address',
        'contract_number',
        'status',
        'contract_start_datetime',
        'contract_comment'
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
                'creator',
                'contract_on_user',
                'contract_to',
                'contract_to_last'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'creator',
                'contract_on_user',
                'contract_to',
                'contract_to_last',
                'orders',
                'prescriptions'
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
            'delete',
            'add-contract-to'
        ],
        'client' => [
            'create',
            'show',
        ],
        'all_roles' => [
            'create',
            'show',
            'edit',
            'add-contract-to'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'contract_to_last.keyword',
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
            'key' => 'contract_number',
            'sortBy' => 'contract_number.keyword',
            'label' => 'Номер договора',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract_address',
            'sortBy' => 'contract_address.keyword',
            'label' => 'Адрес объекта',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract_to_last',
            'sortBy' => 'contract_to_last.keyword',
            'label' => 'Назначенное ТО',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract_on_user.name',
            'sortBy' => 'contract_on_user.keyword',
            'label' => 'Пользователь договора',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'creator.name',
            'sortBy' => 'creator.keyword',
            'label' => 'Кто завел договор',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'status',
            'sortBy' => 'status.keyword',
            'label' => 'Статус',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract_comment',
            'sortBy' => 'contract_comment.keyword',
            'label' => 'Комментарий',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract_start_datetime',
            'sortBy' => 'contract_start_datetime',
            'label' => 'Дата заключения договора',
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
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'creator_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_on_user()
    {
        return $this->hasOne(User::class, 'id', 'contract_on_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * ContractTO table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contract_to()
    {
        return $this->hasMany(ContractTO::class, 'to_contract_id', 'id')->orderBy('to_start_datetime', 'desc')->with(['master']);
    }

    /**
     * ContractTO table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contract_to_last()
    {
        return $this->hasMany(ContractTO::class, 'to_contract_id', 'id')->orderBy('to_start_datetime', 'desc');
        //->take(1); ---> why it not works?????
    }

    /**
     * Orders table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_contract_id', 'id')->orderBy('created_at', 'desc');
    }

    /**
     * Prescriptions table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'prescription_contract_id', 'id')->orderBy('prescription_start_datetime', 'desc');
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

        $contract_to_dates = '';
        foreach ($this->contract_to_last as $contract) {
            $contract_to_dates .= date('d.m.Y H:i', strtotime($contract->to_start_datetime)) . ' ';
        }

        $tableFields = [
            'id' => $this->id,
            'creator' => isset($this->creator->name) ? $this->creator->name : '',
            'contract_to_last' => $contract_to_dates,
            'contract_on_user' => isset($this->contract_on_user->name) ? $this->contract_on_user->name : '',
            'contract_address' => isset($this->contract_address) ? $this->contract_address : '',
            'contract_number' => isset($this->contract_number) ? $this->contract_number : '',
            'status' => isset($this->status) ? $this->status : '',
            'contract_comment' => isset($this->contract_comment) ? $this->contract_comment : '',
            'contract_start_datetime' => isset($this->contract_start_datetime) ? date('d.m.Y H:i', strtotime($this->contract_start_datetime)) : '01.01.1900 00:00',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id,
            'creator_user_id' => isset($this->creator_user_id) ? $this->creator_user_id : null,
            'contract_on_user_id' => isset($this->contract_on_user_id) ? $this->contract_on_user_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
