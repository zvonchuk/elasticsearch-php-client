<?php

namespace Zvonchuk\Elastic\Core;

abstract class Request
{
    protected string $indice;
    abstract function getSource(): array;
}