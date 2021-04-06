<?php

namespace App\Bundles\Elasticsearch;

class ElasticSearchRule
{
    /**
     * Generate search rule.
     *
     * @param null $query
     * @param null $params
     * @return array
     */
    public static function rule($query = null, $params = null)
    {
        $rule = [];

        if ($query) {
           $rule += self::defaultSearchRule($query);
        }

        if ($params) {
            $rule += self::whereInRule($params);
        }

        return $rule;
    }

    /**
     * Default search rule.
     *
     * @param string $query
     * @return array
     */
    private static function defaultSearchRule(string $query)
    {
        return [
            'must' => [
                'bool' => [
                    'should' => [
                        [
                            'match' => [
                                'id_search' => [
                                    "query" => $query
                                ]

                            ]
                        ],
                        [
                            'multi_match' => [
                                'query' => $query,
                                'type'=> 'phrase_prefix',
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Rule whereIn by id.
     *
     * @param array $params
     * @return array
     */
    private static function whereInRule(array $params)
    {
        return [
            "filter" => [
                "bool" => [
                    "must" => [
                        [
                            "terms"  => [
                                "id" => $params
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
