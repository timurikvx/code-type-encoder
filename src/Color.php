<?php

namespace Timurikvx\CodeTypeEncoder;

class Color
{
    private string $color;

    private int $R = 255;

    private int $G = 255;

    private int $B = 255;

    public function __construct(string $color, string $default = '000000')
    {
        if (!preg_match('/^[a-fA-F0-9]{6}$/i', $color)) {
            $color = $default;
        }
        $this->color = $color;
        $this->R = hexdec(substr($color, 0, 2));
        $this->G = hexdec(substr($color, 2, 2));
        $this->B = hexdec(substr($color, 4, 2));
    }

    public function color(): string
    {
        return '#'.strtolower($this->color);
    }

    public function getR(): int
    {
        return $this->R;
    }

    public function getG(): int
    {
        return $this->G;
    }

    public function getB(): int
    {
        return $this->B;
    }

}