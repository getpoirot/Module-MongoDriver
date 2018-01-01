<?php
namespace Module\MongoDriver
{

    use MongoDB\BSON\ObjectID;

    /**
     * Make Mongo Condition From Expression
     *
     * @param array $parsedExpression Expression that Parsed
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
    function buildMongoConditionFromExpression($parsedExpression)
    {
        if (!is_array($parsedExpression))
            throw new \InvalidArgumentException(sprintf(
                'Expression must be parsed to Array; given: (%s).'
                , \Poirot\Std\flatten($parsedExpression)
            ));


        $condition = [];
        foreach ($parsedExpression as $field => $conditioner) {
            foreach ($conditioner as $o => $vl) {
                if ($o === '$eq') {
                    if ($vl instanceof ObjectID) {
                        $condition[$field] = $vl;
                    } else {
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
                    }
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
                    $cond = buildMongoConditionFromExpression([$o => $vl]);
                    $condition[$field.'.'.$o] = current($cond);
                }
            }
        }

        return $condition;
    }

    /**
     * Parse Expression From Query String
     *
     * note: Query strings in php can't include field same as pre.field=x
     *       php convert to _ automatically
     *
     * ?meta=is_file:true|file_size>40000&mime_type=audio/mp3&version=tag:latest|low_quality
     *        &offset=latest_id&limit=20
     *
     * @param string $expression      ['limit']
     * @param array  $exclusionFields The fields that is in query string but must not used in expression
     * @param string $exclusionBehave allow|disallow
     *
     * @return array
     * @throws \Exception
     */
    function parseExpressionFromString($expression, array $exclusionFields = array(), $exclusionBehave = 'disallow')
    {
        if (!is_string($expression))
            throw new \InvalidArgumentException(sprintf(
                'Expression Term Must Be String; given: (%s).'
                , \Poirot\Std\flatten($expression)
            ));


        parse_str($expression, $expression);

        return parseExpressionFromArray($expression, $exclusionFields, $exclusionBehave);
    }

    /**
     * Parse Expression From Array
     *
     * Array (
     *   [$post] => stat:publish|share:private
     * )
     *
     *
     * @param array  $expression      ['limit']
     * @param array  $exclusionFields The fields that is in query string but must not used in expression
     * @param string $exclusionBehave allow|disallow
     *
     * @return array
     * @throws \Exception
     */
    function parseExpressionFromArray(array $expression, array $exclusionFields = array(), $exclusionBehave = 'disallow')
    {
        $parsed = [];
        foreach ($expression as $field => $term)
        {
            if (!empty($exclusionFields))
            {
                $flag = in_array($field, $exclusionFields);

                ($exclusionBehave != 'allow') ?: $flag = !$flag;

                if ($flag)
                    // The Field not considered as Expression Term
                    continue;
            }

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
                    if (preg_match('/(?P<operand>\w.+)(?P<operator>[:<>])(?<value>.+)/', $t, $matches)) {
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
                // Term Is Object exp. ...... _id => ObjectID("")
                // that expression object may have specific meaning for condition builder
                $term = [
                    '$eq' => $term,
                ];

            $parsed[$field] = $term;

        }// end foreach

        return $parsed;
    }
}
