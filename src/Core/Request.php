<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

abstract class Request
{
    public function __construct(protected readonly string $indice)
    {
    }

    /** @return array<string, mixed> parameters for the official client */
    abstract public function toArray(): array;

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array<string, mixed>
     */
    public function getSource(): array
    {
        return $this->toArray();
    }

    public function getIndex(): string
    {
        return $this->indice;
    }

    protected function requireId(?string $id): string
    {
        if ($id === null) {
            throw new \LogicException(sprintf('%s needs a document id; call id() first.', static::class));
        }

        return $id;
    }
}
