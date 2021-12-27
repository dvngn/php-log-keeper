<?php

namespace Devengine\LogKeeper\Repositories;

interface LogFileRepository extends \IteratorAggregate
{
    public function get(string $name): string;

    public function put(string $name, string $content): bool;

    public function exists(string $name): bool;

    public function compress(string $name): bool;

    public function delete(string $name): bool;
}