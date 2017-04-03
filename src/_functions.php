<?php
namespace Module\MongoDriver
{
    /**
     * Make Mongo Condition From Expression
     *
     * @param array|string $expression Query Search Term
     *
     * [
     *   'meta' => [
     *     'is_file' => [
     *       '$eq' => [
     *         true,
     *       ]
     *     ],
     *     'file_size' => [
     *       '$gt' => [
     *         40000,
     *       ]
     *     ],
     *   ],
     *   'version_tag' => [
     *     '$eq' => [
     *       'latest',
     *       'low_quality',
     *     ],
     *   ],
     * ];
     *
     * @return array
     */
    function expressionToMongoCondition($expression)
    {
        if (is_string($expression))
            $expression = parseExpressionFromString($expression);

        $condition = [];
        foreach ($expression as $field => $conditioner) {
            foreach ($conditioner as $o => $vl) {
                if ($o === '$eq') {
                    // 'limit' => [
                    //    '$eq' => [
                    //       40000,
                    //     ]
                    //  ],
                    if (count($vl) > 1)
                        // equality checks for the values of the same field
                        // '$eq' => [100, 200, 300]
                        $condition[$field] = ['$in' => $vl];
                    else
                        // '$eq' => [100]
                        $condition[$field] = current($vl);
                } elseif ($o === '$gt') {
                    $condition[$field] = [
                        '$gt' => $vl,
                    ];
                } elseif ($o === '$lt') {
                    $condition[$field] = [
                        '$lt' => $vl,
                    ];
                } else {
                    // Condition also can be other embed field condition
                    $cond = $this->__importCondition([$o => $vl]);
                    $condition[$field.'.'.$o] = current($cond);
                }
            }
        }

        return $condition;
    }

    /**
     * Parse Expression From Query String
     *
     * ?meta=is_file:true|file_size>40000&mime_type=audio/mp3&version_tag=latest|low_quality
     *        &offset=latest_id&limit=20
     *
     * @param $expression
     *
     * @return array
     * @throws \Exception
     */
    function parseExpressionFromString($expression)
    {
        parse_str($expression, $expression);

        $parsed = [];
        foreach ($expression as $field => $term)
        {
            if (!in_array($field, ['meta', 'mime_type', 'owner_identifier', 'version']) )
                continue;

            // $field => latest_id
            // $field => is_file:true
            // $field => is_file:true|file_size>4000000
            // $field => \Traversable ---> field:value|other_field:value2

            if (is_string($term))
            {
                if (false !== strpos($term, '|'))
                    // mime_type=audio/mp3|audio/wave
                    $termExchange = explode('|', $term);
                else
                    // version=latest
                    $termExchange = [$term];

                $term = [];
                foreach ($termExchange as $i => $t)
                {
                    // $t=is_file:true
                    if (preg_match('/(?P<operand>\w+)(?P<operator>[:<>])(?<value>\w+)/', $t, $matches)) {
                        switch ($matches['operator']) {
                            case ':': $operator = '$eq'; break;
                            case '>': $operator = '$gt'; break;
                            case '<': $operator = '$lt'; break;
                            default: throw new \Exception("Operator {$matches['operator']} is invalid.");
                        }

                        if (!isset($term[$matches['operand']]))
                            $term[$matches['operand']] = [];

                        if (in_array(strtolower($matches['value']), ['true', 'false']))
                            $matches['value'] = filter_var($matches['value'], FILTER_VALIDATE_BOOLEAN);

                        $term[$matches['operand']] = array_merge_recursive(
                            $term[$matches['operand']]
                            , [
                                $operator => [
                                    $matches['value'],
                                ],
                            ]
                        );
                    } else {
                        // $t=audio/mp3
                        if (in_array(strtolower($t), ['true', 'false']))
                            $t = filter_var($t, FILTER_VALIDATE_BOOLEAN);

                        $term = array_merge_recursive($term, [
                            '$eq' => [$t],
                        ]);
                    }
                }
            }

            elseif ($term instanceof \Traversable) {
                $iterTerm = $term; $term = [];
                foreach ($iterTerm as $operand => $value)
                    $term[$operand] = [
                        '$eq' => [
                            $value
                        ],
                    ];
            }

            elseif (!is_array($term))
                throw new \Exception(sprintf('Invalid Term (%s)', \Poirot\Std\flatten($term)));

            $parsed[$field] = $term;

        }// end foreach

        return $parsed;
    }

}
