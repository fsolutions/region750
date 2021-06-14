<?php

namespace App\Models;

use App\Models\City;
use App\Models\Flat;
use App\Models\Region;
use App\Models\Street;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use App\Bundles\Elasticsearch\HouseIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class House extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = HouseIndexConfigurator::class;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'houses';

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
        'name', //name (house+block+block_type)
        'region_id',
        'city_id',
        'street_id'
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
                'street'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'region',
                'city',
                'street',
                'flats'
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
        'all_roles' => [
            'create',
            'show',
            'edit',
            'delete'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'name',
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
            'key' => 'name',
            'label' => 'Номер дома',
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
     * Flats table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flats()
    {
        return $this->hasMany(Flat::class, 'house_id', 'id')->orderBy('name', 'desc');
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
        ];

        $otherFields = [
            'id_search' => $this->id,
            'region_id' => isset($this->region_id) ? $this->region_id : null,
            'city_id' => isset($this->city_id) ? $this->city_id : null,
            'street_id' => isset($this->street_id) ? $this->street_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
