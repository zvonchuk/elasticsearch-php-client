<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

/**
 * One term with edit-distance tolerance; the term is not analyzed.
 */
class FuzzyQueryBuilder extends QueryBuilder
{
    private int|string|null $fuzziness = null;
    private ?int $prefixLength = null;
    private ?int $maxExpansions = null;
    private ?bool $transpositions = null;

    public function __construct(private readonly string $field, private readonly string $value)
    {
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

    public function maxExpansions(int $maxExpansions): static
    {
        $this->maxExpansions = $maxExpansions;
        return $this;
    }

    /**
     * Whether swapping two adjacent characters (ab → ba) counts as one edit (Elasticsearch default: true).
     */
    public function transpositions(bool $transpositions): static
    {
        $this->transpositions = $transpositions;
        return $this;
    }

    public function toArray(): array
    {
        $body = ['value' => $this->value];
        $options = [
            'fuzziness' => $this->fuzziness,
            'prefix_length' => $this->prefixLength,
            'max_expansions' => $this->maxExpansions,
            'transpositions' => $this->transpositions,
        ];
        foreach ($options as $name => $value) {
            if ($value !== null) {
                $body[$name] = $value;
            }
        }

        return ['fuzzy' => [$this->field => $body + $this->commonOptions()]];
    }
}
