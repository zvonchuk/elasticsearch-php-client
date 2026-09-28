<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MatchQueryBuilder extends QueryBuilder
{
    public const OPERATOR_AND = 'and';
    public const OPERATOR_OR = 'or';

    private ?string $operator = null;
    private int|string|null $fuzziness = null;
    private ?int $prefixLength = null;
    private ?int $maxExpansions = null;
    private int|string|null $minimumShouldMatch = null;
    private ?string $analyzer = null;

    public function __construct(private readonly string $field, private readonly int|float|bool|string $value)
    {
    }

    /**
     * "and": every term must match; "or" (Elasticsearch default): any term.
     */
    public function operator(string $operator): static
    {
        if (!in_array(strtolower($operator), [self::OPERATOR_AND, self::OPERATOR_OR], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown match operator "%s"; expected "and" or "or".', $operator));
        }
        $this->operator = $operator;
        return $this;
    }

    /**
     * Allowed edits per term: 0, 1, 2 or "AUTO" (also "AUTO:3,6").
     */
    public function fuzziness(int|string $fuzziness): static
    {
        $this->fuzziness = $fuzziness;
        return $this;
    }

    /**
     * Number of leading characters that must match exactly when fuzziness is used.
     */
    public function prefixLength(int $prefixLength): static
    {
        $this->prefixLength = $prefixLength;
        return $this;
    }

    /**
     * Maximum number of term variations fuzziness expands to.
     */
    public function maxExpansions(int $maxExpansions): static
    {
        $this->maxExpansions = $maxExpansions;
        return $this;
    }

    /**
     * How many terms must match with the "or" operator: 2, -1 or "75%".
     */
    public function minimumShouldMatch(int|string $minimumShouldMatch): static
    {
        $this->minimumShouldMatch = $minimumShouldMatch;
        return $this;
    }

    /**
     * Analyzer for the query text instead of the field's search analyzer.
     */
    public function analyzer(string $analyzer): static
    {
        $this->analyzer = $analyzer;
        return $this;
    }

    public function toArray(): array
    {
        $query = ['query' => $this->value];
        $options = [
            'operator' => $this->operator,
            'fuzziness' => $this->fuzziness,
            'prefix_length' => $this->prefixLength,
            'max_expansions' => $this->maxExpansions,
            'minimum_should_match' => $this->minimumShouldMatch,
            'analyzer' => $this->analyzer,
        ];
        foreach ($options as $name => $value) {
            if ($value !== null && $value !== '') {
                $query[$name] = $value;
            }
        }

        return ['match' => [$this->field => $query + $this->commonOptions()]];
    }
}
