<?php

namespace Timurikvx\CodeTypeEncoder;

use JetBrains\PhpStorm\NoReturn;
use Timurikvx\CodeTypeEncoder\Contracts\CodeTypeInterface;


class CodeTypeBits
{
    protected string $svg = '';

    private array $bits = [];

    private mixed $image = null;

    private string $type;

    private string $barcode;

    private int $font_size = 20;

    private string $font_file = __DIR__.'\\Fonts\\ocrb.ttf';

    private CodeTypeInterface $schema;

    public function __construct(array $bits, string $barcode, CodeTypeInterface $schema)
    {
        $this->bits = $bits;
        $this->barcode = $barcode;
        $this->schema = $schema;
    }

    public function get(): array
    {
        return $this->bits;
    }

    private function toFormat(): void
    {
        ob_start();
        if($this->type == 'png'){
            imagepng($this->image);
        }
        if($this->type == 'jpeg'){
            imagejpeg($this->image);
        }
        if($this->type == 'bmp'){
            imagebmp($this->image);
        }
        if($this->type == 'gif'){
            imagegif($this->image);
        }
        if($this->type == 'webp'){
            imagewebp($this->image);
        }
        if($this->type == 'svg'){
            echo $this->svg;
        }
    }

    private function toImage(int $width = 300, int $height = 150, int $frame = 5, bool $transparent = false)
    {
        $bars = $this->getBarWidths();
        $moduleWidth = round(($width) / count($bars), 0);
        $fontOffset = $this->getFontOffset($moduleWidth);

        $x = $frame;
        $y = $frame;
        $rectangles = [];
        foreach ($bars as $bar) {
            $barWidth = ceil($bar['width'] * $moduleWidth);
            if ($bar['type'] == 'black') {
                $rectangle = new Rectangle($x, $y, $barWidth);
                $rectangles[] = $rectangle;
            }
            $x += $barWidth;
        }
        $x += $frame;

        $image = imagecreate($x, $height);
        // Определяем цвета
        $trans = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        $back = $transparent? $trans: $white;
        imagefilledrectangle($image, 0, 0, $x, $height, $back);

        foreach ($rectangles as $rectangle) {
            $h = $height - $this->font_size;
            imagefilledrectangle($image, $rectangle->getX(), $rectangle->getY(), $rectangle->getX() + $rectangle->getWidth() - 1, $h - $frame - $fontOffset, $black);
        }

        // Добавляем текст с кодом
        $this->printText($image, $x, $height, $frame);
        return imagescale($image, $width, $height, IMG_BICUBIC_FIXED);
    }

    public function toSvg(int $width = 300, int $height = 150, int $frame = 5, bool $transparent = false): string
    {
        $bars = $this->getBarWidths();
        $totalModules = array_sum(array_column($bars, 'width'));

        if ($totalModules == 0) {
            return '';
        }

        $moduleWidth = ($width - 2 * $frame) / $totalModules;
        $fontOffset = $this->getFontOffset(round($moduleWidth));

        $svg = '<svg width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg">';

        // Background
        if (!$transparent) {
            $svg .= '<rect x="0" y="0" width="' . $width . '" height="' . $height . '" fill="white"/>';
        }

        $currentX = $frame;
        $barHeight = $height - $this->font_size - $frame - $fontOffset;

        foreach ($bars as $bar) {
            $barWidth = $bar['width'] * $moduleWidth;
            if ($bar['type'] == 'black') {
                $svg .= '<rect x="' . $currentX . '" y="' . $frame . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="black"/>';
            }
            $currentX += $barWidth;
        }

        // Add text
        $text = html_entity_decode($this->barcode, ENT_QUOTES, 'UTF-8');
        $textX = $width / 2;
        $textY = $height - $frame;

        $svg .= '<text x="' . $textX . '" y="' . $textY . '" font-family="monospace" font-size="' . $this->font_size . '" text-anchor="middle" fill="black">' . $text . '</text>';

        $svg .= '</svg>';

        return $svg;
    }

    #[NoReturn]
    public function answer(): void
    {
        if ($this->type === 'svg') {
            header('Content-type: image/svg+xml');
        }else{
            header('Content-type: image/'.$this->type);
        }
        $this->toFormat();
        die();
    }

    public function base64(): array
    {
        $this->toFormat();
        $imageData = ob_get_clean();
        return [
            'type'=>$this->type,
            'data'=> base64_encode($imageData),
            'code'=>$this->barcode,
            'barcode'=>$this->barcode
        ];
    }

    private function printText($image, int $width, int $height, int $frame): void
    {
        $black = imagecolorallocate($image, 0, 0, 0);
        $text = html_entity_decode($this->barcode, ENT_QUOTES, 'UTF-8');
        $bbox = imagettfbbox($this->font_size, 0, $this->font_file, $text);

        $minX = min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $maxX = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $realWidth = $maxX - $minX;
        $w = ($width / 2) - ($realWidth / 2) - $this->font_size;
        imagettftext($image, $this->font_size, 0, $w + $frame, $height - $frame, $black, $this->font_file, $text);
    }

    /**
     * Получить массив ширины полос
     */
    public function getBarWidths(): array
    {
        $widths = [];
        $binary = $this->bits;
        $len = count($binary);
        $middle = intval(($len / 2) - 0.5);

        $delimiters = [0, 2, $middle - 1, $middle + 1, $len - 3, $len - 1];

        $i = 0;
        while ($i < $len) {
            $current = $binary[$i];
            $count = 1;
            while ($i + $count < $len && $binary[$i + $count] == $current) {
                $count++;
            }
            $delimiter = in_array($i, $delimiters);
            $widths[] = [
                'type' => $current == '1' ? 'black' : 'white',
                'width' => $count,
                'delimiter' => $delimiter
            ];
            $i += $count;
        }
        return $widths;
    }

    public function render(string $type, int $width = 300, int $height = 150, int $frame = 5, $transparent = false): self
    {
        $this->type = $type;
        if($this->type == 'svg'){
            $this->svg = $this->toSvg($width, $height, $frame, $transparent);
        }else {
            $this->image = $this->toImage($width, $height, $frame, $transparent);
        }
        return $this;
    }

    public function save(string $path, string $name): void
    {
        $this->toFormat();
        $imageData = ob_get_clean();
        file_put_contents($path.$name.'.'.$this->type, $imageData);
    }

    private function getFontOffset(int $moduleWidth): int
    {
        if($moduleWidth >= 9){
            return 18;
        }
        if($moduleWidth >= 8){
            return 14;
        }
        if($moduleWidth >= 7){
            return 12;
        }
        if($moduleWidth >= 6){
            return 10;
        }
        if($moduleWidth >= 5){
            return 10;
        }
        if($moduleWidth >= 4){
            return 10;
        }
        if($moduleWidth >= 3){
            return 10;
        }
        if($moduleWidth >= 2){
            return 8;
        }
        if($moduleWidth >= 1){
            return 2;
        }
        return 0;
    }

}