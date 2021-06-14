<?php

namespace App\Models;

use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Bundles\Elasticsearch\ContractTOIndexConfigurator;

class ContractTO extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = ContractTOIndexConfigurator::class;

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
            'to_contract' => [
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
            'to_start_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'to_sms_sended' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'to_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'to_status' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'to_no_access_times' => [
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
            'to_master_user_id' => [
                "type" => "long"
            ],
            'to_contract_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'contracts_to';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'to_contract_id',
        'to_master_user_id',
        'to_start_datetime',
        'to_comment',
        'to_status',
        'to_no_access_times',
        'to_sms_sended',
        'to_email_sended',
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
                'to_contract'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'master',
                'to_contract',
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
        'sortBy' => 'to_start_datetime',
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
            'key' => 'to_contract.contract_number',
            'sortBy' => 'to_contract.keyword',
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
            'key' => 'to_start_datetime',
            'sortBy' => 'to_start_datetime',
            'label' => 'ТО назначено на дату',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'to_sms_sended',
            'sortBy' => 'to_sms_sended.keyword',
            'label' => 'SMS отправлено',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'to_comment',
            'sortBy' => 'to_comment',
            'label' => 'Комментарий',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'to_status',
            'sortBy' => 'to_status.keyword',
            'label' => 'Статус',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'to_no_access_times',
            'sortBy' => 'to_no_access_times.keyword',
            'label' => 'Клиент не обеспечил доступ',
            'sortable' => false,
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
        return $this->hasOne(User::class, 'id', 'to_master_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Contract table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function to_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'to_contract_id')
            ->select(['id', 'contract_number', 'contract_address']);
    }

    /**
     * User table relationships hasOneThrough
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOneThrough
     */
    public function to_contract_for_user()
    {
        return $this->hasOneThrough(User::class, Contract::class, 'id', 'id', 'to_contract_id', 'contract_on_user_id')
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
            'master' => isset($this->master->name) ? $this->master->name : '',
            'to_contract' => isset($this->to_contract->contract_number) ? $this->to_contract->contract_number : '',
            'to_status' => isset($this->to_status) ? $this->to_status : '',
            'to_no_access_times' => isset($this->to_no_access_times) ? $this->to_no_access_times : 0,
            'to_comment' => isset($this->to_comment) ? $this->to_comment : '',
            'to_start_datetime' => isset($this->to_start_datetime) ? date('d.m.Y H:i', strtotime($this->to_start_datetime)) : '01.01.1900 00:00',
            'to_sms_sended' => isset($this->to_sms_sended) ? date('d.m.Y H:i', strtotime($this->to_sms_sended)) : '01.01.1900 00:00',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'to_master_user_id' => isset($this->to_master_user_id) ? $this->to_master_user_id : null,
            'to_contract_id' => isset($this->to_contract_id) ? $this->to_contract_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
