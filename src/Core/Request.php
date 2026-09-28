<?php

namespace Zvonchuk\Elastic\Core;

abstract class Request
{
    protected string $indice;
    abstract function getSource(): array;

    protected function requireId(?string $id): string
    {
        if ($id === null) {
            throw new \LogicException(sprintf('%s needs a document id; call id() first.', static::class));
        }

        return $id;
    }
}
