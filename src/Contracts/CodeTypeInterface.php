<?php

namespace Timurikvx\CodeTypeEncoder\Contracts;

interface CodeTypeInterface
{
    public function encode(string $code): array;

}