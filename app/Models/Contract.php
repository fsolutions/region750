<?php

namespace App\Models;

use App\Models\City;
use App\Models\Flat;
use App\Models\User;
use App\Models\House;
use App\Models\Order;
use App\Models\Region;
use App\Models\Street;
use App\Models\Equipment;
use App\Models\ContractTO;
use App\Models\Prescription;
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
            // 'contract_address' => [
            //     "type" =>  "text",
            //     "fields" => [
            //         "keyword" => [
            //             "type" => "keyword"
            //         ]
            //     ]
            // ],
            'contractRealaddress' => [
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
            ],
            'contract_region_id' => [
                "type" => "long"
            ],
            'contract_city_id' => [
                "type" => "long"
            ],
            'contract_street_id' => [
                "type" => "long"
            ],
            'contract_house_id' => [
                "type" => "long"
            ],
            'contract_flat_id' => [
                "type" => "long"
            ],
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
        'contract_region_id',
        'contract_city_id',
        'contract_street_id',
        'contract_house_id',
        'contract_flat_id',
        'contract_number',
        'status',
        'contract_start_datetime',
        'contract_comment'
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'preparedEquipment',
        'contractRealaddress'
    ];

    /**
     * Get append of preparedEquipment
     * @return array
     */
    public function getPreparedEquipmentAttribute()
    {
        return [];
    }

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
                'contract_to_last',
                'equipment',
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'creator',
                'contract_on_user',
                'contract_to',
                'contract_to_last',
                'orders',
                'prescriptions',
                'equipment',
                'contract_region:id,name',
                'contract_city:id,name',
                'contract_street:id,name',
                'contract_house:id,name,build_year',
                'contract_flat:id,name',
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
            'edit',
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
            'key' => 'contractRealaddress',
            'sortBy' => 'contractRealaddress.keyword',
            'label' => 'Адрес объекта',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        // [
        //     'key' => 'contract_address',
        //     'sortBy' => 'contract_address.keyword',
        //     'label' => 'Адрес объекта',
        //     'sortable' => true,
        //     'sortDirection' => 'desc',
        //     'visible' => true
        // ],
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
     * Get append of contractRealaddress
     * @return array
     */
    public function getContractRealaddressAttribute()
    {
        $address = '';
        $address .= isset($this->contract_region) ? $this->contract_region->name . ' обл.' : '';
        $address .= isset($this->contract_city) ? ', ' . $this->contract_city->name : '';
        $address .= isset($this->contract_street) ? ', ' . $this->contract_street->name : '';
        $address .= isset($this->contract_house) ? ', ' . $this->contract_house->name : '';
        $address .= isset($this->contract_flat) ? ', ' . $this->contract_flat->name : '';

        $address .= isset($this->contract_house->build_year) ? ' (Год постройки: ' . $this->contract_house->build_year . ' г.)' : '';

        return $address;
    }

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
     * Equipment table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'equip_contract_id', 'id');
    }

    /**
     * Region table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_region()
    {
        return $this->hasOne(Region::class, 'id', 'contract_region_id')
            ->select(['id', 'name']);
    }

    /**
     * City table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_city()
    {
        return $this->hasOne(City::class, 'id', 'contract_city_id')
            ->select(['id', 'name']);
    }

    /**
     * Street table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_street()
    {
        return $this->hasOne(Street::class, 'id', 'contract_street_id')
            ->select(['id', 'name']);
    }

    /**
     * House table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_house()
    {
        return $this->hasOne(House::class, 'id', 'contract_house_id')
            ->select(['id', 'name', 'build_year']);
    }

    /**
     * Flat table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract_flat()
    {
        return $this->hasOne(Flat::class, 'id', 'contract_flat_id')
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

        $contract_to_dates = '';
        foreach ($this->contract_to_last as $contract) {
            $contract_to_dates .= date('d.m.Y H:i', strtotime($contract->to_start_datetime)) . ' ';
        }

        $tableFields = [
            'id' => $this->id,
            'creator' => isset($this->creator->name) ? $this->creator->name : '',
            'contract_to_last' => $contract_to_dates,
            'contract_on_user' => isset($this->contract_on_user->name) ? $this->contract_on_user->name : '',
            // 'contract_address' => isset($this->contract_address) ? $this->contract_address : '',
            'contractRealaddress' => $this->contractRealaddress,
            'contract_number' => isset($this->contract_number) ? $this->contract_number : '',
            'status' => isset($this->status) ? $this->status : '',
            'contract_comment' => isset($this->contract_comment) ? $this->contract_comment : '',
            'contract_start_datetime' => isset($this->contract_start_datetime) ? date('d.m.Y H:i', strtotime($this->contract_start_datetime)) : '01.01.1900 00:00',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'creator_user_id' => isset($this->creator_user_id) ? $this->creator_user_id : null,
            'contract_on_user_id' => isset($this->contract_on_user_id) ? $this->contract_on_user_id : null,
            'contract_region_id' => isset($this->contract_region_id) ? $this->contract_region_id : null,
            'contract_city_id' => isset($this->contract_city_id) ? $this->contract_city_id : null,
            'contract_street_id' => isset($this->contract_street_id) ? $this->contract_street_id : null,
            'contract_house_id' => isset($this->contract_house_id) ? $this->contract_house_id : null,
            'contract_flat_id' => isset($this->contract_flat_id) ? $this->contract_flat_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
