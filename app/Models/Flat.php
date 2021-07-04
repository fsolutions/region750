<?php

namespace App\Models;

use App\Models\City;
use App\Models\House;
use App\Models\Region;
use App\Models\Street;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use App\Bundles\Elasticsearch\FlatIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flat extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = FlatIndexConfigurator::class;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'flats';

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
            'name' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'region' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'city' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'street' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'house' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'house_zip' => [
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

            // other fields
            'id_search' => [
                "type" => "keyword"
            ],
            'region_id' => [
                "type" => "long"
            ],
            'city_id' => [
                "type" => "long"
            ],
            'street_id' => [
                "type" => "long"
            ],
            'house_id' => [
                "type" => "long"
            ]
        ]
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Customize format
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'region_id',
        'city_id',
        'street_id',
        'house_id'
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'region',
                'city',
                'street',
                'house'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'region',
                'city',
                'street',
                'house'
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
            // 'delete'
        ],
        'all_roles' => [
            'create',
            // 'show',
            'edit',
            // 'delete'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'name.keyword',
        'sortDirection' => 'asc'
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
            'key' => 'house.zip',
            'sortBy' => 'house_zip.keyword',
            'label' => 'Индекс',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'region.name',
            'sortBy' => 'region.keyword',
            'label' => 'Регион',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'city.name',
            'sortBy' => 'city.keyword',
            'label' => 'Город',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'street.name',
            'sortBy' => 'street.keyword',
            'label' => 'Улица',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'house.name',
            'sortBy' => 'house.keyword',
            'label' => 'Номер дома',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'house.build_year',
            'label' => 'Год постройки',
            'sortBy' => 'house_build_year.keyword',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'name',
            'label' => 'Квартира',
            'sortBy' => 'name',
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
     * Regions table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id')
            ->select(['id', 'name']);
    }

    /**
     * Cities table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id')
            ->select(['id', 'name']);
    }

    /**
     * Streets table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function street()
    {
        return $this->hasOne(Street::class, 'id', 'street_id')
            ->select(['id', 'name']);
    }

    /**
     * Houses table relationships One To One.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function house()
    {
        return $this->hasOne(House::class, 'id', 'house_id')
            ->select(['id', 'name', 'zip', 'build_year']);
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
            'name' => $this->name,
            'region' => isset($this->region->name) ? $this->region->name : '',
            'city' => isset($this->city->name) ? $this->city->name : '',
            'street' => isset($this->street->name) ? $this->street->name : '',
            'house' => isset($this->house->name) ? $this->house->name : '',
            'house_build_year' => isset($this->house->build_year) ? $this->house->build_year : 1900,
            'house_zip' => isset($this->house->zip) ? $this->house->zip : '',
        ];

        $otherFields = [
            'id_search' => $this->id,
            'region_id' => isset($this->region_id) ? $this->region_id : null,
            'city_id' => isset($this->city_id) ? $this->city_id : null,
            'street_id' => isset($this->street_id) ? $this->street_id : null,
            'house_id' => isset($this->house_id) ? $this->house_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
