<?php


namespace Zvonchuk\Elastic\Search;

class CountResponse
{
    private array $response = [];

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getCount(): int
    {
        return $this->response['count'];
    }

}