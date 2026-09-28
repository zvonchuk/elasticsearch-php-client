<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Core;

class BulkRequest
{
    /** @var list<array<string, mixed>> */
    private array $body = [];

    public function add(IndexRequest|UpdateRequest|DeleteRequest $request): static
    {
        $action = ['_index' => $request->getIndex()];

        if ($request instanceof IndexRequest) {
            if ($request->getId() !== null) {
                $action['_id'] = $request->getId();
            }
            $this->body[] = ['index' => $action];
            $this->body[] = $request->getDocument();

            return $this;
        }

        $action['_id'] = $request->toArray()['id'];
        if ($request instanceof UpdateRequest) {
            $this->body[] = ['update' => $action];
            $this->body[] = ['doc' => $request->getDocument()];

            return $this;
        }

        $this->body[] = ['delete' => $action];

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->body === [];
    }

    /** @return array{body: list<array<string, mixed>>} */
    public function toArray(): array
    {
        return ['body' => $this->body];
    }

    /**
     * @deprecated since 1.0, use toArray(); will be removed in 2.0
     * @return array{body: list<array<string, mixed>>}
     */
    public function getSource(): array
    {
        return $this->toArray();
    }
}
