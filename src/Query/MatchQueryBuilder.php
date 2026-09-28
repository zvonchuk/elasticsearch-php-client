<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchQueryBuilder extends QueryBuilder
{
    private ?string $operator = null;
    private ?string $fuzziness = null;

    public function __construct(private readonly string $field, private readonly int|float|bool|string $value)
    {
    }

    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function fuzziness(string $fuzziness): static
    {
        $this->fuzziness = $fuzziness;
        return $this;
    }

    public function toArray(): array
    {
        $query = ['query' => $this->value];
        if ($this->operator !== null && $this->operator !== '') {
            $query['operator'] = $this->operator;
        }
        if ($this->fuzziness !== null && $this->fuzziness !== '') {
            $query['fuzziness'] = $this->fuzziness;
        }

        return ['match' => [$this->field => $query + $this->commonOptions()]];
    }
}
