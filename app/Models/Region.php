<?php

namespace App\Models;

use App\Models\City;
use App\Models\Street;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use App\Bundles\Elasticsearch\RegionIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = RegionIndexConfigurator::class;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'regions';

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

            // other fields
            'id_search' => [
                "type" => "keyword"
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
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => []
        ],
        'other_actions' => [
            'all_roles' => [
                'cities'
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
     * Cities table relationships One To Many.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'region_id', 'id')->orderBy('name', 'desc');
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
        ];

        $otherFields = [
            'id_search' => $this->id,
        ];

        return $tableFields + $otherFields;
    }
}
