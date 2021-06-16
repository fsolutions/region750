<?php

namespace App\Models;

use App\Models\City;
use App\Models\House;
use App\Models\Region;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Bundles\Elasticsearch\TOVentilationIndexConfigurator;

class TOVentilation extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = TOVentilationIndexConfigurator::class;

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
            'ventilation_region' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_city' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_street' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_house' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_master' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_date_of_work' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'ventilation_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'ventilation_status' => [
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
            'ventilation_master_user_id' => [
                "type" => "long"
            ],
            'ventilation_region_id' => [
                "type" => "long"
            ],
            'ventilation_city_id' => [
                "type" => "long"
            ],
            'ventilation_street_id' => [
                "type" => "long"
            ],
            'ventilation_house_id' => [
                "type" => "long"
            ],
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'to_ventilation';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'ventilation_region_id',
        'ventilation_city_id',
        'ventilation_street_id',
        'ventilation_house_id',
        'ventilation_master_user_id',
        'ventilation_comment',
        'ventilation_status',
        'ventilation_date_of_work',
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
                'ventilation_master',
                'ventilation_region',
                'ventilation_city',
                'ventilation_street',
                'ventilation_house'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'ventilation_master',
                'ventilation_region',
                'ventilation_city',
                'ventilation_street',
                'ventilation_house'
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
            // 'show'
        ],
        'all_roles' => [
            'create',
            // 'show',
            'edit'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'ventilation_date_of_work',
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
            'key' => 'ventilation_region.name',
            'sortBy' => 'ventilation_region.keyword',
            'label' => 'Область',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_city.name',
            'sortBy' => 'ventilation_city.keyword',
            'label' => 'Город',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_street.name',
            'sortBy' => 'ventilation_street.keyword',
            'label' => 'Улица',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_house.name',
            'sortBy' => 'ventilation_house.keyword',
            'label' => 'Номер дома',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_master.name',
            'sortBy' => 'ventilation_master.keyword',
            'label' => 'Мастер на ТО',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_date_of_work',
            'sortBy' => 'ventilation_date_of_work',
            'label' => 'ТО назначено на дату',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_comment',
            'sortBy' => 'ventilation_comment.keyword',
            'label' => 'Комментарий',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'ventilation_status',
            'sortBy' => 'ventilation_status.keyword',
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
    public function ventilation_master()
    {
        return $this->hasOne(User::class, 'id', 'ventilation_master_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Region table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ventilation_region()
    {
        return $this->hasOne(Region::class, 'id', 'ventilation_region_id')
            ->select(['id', 'name']);
    }

    /**
     * City table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ventilation_city()
    {
        return $this->hasOne(City::class, 'id', 'ventilation_city_id')
            ->select(['id', 'name']);
    }

    /**
     * Street table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ventilation_street()
    {
        return $this->hasOne(Street::class, 'id', 'ventilation_street_id')
            ->select(['id', 'name']);
    }

    /**
     * House table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ventilation_house()
    {
        return $this->hasOne(House::class, 'id', 'ventilation_house_id')
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
            'ventilation_master' => isset($this->ventilation_master->name) ? $this->ventilation_master->name : '',
            'ventilation_region' => isset($this->ventilation_region->name) ? $this->ventilation_region->name : '',
            'ventilation_city' => isset($this->ventilation_city->name) ? $this->ventilation_city->name : '',
            'ventilation_street' => isset($this->ventilation_street->name) ? $this->ventilation_street->name : '',
            'ventilation_house' => isset($this->ventilation_house->name) ? $this->ventilation_house->name : '',
            'ventilation_date_of_work' => isset($this->ventilation_date_of_work) ? date('d.m.Y H:i', strtotime($this->ventilation_date_of_work)) : '01.01.1900 00:00',
            'ventilation_comment' => isset($this->ventilation_comment) ? $this->ventilation_comment : '',
            'ventilation_status' => isset($this->ventilation_status) ? $this->ventilation_status : '',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'ventilation_master_user_id' => isset($this->ventilation_master_user_id) ? $this->ventilation_master_user_id : null,
            'ventilation_region_id' => isset($this->ventilation_region_id) ? $this->ventilation_region_id : null,
            'ventilation_city_id' => isset($this->ventilation_city_id) ? $this->ventilation_city_id : null,
            'ventilation_street_id' => isset($this->ventilation_street_id) ? $this->ventilation_street_id : null,
            'ventilation_house_id' => isset($this->ventilation_house_id) ? $this->ventilation_house_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
