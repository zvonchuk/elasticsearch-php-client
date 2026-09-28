<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Search;

/**
 * Builds objects from document sources through their constructors: each constructor parameter
 * is taken from the source field with the same name, or its snake_case form ($createdAt ← created_at).
 */
final class DocumentMapper
{
    /**
     * @template T of object
     * @param class-string<T>     $class
     * @param array<string, mixed> $source
     * @return T
     */
    public static function map(string $class, array $source): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
            if (array_key_exists($name, $source)) {
                $arguments[$name] = $source[$name];
            } elseif (array_key_exists($snake, $source)) {
                $arguments[$name] = $source[$snake];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[$name] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $arguments[$name] = null;
            } else {
                throw new \UnexpectedValueException(sprintf(
                    'Cannot build %s: the document has no "%s" field (or "%s") and the parameter has no default.',
                    $class,
                    $name,
                    $snake,
                ));
            }
        }

        try {
            return $reflection->newInstanceArgs($arguments);
        } catch (\TypeError $e) {
            throw new \UnexpectedValueException(sprintf('Cannot build %s from the document: %s', $class, $e->getMessage()), 0, $e);
        }
    }
}
