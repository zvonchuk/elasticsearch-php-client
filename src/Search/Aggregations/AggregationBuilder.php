<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Aggregations;

abstract class AggregationBuilder implements AggregationInterface
{
    /** @var list<AggregationInterface> */
    protected array $aggregations = [];

    public function __construct(protected readonly string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
    abstract public function toArray(): array;

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array<string, mixed>
     */
    public function getSource(): array
    {
        return $this->toArray();
    }

    public function subAggregation(AggregationInterface $subAggregation): static
    {
        $this->aggregations[] = $subAggregation;
        return $this;
    }

    /**
     * Wraps the aggregation body under its name and adds the sub-aggregations, if any.
     *
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    protected function render(array $body): array
    {
        $source = [$this->name => $body];
        if ($this->aggregations !== []) {
            $source[$this->name]['aggregations'] = array_merge(
                ...array_map(static fn (AggregationInterface $aggregation): array => $aggregation->toArray(), $this->aggregations),
            );
        }

        return $source;
    }
}
