<?php

namespace Timurikvx\CodeTypeEncoder;

class Rectangle
{

    private int $x;

    private int $y;

    private int $width;

    public function __construct(int $x, int $y, int $width)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

}
