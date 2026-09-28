<?php


namespace Zvonchuk\Elastic\Query;

class BoolQueryBuilder extends QueryBuilder
{
    private array $mustClauses = [];
    private array $mustNotClauses = [];
    private array $filterClauses = [];
    private array $shouldClauses = [];

    public function __construct()
    {
        $this->name = 'bool';
    }

    public function must(QueryBuilder $query)
    {
        $this->mustClauses[] = $query->getSource();
        return $this;
    }

    public function mustNot(QueryBuilder $query)
    {
        $this->mustNotClauses[] = $query->getSource();
        return $this;
    }

    public function filter(QueryBuilder $query)
    {
        $this->filterClauses[] = $query->getSource();
        return $this;
    }

    public function should(QueryBuilder $query)
    {
        $this->shouldClauses[] = $query->getSource();
        return $this;
    }

    public function getSource()
    {
        $clauses = [];
        foreach (['must' => 'must', 'must_not' => 'mustNot', 'filter' => 'filter', 'should' => 'should'] as $key => $clause) {
            if (count($this->{$clause . 'Clauses'})) {
                $clauses[$key] = $this->{$clause . 'Clauses'};
            }
        }

        $query = empty($clauses) ? new \stdClass() : $clauses;
        return [$this->name => $query];
    }

}