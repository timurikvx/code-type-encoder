<?php

namespace Timurikvx\CodeTypeEncoder\Schemas;

use Timurikvx\CodeTypeEncoder\Contracts\CodeTypeInterface;

class CODE39 implements CodeTypeInterface
{
    private array $map = [
        '0' => '111221211', '1' => '211211112', '2' => '112211112',
        '3' => '212211111', '4' => '111221112', '5' => '211221111',
        '6' => '112221111', '7' => '111211212', '8' => '211211211',
        '9' => '112211211', 'A' => '211112112', 'B' => '112112112',
        'C' => '212112111', 'D' => '111122112', 'E' => '211122111',
        'F' => '112122111', 'G' => '111112212', 'H' => '211112211',
        'I' => '112112211', 'J' => '111122211', 'K' => '211111122',
        'L' => '112111122', 'M' => '212111121', 'N' => '111121122',
        'O' => '211121121', 'P' => '112121121', 'Q' => '111111222',
        'R' => '211111221', 'S' => '112111221', 'T' => '111121221',
        'U' => '221111112', 'V' => '122111112', 'W' => '222111111',
        'X' => '121121112', 'Y' => '221121111', 'Z' => '122121111',
        '-' => '121111212', '.' => '221111211', ' ' => '122111211',
        '$' => '121212111', '/' => '121211121', '+' => '121112121',
        '%' => '111212121', '*' => '121121211' // Star symbol (start/stop)
    ];

    public function __construct()
    {

    }

    protected function getMap(): array
    {
        return $this->map;
    }


    public function encode(string $code): array
    {
        // 1. Добавляем стартовый и стоповый символы
        $full_code = '*' . strtoupper($code) . '*';
        $final_bits = '';

        for ($i = 0; $i < strlen($full_code); $i++) {
            $char = $full_code[$i];
            if (isset($this->map[$char])) {
                $pattern = $this->map[$char];
                $char_bits = '';

                for ($j = 0; $j < strlen($pattern); $j++) {
                    $width = (int)$pattern[$j];
                    $bit = ($j % 2 == 0) ? '1' : '0';
                    $char_bits .= str_repeat($bit, $width);
                }

                $final_bits .= $char_bits;
                if ($i < strlen($full_code) - 1) {
                    $final_bits .= '0';
                }
            }
        }

        return str_split($final_bits);
    }

}