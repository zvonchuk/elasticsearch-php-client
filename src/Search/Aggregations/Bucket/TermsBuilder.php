<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations\Bucket;

use Zvonchuk\Elastic\Search\Aggregations\AggregationBuilder;

class TermsBuilder extends AggregationBuilder
{
    private ?string $field = null;
    private int $size = 10;

    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function toArray(): array
    {
        return $this->render(['terms' => ['field' => $this->field, 'size' => $this->size]]);
    }
}
