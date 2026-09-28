<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search\Sort;

class ScriptSort extends SortBuilder
{
    public const NUMBER = 'number';
    public const STRING = 'string';

    public function __construct(private readonly string $script, private readonly string $type)
    {
        if (!in_array($type, [self::NUMBER, self::STRING], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown script sort type "%s"; expected "%s" or "%s".',
                $type,
                self::NUMBER,
                self::STRING,
            ));
        }
    }

    public function toArray(): array
    {
        return [
            '_script' => [
                'order' => $this->order,
                'type' => $this->type,
                'script' => ['lang' => 'painless', 'source' => $this->script],
            ],
        ];
    }
}
