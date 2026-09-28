<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

/**
 * A request about one document: index, update, delete, get, exists.
 */
abstract class DocumentRequest extends Request
{
    protected ?string $id = null;

    public function id(int|string $id): static
    {
        $this->id = (string) $id;
        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}
