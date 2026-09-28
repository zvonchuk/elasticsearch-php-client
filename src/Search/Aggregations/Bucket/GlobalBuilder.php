<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

/**
 * One bucket with every document of the index, ignoring the search query; sub-aggregations run over all of them.
 */
class GlobalBuilder extends AggregationBuilder
{
    public function toArray(): array
    {
        return $this->render(['global' => new \stdClass()]);
    }
}
