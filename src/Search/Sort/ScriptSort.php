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
        if (!in_array($type, ['number', 'string'])) {
            throw new Exception('Incorrect type');
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