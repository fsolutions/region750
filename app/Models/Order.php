<?php

namespace App\Models;

use App\Bundles\Elasticsearch\OrderIndexConfigurator;
use App\Traits\ModelGettersTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ScoutElastic\Searchable;

class Order extends Model
{
    use HasFactory, SoftDeletes, ModelGettersTrait, Searchable;

    /**
     * Settings to index
     *
     * @var string
     */
    protected $indexConfigurator = OrderIndexConfigurator::class;

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
            'type' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'company' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'company_status' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'lead_fio' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'lead_phone' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'inn' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'categories' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'services' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'receive_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'processing_end_datetime' => [
                "type" => "date",
                "format" => "dd.MM.yyyy HH:mm"
            ],
            'status_order' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],

            // for table with service
            'service_status' => [
                "type" =>  "text",
                "fields" => [
                    "keyword" => [
                        "type" => "keyword"
                    ]
                ]
            ],
            'service_users' => [
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
            'source_order' => [
                "type" =>  "text"
            ],
            'creator_user_id' => [
                "type" => "long"
            ],
            'reference_order_type_id' => [
                "type" => "long"
            ],
            'reference_status_id' => [
                "type" => "long"
            ],
            'reference_sources_id' => [
                "type" => "long"
            ],
            'source_client' => [
                "type" =>  "text",
            ],
            'sales_manager' => [
                "type" =>  "text"
            ],
            'optional_sales_manager' => [
                "type" =>  "text"
            ],
            'temp_name' => [
                "type" =>  "text"
            ]
        ]
    ];

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = [
        'creator_user_id',
        'reference_order_type_id',
        'reference_status_id',
        'reference_sources_id',
        'company_employee_id',
        'company_id',
        'optional_sales_manager_user_id',
        'receive_datetime',
        'processing_end_datetime',
        'comment',
        'reference_close_reason_id',
        'close_comment',
        'short_order',
        'temp_name'
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'set_categories_ids',
        'selected_categories',
        'set_services_ids',
        'selected_services',
        'loading'
    ];

    /**
     * Customize format
     *
     * @var array
     */
    protected $casts = [
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
                'type:id,name',
                'company:id,name,sales_manager_user_id,reference_sources_id,reference_company_status_id,inn',
                'categories:name',
                'services:name',
                'status:id,name',
                'company_employee:id,lead_fio,lead_phone',
                'documents:id,reference_document_type_id',
                'service_users:user_services.id,user_services.user_id,user_services.service_id'
            ],
        ],
        'other_actions' => [
            'all_roles' => [
                'type',
                'creator',
                'company_employee',
                'company',
                'categories',
                'services',
                'documents',
                'status',
                'finance',
                'service_users',
                'close_reason',
                'finance_requests',
                'optional_sales_manager'
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
            'create',
            'show',
            'edit',
            'make-order-supply',
            'distribute-services',
            'make-bill'
        ],
        'administrator' => [
            'create',
            'show',
            'edit',
            'delete',
            'make-order-supply',
            'distribute-services',
            'make-bill'
        ],
        'client' => [
            'create',
            'show',
            'edit'
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
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'type.name',
            'sortBy' => 'type.keyword',
            'label' => 'Тип',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'company.name',
            'sortBy' => 'company.keyword',
            'label' => 'Клиент',
            'stickyColumn' => true,
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'company.status.name',
            'sortBy' => 'company_status.keyword',
            'label' => 'Тип клиента',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'order_service_properties_271_247',
            'label' => 'СВХ',
            'sortable' => false,
            'sortDirection' => 'desc',
            'class' => 'text-center',
            'loading' => true,
            'whatLoad' => 'order_service_properties',
            'values' => [271,247],
            'visible' => true
        ],
        [
            'key' => 'order_service_properties_257',
            'label' => 'Номер ДТ',
            'sortable' => false,
            'sortDirection' => 'desc',
            'class' => 'text-center',
            'loading' => true,
            'whatLoad' => 'order_service_properties',
            'values' => [257],
            'visible' => true
        ],
        [
            'key' => 'order_service_properties_253_276',
            'label' => 'Номера ТС',
            'sortable' => false,
            'sortDirection' => 'desc',
            'class' => 'text-center',
            'loading' => true,
            'whatLoad' => 'order_service_properties',
            'values' => [253,276],
            'visible' => true
        ],
        [
            'key' => 'company_employee.lead_fio',
            'sortBy' => 'lead_fio.keyword',
            'label' => 'Контакт',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'company_employee.lead_phone',
            'sortBy' => 'lead_phone.keyword',
            'label' => 'Телефон',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'company.inn',
            'sortBy' => 'inn.keyword',
            'label' => 'ИНН',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'selected_categories',
            'sortBy' => 'categories.keyword',
            'label' => 'Категории',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'selected_services',
            'sortBy' => 'services.keyword',
            'label' => 'Вид услуги',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'receive_datetime',
            'sortBy' => 'receive_datetime',
            'label' => 'Дата получения заявки',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'processing_end_datetime',
            'sortBy' => 'processing_end_datetime',
            'label' => 'Дата отработки заявки',
            'sortable' => true,
            'sortDirection' => 'desc',
            'visible' => true
        ],
        [
            'key' => 'status.name',
            'sortBy' => 'status_order.keyword',
            'label' => 'Статус',
            'sortable' => true,
            'stickyColumn' => true,
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
     * Get append of set_categories_ids
     * @return array
     */
    public function getSetCategoriesIdsAttribute()
    {
        $selected_categories_list = [];
        foreach ($this->categories()->get() as $category) {
            $selected_categories_list[] = $category['id'];
        }

        return $selected_categories_list;
    }

    /**
     * Get append of selected_categories
     * @return string
     */
    public function getSelectedCategoriesAttribute()
    {
        $selected_categories_list = [];
        foreach ($this->categories()->get() as $category) {
            $selected_categories_list[] = $category['name'];
        }

        return implode("; ", $selected_categories_list);
    }

    /**
     * Get append of set_services_ids
     * @return array
     */
    public function getSetServicesIdsAttribute()
    {
        $selected_services_list = [];
        foreach ($this->services()->get() as $service) {
            $selected_services_list[] = $service['id'];
        }

        return $selected_services_list;
    }

    /**
     * Get append of selected_services
     * @return array
     */
    public function getSelectedServicesAttribute()
    {
        $selected_services_list = [];
        foreach ($this->services()->get() as $service) {
            $selected_services_list[] = [
                'id' => $service['id'],
                'name' => $service['name']
            ];
        }

        return $selected_services_list;
    }

    /**
     * Get append of loading
     * @return string
     */
    public function getLoadingAttribute()
    {
        return false;
    }

    /**
     * ReferenceProperty table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function status()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'reference_status_id');
    }

    /**
     * Company table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company()
    {
        return $this->hasOne(Company::class, 'id', 'company_id')
            ->with(['status:id,name', 'sales_manager:id,name', 'source:id,name']);
    }

    /**
     * ReferenceProperty table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'reference_order_type_id');
    }

    /**
     * Users table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creator()
    {
        return $this->hasOne(User::class, 'id', 'creator_user_id');
    }

    /**
     * ReferenceProperty table relationship Many To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ReferenceProperty::class,
            'order_categories',
            'order_id',
            'reference_category_id')
            ->whereNull('deleted_at');
    }

    /**
     * User table relationship Many To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class,'user_orders');
    }

    /**
     * hasMany Users Through Service list
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function service_users()
    {
        return $this->hasManyThrough(
            UserService::class,
            OrderService::class,
            'order_id',
            'service_id',
            'id',
            'id')
            ->with('user');
    }

    /**
     * Order services table relationship Many To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Reference::class,
            'order_services',
            'order_id',
            'reference_service_id')
            ->whereNull('deleted_at')
            ->withPivot('id', 'reference_property_status_id', 'deleted_at');
    }

    /**
     * Documents table relationships One to Many
     *
     * @return HasMany
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'order_id', 'id');
    }

    /**
     * Finance table relationships One to Many
     *
     * @return HasMany
     */
    public function finance()
    {
        return $this->hasMany(Finance::class, 'order_id', 'id');
    }

    /**
     * Company Users table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company_employee()
    {
        return $this->hasOne(CompanyEmployee::class, 'id', 'company_employee_id');
    }

    /**
     * ReferenceProperty table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function close_reason()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'reference_close_reason_id');
    }

    /**
     * FinanceRequest table relationship One To One
     *
     * @return HasMany
     */
    public function finance_requests()
    {
        return $this->hasMany(FinanceRequest::class, 'order_id', 'id');
    }

    /**
     * Users table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optional_sales_manager()
    {
        return $this->hasOne(User::class, 'id', 'optional_sales_manager_user_id');
    }

    /**
     * ReferencePropery table relationship One To One
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function source()
    {
        return $this->hasOne(ReferenceProperty::class, 'id', 'reference_sources_id');
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

        $categories = null;
        foreach ($this->categories as $category) {
            $categories .= $category->name . ' ';
        }

        $services = null;
        foreach ($this->services as $service) {
            $services .= $service->name . ' ';
        }

        $orderServices = OrderService::where('order_id', $this->id)->get();

        $statuses = null;
        foreach ($orderServices as $service) {
            $statuses .= $service->status->name . ' ';
            $users = null;
            foreach ($service->users as $user) {
                $users .= $user->name . ' ';
            }
        }

        $tableFields = [
            'id' => $this->id,
            'type' => isset($this->type->name) ? $this->type->name : '',
            'company' => isset($this->company->name) ? $this->company->name : '',
            'company_status' => isset($this->company->status->name) ? $this->company->status->name : '',
            'lead_fio' => isset($this->company_employee->lead_fio) ? $this->company_employee->lead_fio : '',
            'lead_phone' => isset($this->company_employee->lead_phone) ? $this->company_employee->lead_phone: '',
            'inn' => isset($this->company->inn) ? $this->company->inn : '',
            'categories' => isset($categories) ? $categories : null,
            'services' => isset($services) ? $services : null,
            'receive_datetime' => isset($this->receive_datetime) ? date('d.m.Y H:i', strtotime($this->receive_datetime)) : '01.01.1900 00:00',
            'processing_end_datetime' => isset($this->processing_end_datetime) ? date('d.m.Y H:i', strtotime($this->processing_end_datetime)) : '01.01.1900 00:00',
            'status_order' => isset($this->status->name) ? $this->status->name : '',

            // for table with service
            'service_status' => isset($statuses) ? $statuses : null,
            'service_users' =>  isset($users) ? $users : null,
        ];

        $otherFields = [
            'id_search' => '$id' . $this->id,
            'source_order' => isset($this->source->name) ? $this->source->name : '',
            'creator_user_id' => isset($this->creator_user_id) ? $this->creator_user_id : null,
            'reference_order_type_id' => isset($this->reference_order_type_id) ? $this->reference_order_type_id : null,
            'reference_status_id' => isset($this->reference_status_id) ? $this->reference_status_id : null,
            'reference_sources_id' => isset($this->reference_sources_id) ? $this->reference_sources_id : null,
            'source_client' => isset($this->company->source->name) ? $this->company->source->name : '',
            'sales_manager' => isset($this->company->sales_manager->name) ? $this->company->sales_manager->name : '',
            'optional_sales_manager' => isset($this->optional_sales_manager->name) ? $this->optional_sales_manager->name : '',
            'temp_name' => isset($this->temp_name) ? $this->temp_name : ''
        ];

        return $tableFields + $otherFields;
    }
}
