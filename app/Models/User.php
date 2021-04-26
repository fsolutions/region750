<?php

namespace App\Models;

use App\Traits\HasRoles;
use Illuminate\Support\Str;
use ScoutElastic\Searchable;
use App\Traits\ModelGettersTrait;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Bundles\Elasticsearch\UserIndexConfigurator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = UserIndexConfigurator::class;

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
            'email' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'phone' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'position' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'roles' => [
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
     * Table name
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Appends for controller
     *
     * @var array
     */
    protected $controllerAppends = [
        'role',
        'string-of-roles'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'position',
        'phone_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Customize format
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:d.m.Y H:i',
        'updated_at' => 'datetime:d.m.Y H:i',
        'deleted_at' => 'datetime:d.m.Y H:i',
    ];

    /**
     * Load relationship.
     *
     * @var array
     */
    protected $loads = [
        'index' => [
            'all_roles' => [
                'roles'
            ]
        ],
        'other_actions' => [
            'all_roles' => [
                'roles',
                // 'contracts'
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
            'delete',
            'send_sms'
        ],
        'client' => [
            // 'create',
            // 'show',
            // 'edit',
            // 'delete'
        ],
        'all_roles' => [
            'create',
            // 'show',
            'edit',
            // 'delete',
            'send_sms'
        ]
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
        [
            'key' => 'id',
            'sortBy' => 'id',
            'label' => 'ID',
            'sortable' => true,
            'sortDirection' => 'desc'
        ],
        [
            'key' => 'name',
            'sortBy' => 'name.keyword',
            'label' => 'ФИО',
            'sortable' => true,
            'sortDirection' => 'desc'
        ],
        [
            'key' => 'email',
            'sortBy' => 'email.keyword',
            'label' => 'E-mail',
            'sortable' => true,
            'sortDirection' => 'desc'
        ],
        [
            'key' => 'phone',
            'sortBy' => 'phone.keyword',
            'label' => 'Телефон',
            'sortable' => true,
            'sortDirection' => 'desc'
        ],
        // [
        //     'key' => 'position',
        //     'sortBy' => 'position.keyword',
        //     'label' => 'Должность',
        //     'sortable' => true,
        //     'sortDirection' => 'desc'
        // ],
        [
            'key' => 'string-of-roles',
            'sortBy' => 'roles.keyword',
            'label' => 'Роль',
            'sortable' => true,
            'sortDirection' => 'desc'
        ],
        [
            'key' => 'actions',
            'label' => 'Действия'
        ]
    ];

    /**
     * Get append of string of roles
     * @return array
     */
    public function getStringOfRolesAttribute()
    {
        $selected_role_list = [];

        foreach ($this->roles()->get()->toArray() as $role) {
            $selected_role_list[] = $role['name'];
        }

        return implode(", ", $selected_role_list);
    }

    /**
     * Get append of role
     * @return array
     */
    public function getRoleAttribute()
    {
        $selected_role_list = [];

        foreach ($this->roles()->get()->toArray() as $role) {
            $selected_role_list[] = $role['slug'];
        }

        return $selected_role_list;
    }

    /**
     * Checking show data by user roles
     *
     * @return int
     */
    public function canShowAll()
    {
        foreach ($this->roles()->get()->toArray() as $role) {
            if (
                $role['slug'] == 'manager'
                || $role['slug'] == 'master'
                || $role['slug'] == 'intern'
                || $role['slug'] == 'administrator'
            ) {
                return true;
            }
        }
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

        $roles = null;

        foreach ($this->roles as $role) {
            $roles = $role->name . ' ';
        }

        $fieldsTable = [
            'id' => $this->id,
            'name' => isset($this->name) ? $this->name : '',
            'email' => isset($this->email) ? $this->email : '',
            'phone' => isset($this->phone) ? $this->phone : '',
            'position' => isset($this->position) ? $this->position : '',
            'roles' => $roles
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id
        ];

        return $fieldsTable + $otherFields;
    }

    /**
     * Contracts table relationship Belongs To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    // public function contracts()
    // {
    //     return $this->belongsToMany(Contract::class, 'contract_on_user_id');
    // }

    /**
     * Relationships roles table Many To Many
     *
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }
}
