<?php
namespace App\Services\RateShopper\StripeyHorse;

readonly class StripeyHorseConfig {
    const SIZE_2_INCH = 50.8;
    const SIZE_4_INCH = 101.6;
    const SIZE_6_INCH = 152.4;
    const SIZE_3_INCH = 76.2;
    const SIZE_5_INCH = 127.0;

    public const DEFAULT_WIDTH_MM = self::SIZE_4_INCH;
    public const DEFAULT_HEIGHT_MM = self::SIZE_6_INCH;
    public const DEFAULT_DPMM = 8;

    public const LABEL_SIZES = [
        // 2x4
        '2x4' => ['width' => self::SIZE_4_INCH, 'height' => self::SIZE_2_INCH],
        '4x2' => ['width' => self::SIZE_4_INCH, 'height' => self::SIZE_2_INCH],

        // 4x6 
        '4x6' => ['width' => self::SIZE_4_INCH, 'height' => self::SIZE_6_INCH],
        '6x4' => ['width' => self::SIZE_4_INCH, 'height' => self::SIZE_6_INCH],

        // 4x4 
        '4x4' => ['width' => self::SIZE_4_INCH, 'height' => self::SIZE_4_INCH],

        // 3x5
        '3x5' => ['width' => self::SIZE_3_INCH, 'height' => self::SIZE_5_INCH],
        '5x3' => ['width' => self::SIZE_3_INCH, 'height' => self::SIZE_5_INCH],
    ];

    public function __construct(
        public int $rotation = 0,
        public float $labelWidthMm = self::DEFAULT_WIDTH_MM,
        public float $labelHeightMm = self::DEFAULT_HEIGHT_MM,
        public int $dpmm = self::DEFAULT_DPMM,
    ) {
    }

    public static function builder(): StripeyHorseConfigBuilder {
        return new StripeyHorseConfigBuilder();
    }

    public function toArray(): array {
        return [
            'labelWidthMm' => $this->labelWidthMm,
            'labelHeightMm' => $this->labelHeightMm,
            'dpmm' => $this->dpmm,
            'rotation' => $this->rotation,
        ];
    }

    public static function pixelsToMillimeters(int $pixels, int $dpmm = self::DEFAULT_DPMM): float {
        return $pixels / $dpmm;
    }

    public static function millimetersToPixels(float $mm, int $dpmm = self::DEFAULT_DPMM): int {
        return (int) round($mm * $dpmm);
    }

    public function toJsonPayload(): string {
        return json_encode($this->toArray());
    }
}
