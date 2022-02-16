<?php

namespace Zvonchuk\Elastic\Core;

class BulkRequest extends Request
{
    private array $request;

    public function add(Request $request)
    {
        if ($request instanceof IndexRequest) {
            $this->request[] = [
                "index" => [
                    "_index" => $request->indice,
                    "_id" => $request->id
                ]
            ];

            $this->request[] = $request->source;
        }

        if ($request instanceof DeleteRequest) {
            $this->request[] = [
                "delete" => [
                    "_index" => $request->indice,
                    "_id" => $request->id
                ]
            ];
        }

        if ($request instanceof UpdateRequest) {
            $this->request[] = [
                "update" => [
                    "_index" => $request->indice,
                    "_id" => $request->id
                ]
            ];

            $this->request[] = [
                "doc" => $request->source
            ];
        }

        return $this;
    }

    public function getSource(): array
    {
        return [
            'body' => $this->request
        ];
    }

}