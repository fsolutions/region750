<?php

namespace App\Models;

use DateTime;
use App\Models\Contract;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Bundles\Elasticsearch\EquipmentIndexConfigurator;

class Equipment extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = EquipmentIndexConfigurator::class;

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
            'equip_user' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_contract' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_type' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_mark' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_date_of_release' => [
                "type" => "date",
                "format" => "dd.MM.yyyy"
            ],
            'equip_passport' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_years_of_use' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'percentage_of_wear' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'equip_comment' => [
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
            'equip_user_id' => [
                "type" => "long"
            ],
            'equip_contract_id' => [
                "type" => "long"
            ],
            'equip_type_reference_id' => [
                "type" => "long"
            ],
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'equip_user_id',
        'equip_contract_id',
        'equip_type_reference_id',
        'equip_mark',
        'equip_date_of_release',
        'equip_passport',
        'equip_comment'
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'equip_years_of_use',
        'percentage_of_wear',
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
                'equip_contract',
                'equip_type:id,name'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'equip_user:id,name,phone',
                'equip_contract',
                'equip_type:id,name'
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
            // 'show',
            'edit',
            'delete'
        ],
        'client' => [
            'create',
            // 'show'
        ],
        'all_roles' => [
            'create',
            'edit',
            // 'show'
        ],
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
        // [
        //     'key' => 'id',
        //     'label' => 'ID',
        //     'sortBy' => 'id',
        //     'stickyColumn' => true,
        //     'sortable' => true,
        //     'sortDirection' => 'desc',
        //     'visible' => true
        // ],
        [
            'key' => 'equip_type.name',
            'sortBy' => 'equip_type.keyword',
            'label' => 'Вид оборудования',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'equip_mark',
            'sortBy' => 'equip_mark',
            'label' => 'Марка',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'equip_date_of_release',
            'sortBy' => 'equip_date_of_release',
            'label' => 'Дата выпуска',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'equip_passport',
            'sortBy' => 'equip_passport',
            'label' => 'Номер паспорта',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'equip_years_of_use',
            'sortBy' => 'equip_years_of_use',
            'label' => 'Срок эксплуатации (год)',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'percentage_of_wear',
            'sortBy' => 'percentage_of_wear',
            'label' => 'Процент износа',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        // [
        //     'key' => 'order_comment',
        //     'sortBy' => 'order_comment',
        //     'label' => 'Комментарий для коллег',
        //     'stickyColumn' => false,
        //     'sortable' => false,
        //     'sortDirection' => 'desc',
        //     'visible' => true
        // ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => 'Добавлено в систему',
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
     * Get append of equip_years_of_use
     * @return array
     */
    public function getEquipYearsOfUseAttribute()
    {
        $equip_years_of_use = 0;

        if (isset($this->equip_date_of_release) && $this->equip_date_of_release != NULL) {
            $dateNow = new DateTime();
            $dateOfStart = date_create($this->equip_date_of_release);
            $diff = date_diff($dateNow, $dateOfStart);
            $equip_years_of_use = $diff->y;
        }

        return $equip_years_of_use;
    }

    /**
     * Get append of percentage_of_wear
     * @return array
     */
    public function getPercentageOfWearAttribute()
    {
        $percentage_of_wear = 0;

        if (isset($this->equip_date_of_release) && $this->equip_date_of_release != NULL) {
            if ($this->equip_years_of_use != 0) {
                $percentage_of_wear = $this->equip_years_of_use / 10 * 100;
            }
        }

        return $percentage_of_wear . '%';
    }

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function equip_user()
    {
        return $this->hasOne(User::class, 'id', 'equip_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Contract table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function equip_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'equip_contract_id');
        // ->select(['id', 'contract_number', 'contract_address']);
    }

    /**
     * Reference properties table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function equip_type()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'equip_type_reference_id');
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
            'equip_user' => isset($this->equip_user->name) ? $this->equip_user->name : '',
            'equip_contract' => isset($this->equip_contract->contract_number) ? $this->equip_contract->contract_number : '',
            'equip_type' => isset($this->equip_type->name) ? $this->equip_type->name : '',
            'equip_mark' => $this->equip_mark,
            'equip_date_of_release' => isset($this->equip_date_of_release) ? date('d.m.Y', strtotime($this->equip_date_of_release)) : '01.01.1900',
            'equip_passport' => $this->equip_passport,
            'equip_years_of_use' => $this->equip_years_of_use,
            'percentage_of_wear' => $this->percentage_of_wear,
            'equip_comment' => $this->equip_comment,
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'equip_user_id' => isset($this->equip_user_id) ? $this->equip_user_id : null,
            'equip_contract_id' => isset($this->equip_contract_id) ? $this->equip_contract_id : null,
            'equip_type_reference_id' => isset($this->equip_type_reference_id) ? $this->equip_type_reference_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
