<?php

namespace App\Models;

use App\Models\Region;
use App\Models\Street;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use App\Bundles\Elasticsearch\CityIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = CityIndexConfigurator::class;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'cities';

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

            // other fields
            'id_search' => [
                "type" => "keyword"
            ],
            'region_id' => [
                "type" => "long"
            ],
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
        'region_id'
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
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'region',
                'streets'
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
            'key' => 'region.name',
            'sortBy' => 'region.keyword',
            'label' => 'Регион',
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
     * Streets table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function streets()
    {
        return $this->hasMany(Street::class, 'city_id', 'id')->orderBy('name', 'desc');
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
        ];

        $otherFields = [
            'id_search' => $this->id,
            'region_id' => isset($this->region_id) ? $this->region_id : null,
        ];

        return $tableFields + $otherFields;
    }
}
