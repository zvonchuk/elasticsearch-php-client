<?php

namespace Zvonchuk\Elastic\Search\Sort;

class ScriptSort extends SortBuilder
{
    public const NUMBER = "number";
    public const STRING = "string";
    private string $script;
    private string $type;

    public function __construct(string $script, string $type)
    {
        if (!in_array($type, [self::NUMBER, self::STRING], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown script sort type "%s"; expected "%s" or "%s".',
                $type,
                self::NUMBER,
                self::STRING,
            ));
        }

        $this->script = $script;
        $this->type = $type;
    }

    public function getSource()
    {
        return [
            '_script' => [
                'order' => $this->order,
                'type' => $this->type,
                'script' => [
                    'lang' => "painless",
                    'source' => $this->script,
                ],
            ]
        ];
    }
}