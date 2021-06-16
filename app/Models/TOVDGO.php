<?php

namespace App\Models;

use App\Models\City;
use App\Models\House;
use App\Models\Region;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use App\Bundles\Elasticsearch\TOVDGOIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TOVDGO extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = TOVDGOIndexConfigurator::class;

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
            'vgko_region' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_city' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_street' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_house' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_master' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_date_of_work' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'vgko_comment' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_status' => [
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
            'vgko_master_user_id' => [
                "type" => "long"
            ],
            'vgko_region_id' => [
                "type" => "long"
            ],
            'vgko_city_id' => [
                "type" => "long"
            ],
            'vgko_street_id' => [
                "type" => "long"
            ],
            'vgko_house_id' => [
                "type" => "long"
            ],
        ]
    ];

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'to_vgko';

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'vgko_region_id',
        'vgko_city_id',
        'vgko_street_id',
        'vgko_house_id',
        'vgko_master_user_id',
        'vgko_comment',
        'vgko_status',
        'vgko_date_of_work',
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
                'vgko_master',
                'vgko_region',
                'vgko_city',
                'vgko_street',
                'vgko_house'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'vgko_master',
                'vgko_region',
                'vgko_city',
                'vgko_street',
                'vgko_house'
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
        'sortBy' => 'vgko_date_of_work',
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
            'key' => 'vgko_region.name',
            'sortBy' => 'vgko_region.keyword',
            'label' => 'Область',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_city.name',
            'sortBy' => 'vgko_city.keyword',
            'label' => 'Город',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_street.name',
            'sortBy' => 'vgko_street.keyword',
            'label' => 'Улица',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_house.name',
            'sortBy' => 'vgko_house.keyword',
            'label' => 'Номер дома',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_master.name',
            'sortBy' => 'vgko_master.keyword',
            'label' => 'Мастер на ТО',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_date_of_work',
            'sortBy' => 'vgko_date_of_work',
            'label' => 'ТО назначено на дату',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_comment',
            'sortBy' => 'vgko_comment.keyword',
            'label' => 'Комментарий',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_status',
            'sortBy' => 'vgko_status.keyword',
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
    public function vgko_master()
    {
        return $this->hasOne(User::class, 'id', 'vgko_master_user_id')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Region table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_region()
    {
        return $this->hasOne(Region::class, 'id', 'vgko_region_id')
            ->select(['id', 'name']);
    }

    /**
     * City table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_city()
    {
        return $this->hasOne(City::class, 'id', 'vgko_city_id')
            ->select(['id', 'name']);
    }

    /**
     * Street table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_street()
    {
        return $this->hasOne(Street::class, 'id', 'vgko_street_id')
            ->select(['id', 'name']);
    }

    /**
     * House table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_house()
    {
        return $this->hasOne(House::class, 'id', 'vgko_house_id')
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
            'vgko_master' => isset($this->vgko_master->name) ? $this->vgko_master->name : '',
            'vgko_region' => isset($this->vgko_region->name) ? $this->vgko_region->name : '',
            'vgko_city' => isset($this->vgko_city->name) ? $this->vgko_city->name : '',
            'vgko_street' => isset($this->vgko_street->name) ? $this->vgko_street->name : '',
            'vgko_house' => isset($this->vgko_house->name) ? $this->vgko_house->name : '',
            'vgko_date_of_work' => isset($this->vgko_date_of_work) ? date('d.m.Y H:i', strtotime($this->vgko_date_of_work)) : '01.01.1900 00:00',
            'vgko_comment' => isset($this->vgko_comment) ? $this->vgko_comment : '',
            'vgko_status' => isset($this->vgko_status) ? $this->vgko_status : '',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'vgko_master_user_id' => isset($this->vgko_master_user_id) ? $this->vgko_master_user_id : null,
            'vgko_region_id' => isset($this->vgko_region_id) ? $this->vgko_region_id : null,
            'vgko_city_id' => isset($this->vgko_city_id) ? $this->vgko_city_id : null,
            'vgko_street_id' => isset($this->vgko_street_id) ? $this->vgko_street_id : null,
            'vgko_house_id' => isset($this->vgko_house_id) ? $this->vgko_house_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
