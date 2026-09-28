<?php

namespace Zvonchuk\Elastic\Core;

class BulkRequest extends Request
{
    private array $request = [];

    public function add(Request $request)
    {
        if ($request instanceof IndexRequest) {
            $action = ["_index" => $request->indice];
            if ($request->id !== null) {
                $action["_id"] = $request->id;
            }
            $this->request[] = ["index" => $action];

            $this->request[] = $request->source;
        }

        if ($request instanceof DeleteRequest) {
            $this->request[] = [
                "delete" => [
                    "_index" => $request->indice,
                    "_id" => $request->requireId($request->id)
                ]
            ];
        }

        if ($request instanceof UpdateRequest) {
            $this->request[] = [
                "update" => [
                    "_index" => $request->indice,
                    "_id" => $request->requireId($request->id)
                ]
            ];

            $this->request[] = [
                "doc" => $request->source
            ];
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->request === [];
    }

    public function getSource(): array
    {
        return [
            'body' => $this->request
        ];
    }

}