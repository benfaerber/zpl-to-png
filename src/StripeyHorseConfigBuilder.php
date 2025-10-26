<?php
namespace Faerber\ZplToPng;

class StripeyHorseConfigBuilder {
    private int $rotation = 0;
    private float $labelWidthMm = StripeyHorseConfig::DEFAULT_WIDTH_MM;
    private float $labelHeightMm = StripeyHorseConfig::DEFAULT_HEIGHT_MM;
    private int $dpmm = StripeyHorseConfig::DEFAULT_DPMM;

    /** Sets the rotation angle in degrees. */
    public function rotation(int $rotation): self {
        $this->rotation = $rotation;
        return $this;
    }

    /** Sets the label width in millimeters. */
    public function labelWidthMm(float $labelWidthMm): self {
        $this->labelWidthMm = $labelWidthMm;
        return $this;
    }

    /** Sets the label height in millimeters. */
    public function labelHeightMm(float $labelHeightMm): self {
        $this->labelHeightMm = $labelHeightMm;
        return $this;
    }

    /** Sets the label width in pixels. */
    public function labelWidthPx(int $labelWidthPx): self {
        $this->labelWidthMm = StripeyHorseConfig::pixelsToMillimeters($labelWidthPx, $this->dpmm);
        return $this;
    }

    /** Sets the label height in pixels. */
    public function labelHeightPx(int $labelHeightPx): self {
        $this->labelHeightMm = StripeyHorseConfig::pixelsToMillimeters($labelHeightPx, $this->dpmm);
        return $this;
    }

    /** Sets the dots per millimeter resolution. */
    public function dpmm(int $dpmm): self {
        $this->dpmm = $dpmm;
        return $this;
    }

    /** Sets the label dimensions in millimeters. */
    public function labelSize(float $widthMm, float $heightMm): self {
        $this->labelWidthMm = $widthMm;
        $this->labelHeightMm = $heightMm;
        return $this;
    }

    /** Sets the label dimensions in pixels. */
    public function labelSizePx(int $widthPx, int $heightPx): self {
        $this->labelWidthMm = StripeyHorseConfig::pixelsToMillimeters($widthPx, $this->dpmm);
        $this->labelHeightMm = StripeyHorseConfig::pixelsToMillimeters($heightPx, $this->dpmm);
        return $this;
    }

    /** Sets the label size using a preset name (e.g., '4x6', '2x4'). */
    public function labelPreset(string $preset): self {
        $preset = strtolower($preset);

        if (! isset(StripeyHorseConfig::LABEL_SIZES[$preset])) {
            throw new StripeyHorseException(
                "Unknown label preset: {$preset}. Available presets: " .
                implode(', ', array_keys(StripeyHorseConfig::LABEL_SIZES))
            );
        }

        $size = StripeyHorseConfig::LABEL_SIZES[$preset];
        $this->labelWidthMm = $size['width'];
        $this->labelHeightMm = $size['height'];

        return $this;
    }

    /** Builds and returns the final configuration object. */
    public function build(): StripeyHorseConfig {
        return new StripeyHorseConfig(
            rotation: $this->rotation,
            labelWidthMm: $this->labelWidthMm,
            labelHeightMm: $this->labelHeightMm,
            dpmm: $this->dpmm,
        );
    }
}
