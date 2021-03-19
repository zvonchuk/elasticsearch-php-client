<?php

namespace Zvonchuk\Elastic\Driver\Query;

class MatchQueryBuilder extends QueryBuilder
{
    private string $field;
    private string $value;
    private ?string $operator = null;
    private ?string $fuzziness = null;

    public function __construct(string $field, string $value)
    {
        $this->name = 'match';
        $this->field = $field;
        $this->value = $value;
    }

    public function operator(string $operator): QueryBuilder
    {
        $this->operator = $operator;
        return $this;
    }

    public function fuzziness(string $fuzziness): QueryBuilder
    {
        $this->fuzziness = $fuzziness;
        return $this;
    }

    public function getSource()
    {
        $query['query'] = $this->value;

        foreach (['operator', 'fuzziness'] as $clause) {
            if (count($this->{$clause})) {
                $query[$clause] = $this->{$clause};
            }
        }

        return [
            $this->name => [
                $this->field => $query
            ]
        ];
    }
}