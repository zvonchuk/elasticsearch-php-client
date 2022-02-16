<?php

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class TermsBuilder extends AggregationBuilder
{
    private string $field;
    private $_size = 10;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getSource()
    {
        return [
            $this->name => [
                'terms' => [
                    'field' => $this->field,
                    'size' => $this->_size
                ]
            ]
        ];
    }

    public function size(int $size): TermsBuilder
    {
        $this->_size = $size;
        return $this;
    }

    public function field(string $field): TermsBuilder
    {
        $this->field = $field;
        return $this;
    }
}
