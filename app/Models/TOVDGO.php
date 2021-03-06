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
            'house_build_year' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'vgko_masters' => [
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
        'vgko_master_user_id_2',
        'vgko_master_user_id_3',
        'vgko_master_user_id_4',
        'vgko_comment',
        'vgko_status',
        'vgko_date_of_work',
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'vgko_masters',
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
                'vgko_master_2',
                'vgko_master_3',
                'vgko_master_4',
                'vgko_region',
                'vgko_city',
                'vgko_street',
                'vgko_house'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'vgko_master',
                'vgko_master_2',
                'vgko_master_3',
                'vgko_master_4',
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
            'label' => '??????????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_city.name',
            'sortBy' => 'vgko_city.keyword',
            'label' => '??????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_street.name',
            'sortBy' => 'vgko_street.keyword',
            'label' => '??????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_house.name',
            'sortBy' => 'vgko_house.keyword',
            'label' => '?????????? ????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_house.build_year',
            'label' => '?????? ??????????????????',
            'sortBy' => 'house_build_year.keyword',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_masters',
            'sortBy' => 'vgko_masters.keyword',
            'label' => '???????????? ???? ????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_date_of_work',
            'sortBy' => 'vgko_date_of_work',
            'label' => '???? ?????????????????? ???? ????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_comment',
            'sortBy' => 'vgko_comment.keyword',
            'label' => '??????????????????????',
            'stickyColumn' => false,
            'sortable' => false,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'vgko_status',
            'sortBy' => 'vgko_status.keyword',
            'label' => '????????????',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => '???????? ????????????????????',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'actions',
            'label' => '????????????????',
            'stickyColumn' => true,
            'visible' => true
        ]
    ];

    /**
     * Get append of vgko_masters
     * @return string
     */
    public function getVgkoMastersAttribute()
    {
        $master = isset($this->vgko_master->name) ? $this->vgko_master->name : '';
        $master .= isset($this->vgko_master_2->name) ? ', ' . $this->vgko_master_2->name : '';
        $master .= isset($this->vgko_master_3->name) ? ', ' . $this->vgko_master_3->name : '';
        $master .= isset($this->vgko_master_4->name) ? ', ' . $this->vgko_master_4->name : '';

        return $master;
    }

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
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_master_2()
    {
        return $this->hasOne(User::class, 'id', 'vgko_master_user_id_2')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_master_3()
    {
        return $this->hasOne(User::class, 'id', 'vgko_master_user_id_3')
            ->select(['id', 'name', 'email', 'phone']);
    }

    /**
     * Users table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vgko_master_4()
    {
        return $this->hasOne(User::class, 'id', 'vgko_master_user_id_4')
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
            ->select(['id', 'name', 'build_year']);
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

        $master = isset($this->vgko_master->name) ? $this->vgko_master->name : '';
        $master .= isset($this->vgko_master_2->name) ? ', ' . $this->vgko_master_2->name : '';
        $master .= isset($this->vgko_master_3->name) ? ', ' . $this->vgko_master_3->name : '';
        $master .= isset($this->vgko_master_4->name) ? ', ' . $this->vgko_master_4->name : '';

        $tableFields = [
            'id' => $this->id,
            'vgko_masters' => $master,
            'vgko_region' => isset($this->vgko_region->name) ? $this->vgko_region->name : '',
            'vgko_city' => isset($this->vgko_city->name) ? $this->vgko_city->name : '',
            'vgko_street' => isset($this->vgko_street->name) ? $this->vgko_street->name : '',
            'vgko_house' => isset($this->vgko_house->name) ? $this->vgko_house->name : '',
            'house_build_year' => isset($this->vgko_house->build_year) ? $this->vgko_house->build_year : 1900,
            'vgko_date_of_work' => isset($this->vgko_date_of_work) ? date('d.m.Y H:i', strtotime($this->vgko_date_of_work)) : '01.01.1900 00:00',
            'vgko_comment' => isset($this->vgko_comment) ? $this->vgko_comment : '',
            'vgko_status' => isset($this->vgko_status) ? $this->vgko_status : '',
            'created_at' => isset($this->created_at) ? date('d.m.Y H:i', strtotime($this->created_at)) : '01.01.1900 00:00'
        ];

        $otherFields = [
            'id_search' => $this->id,
            'vgko_master_user_id' => isset($this->vgko_master_user_id) ? $this->vgko_master_user_id : null,
            'vgko_master_user_id_2' => isset($this->vgko_master_user_id_2) ? $this->vgko_master_user_id_2 : null,
            'vgko_master_user_id_3' => isset($this->vgko_master_user_id_3) ? $this->vgko_master_user_id_3 : null,
            'vgko_master_user_id_4' => isset($this->vgko_master_user_id_4) ? $this->vgko_master_user_id_4 : null,
            'vgko_region_id' => isset($this->vgko_region_id) ? $this->vgko_region_id : null,
            'vgko_city_id' => isset($this->vgko_city_id) ? $this->vgko_city_id : null,
            'vgko_street_id' => isset($this->vgko_street_id) ? $this->vgko_street_id : null,
            'vgko_house_id' => isset($this->vgko_house_id) ? $this->vgko_house_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
