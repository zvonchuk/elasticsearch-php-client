<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

class MultiMatchQueryBuilder extends QueryBuilder
{
    public const BEST_FIELDS = 'best_fields';
    public const MOST_FIELDS = 'most_fields';
    public const CROSS_FIELDS = 'cross_fields';
    public const PHRASE = 'phrase';
    public const PHRASE_PREFIX = 'phrase_prefix';
    public const BOOL_PREFIX = 'bool_prefix';

    private const TYPES = [
        self::BEST_FIELDS, self::MOST_FIELDS, self::CROSS_FIELDS, self::PHRASE, self::PHRASE_PREFIX, self::BOOL_PREFIX,
    ];

    private ?string $type = null;
    private ?string $operator = null;
    private int|string|null $fuzziness = null;
    private ?int $prefixLength = null;
    private ?float $tieBreaker = null;
    private int|string|null $minimumShouldMatch = null;
    private ?string $analyzer = null;

    /** @param list<string> $fields field names, optionally boosted: "title^3" */
    public function __construct(private readonly int|float|bool|string $value, private readonly array $fields)
    {
    }

    public function type(string $type): static
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown multi_match type "%s"; expected one of: %s.',
                $type,
                implode(', ', self::TYPES),
            ));
        }
        $this->type = $type;
        return $this;
    }

    public function operator(string $operator): static
    {
        if (!in_array(strtolower($operator), [MatchQueryBuilder::OPERATOR_AND, MatchQueryBuilder::OPERATOR_OR], true)) {
            throw new \InvalidArgumentException(sprintf('Unknown match operator "%s"; expected "and" or "or".', $operator));
        }
        $this->operator = $operator;
        return $this;
    }

    public function fuzziness(int|string $fuzziness): static
    {
        $this->fuzziness = $fuzziness;
        return $this;
    }

    public function prefixLength(int $prefixLength): static
    {
        $this->prefixLength = $prefixLength;
        return $this;
    }

    /**
     * Share of the other fields' scores added to the best field's score (0.0–1.0).
     */
    public function tieBreaker(float $tieBreaker): static
    {
        $this->tieBreaker = $tieBreaker;
        return $this;
    }

    public function minimumShouldMatch(int|string $minimumShouldMatch): static
    {
        $this->minimumShouldMatch = $minimumShouldMatch;
        return $this;
    }

    public function analyzer(string $analyzer): static
    {
        $this->analyzer = $analyzer;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['query' => $this->value, 'fields' => $this->fields];
        $options = [
            'type' => $this->type,
            'operator' => $this->operator,
            'fuzziness' => $this->fuzziness,
            'prefix_length' => $this->prefixLength,
            'tie_breaker' => $this->tieBreaker,
            'minimum_should_match' => $this->minimumShouldMatch,
            'analyzer' => $this->analyzer,
        ];
        foreach ($options as $name => $value) {
            if ($value !== null) {
                $body[$name] = $value;
            }
        }

        return ['multi_match' => $body + $this->commonOptions()];
    }
}
