<?php

namespace Timurikvx\CodeTypeEncoder;

class Rectangle
{

    private int $x;

    private int $y;

    private int $width;
    private bool $delimiter;

    private $color;

    public function __construct(int $x, int $y, int $width, bool $delimiter = false, $color = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->delimiter = $delimiter;
        $this->color = $color;
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

    public function isDelimiter(): bool
    {
        return $this->delimiter;
    }

    public function getColor(): mixed
    {
        return $this->color;
    }

}
