<?php

namespace Timurikvx\CodeTypeEncoder;

use Timurikvx\CodeTypeEncoder\Contracts\CodeTypeInterface;

class CodeTypeEncoder
{

    protected CodeTypeInterface $schema;

    public function __construct(CodeTypeInterface $schema)
    {
        $this->schema = $schema;
    }

    public function encode(string &$code): CodeTypeBits
    {
        $code = trim($code);
        $array = $this->schema->encode($code);
        return new CodeTypeBits($array, $code, $this->schema);
    }

}