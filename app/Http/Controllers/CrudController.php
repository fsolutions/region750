<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bundles\Keyboard\Switcher;
use App\Bundles\Elasticsearch\ElasticSearchRule;

class CrudController extends Controller
{
    /**
     * Const access to fields models.
     */
    const ALL_ROLES = 'all_roles';

    /**
     * Using Model instance
     *
     * @var Model
     */
    public $model;

    /**
     * Now user instance
     *
     * @var User
     */
    public $user;

    /**
     * Default params for sort.
     *
     * @var array
     */
    public $sort = [
        'sortBy' => 'id',
        'sortDirection' => 'desc'
    ];

    /**
     * Formated data for model
     *
     * @var array
     */
    public $formData;

    /**
     * Now model loads access array
     *
     * @var array
     */
    protected $modelLoads = [];

    /**
     * Now model headers array
     *
     * @var array
     */
    protected $modelHeaders = [];

    /**
     * Now model actionAllows array
     *
     * @var array
     */
    protected $modelActionAllows = [];

    /**
     * Appends field model
     *
     * @var
     */
    protected $modelAppends;

    /**
     * User roles
     *
     * @var
     */
    protected $userRoles;

    /**
     * Action name.
     *
     * @var
     */
    private $action;

    /**
     * Model conn to elastic.
     *
     * @var
     */
    private $isModelElastic;


    /**
     * User constructor
     *
     * @param $model Model
     */
    public function __construct($model)
    {
        $this->middleware(function ($request, $next) use ($model) {
            $this->user = auth()->user();
            $this->userRoles = $this->user->roles;
            $this->model = $model;
            $this->modelAppends = $model->getControllerAppends();
            $this->sort = $model->getSortParams();
            $this->modelHeaders = $this->model->getHeaders();
            $this->modelActionAllows = $this->model->getActionAllows();
            $this->action = $request->route()->getActionMethod();
            $this->modelLoads = $this->checkUserLoadsForModel();
            $this->isModelElastic = $this->model->checkElasticModel();
            $this->model = $this->elasticSearch($model, request('q'));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $per_page = empty(request('per_page')) ? 100 : (int) request('per_page');

        $this->model = $this->sorting();

        $this->model = $this->model->paginate($per_page);

        if (isset($this->modelAppends)) {
            foreach ($this->model->items() as $item) {
                $item->append($this->modelAppends);
            }
        }

        $this->model->load($this->modelLoads);

        $this->model = $this->model->toArray();

        $this->model['headers'] = $this->modelHeaders;

        $this->model['actionAllows'] = $this->checkUserActionAllows();

        return $this->model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->model = $this->model::create($this->formData);

        if (isset($this->modelAppends)) {
            $this->model->append($this->modelAppends);
        }

        $this->model->load($this->modelLoads);

        return $this->model;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->model = $this->model::findOrfail($id);

        if (isset($this->modelAppends)) {
            $this->model->append($this->modelAppends);
        }

        $this->model->load($this->modelLoads);

        return $this->model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Model
     */
    public function update(Request $request, $id)
    {
        $this->model->update($this->formData);

        if (isset($this->modelAppends)) {
            $this->model->append($this->modelAppends);
        }

        $this->model->load($this->modelLoads);

        return $this->model;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->model::destroy($id);
    }

    /**
     * Check user loads availability
     *
     * @param $key
     * @return array
     */
    private function checkUserLoadsForModel()
    {
        $result = [];

        $allLoads =  $this->model->getLoads($this->action);

        foreach ($this->userRoles->toArray() as $key => $role) {
            if (array_key_exists($role['slug'], $allLoads)) {
                $result = array_merge($allLoads[$role['slug']], $result);
            }
        }

        if (!$result) {
            if (array_key_exists(self::ALL_ROLES, $allLoads)) {
                return $allLoads[self::ALL_ROLES];
            }
        }

        return array_unique($result);
    }

    /**
     * Check user action allows
     *
     * @return array
     */
    private function checkUserActionAllows(): array
    {
        $result = [];
        $actionAllows = $this->modelActionAllows;

        foreach ($this->userRoles->toArray() as $key => $role) {
            if (array_key_exists($role['slug'], $actionAllows)) {
                $result = array_merge($actionAllows[$role['slug']], $result);
            }
        }

        if (!$result) {
            if (array_key_exists(self::ALL_ROLES, $actionAllows)) {
                return $actionAllows[self::ALL_ROLES];
            }
        }

        return array_unique($result);
    }

    /**
     * Search by ElasticSearch.
     *
     * @param $model
     * @param null $query
     * @param null $params
     * @return mixed
     */
    protected function elasticSearch($model, $query = null, $params = null)
    {
        if ($this->isModelElastic) {
            $isElasticAction = in_array($this->action, ['show', 'store', 'destroy', 'update']);
            if (!$isElasticAction) {
                $result = $model::search($query)->rule(function ($builder) use ($params) {
                    return ElasticSearchRule::rule($builder->query, $params);
                });
                if ($result->count()) {
                    return $result;
                } else {
                    $query = Switcher::toCyrillic($query);
                    return $model::search($query)->rule(function ($builder) use ($query, $params) {
                        return ElasticSearchRule::rule($builder->query, $params);
                    });
                }
            }
        }
        return $model;
    }

    /**
     * Sorting data.
     *
     * @return mixed
     */
    private function sorting()
    {
        if ($this->model->get()->count()) {
            if ($this->isModelElastic) {
                $this->model = $this->model->orderRaw([
                    $this->sort['sortBy'] => [
                        'order'  => $this->sort['sortDirection']
                    ]
                ]);
            } else {
                $this->model = $this->model->orderBy($this->sort['sortBy'], $this->sort['sortDirection']);
            }
        }

        return $this->model;
    }
}
