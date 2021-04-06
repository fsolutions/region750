<?php

namespace App\Traits;

trait ModelGettersTrait
{
    /**
     * Get property loads.
     *
     * @param $key
     * @return array
     */
    public function getLoads($key = null)
    {
        if (array_key_exists($key, $this->loads)) {
            return $this->loads[$key];
        }

        if(array_key_exists('other_actions', $this->loads)) {
            return $this->loads['other_actions'];
        }

        return [];
    }

    /**
     * Get property actionAllows.
     *
     * @return array
     */
    public function getActionAllows()
    {
        return $this->actionAllows;
    }

    /**
     * Get params for sort.
     *
     * @return array
     */
    public function getSortParams()
    {
        $params = request()->only(['sortDirection', 'elasticSortBy', 'sortBy']);

        if (isset($params['elasticSortBy']) && isset($params['sortDirection'])) {
            return [
                'sortDirection' => $params['sortDirection'],
                'sortBy' => $params['elasticSortBy'],
            ];

        } elseif (isset($params['sortBy']) && isset($params['sortDirection'])) {
            return [
                'sortDirection' => $params['sortDirection'],
                'sortBy' => $params['elasticSortBy'],
            ];
        }

        return $this->sort;
    }

    /**
     * Get headers for tables.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->tableHeaders;
    }

    /**
     * Get appends for tables.
     *
     * @return array
     */
    public function getAppends()
    {
        return $this->appends;
    }

    /**
     * Get appends for controller
     *
     * @return mixed
     */
    public function getControllerAppends()
    {
        return $this->controllerAppends;
    }

    /**
     * Check to conn by ElasticSearch.
     *
     * @return bool
     */
    public function checkElasticModel()
    {
        if(isset($this->indexConfigurator) && !empty($this->indexConfigurator)) {
            return true;
        }

        return false;
    }
}
