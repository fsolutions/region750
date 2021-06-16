<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Str;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    use HasFactory, ModelGettersTrait;

    /**
     * Disabled updated_at.
     */
    const UPDATED_AT = null;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'history';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $timestamps = [
        'created_at'
    ];

    /**
     * Customize format.
     *
     * @var array
     */
    // protected $casts = [
    //     'created_at' => 'datetime:d.m.Y H:i',
    // ];

    /**
     *  Attributes models.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'operation_name',
        'user_id',
        'contract_id',
        'model_name',
        'model_id'
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'contract'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'contract'
            ]
        ]
    ];

    /**
     * Action allows for roles
     *
     * @var array
     */
    protected $actionAllows = [
        'all_roles' => [
            // 'create',
            // 'show',
            // 'edit'
        ],
    ];

    /**
     * Default params for sort.
     *
     * @var array
     */
    protected $sort = [
        'sortBy' => 'created_at',
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
            'key' => 'operation_name',
            'sortBy' => 'operation_name',
            'label' => 'Произведенное действие / Событие',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'contract.contract_number',
            'sortBy' => 'contract_id',
            'label' => 'Номер договора',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'created_at',
            'sortBy' => 'created_at',
            'label' => 'Дата операции',
            'stickyColumn' => false,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ]
    ];


    /**
     * Add new record history table.
     *
     * @param array $fields
     */
    public static function addNew(array $fields)
    {
        $uuid = Str::uuid()->toString();

        History::create(array('id' => $uuid) + $fields);
    }

    /**
     * User rekationships One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Contract rekationships One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contract()
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }
}
