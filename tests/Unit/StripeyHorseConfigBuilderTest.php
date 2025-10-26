<?php

use Faerber\ZplToPng\StripeyHorseConfig;
use Faerber\ZplToPng\StripeyHorseConfigBuilder;
use Faerber\ZplToPng\StripeyHorseException;

test('can build config with default values', function () {
    $config = (new StripeyHorseConfigBuilder())->build();

    expect($config)->toBeInstanceOf(StripeyHorseConfig::class)
        ->and($config->rotation)->toBe(0)
        ->and($config->labelWidthMm)->toBe(StripeyHorseConfig::DEFAULT_WIDTH_MM)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::DEFAULT_HEIGHT_MM)
        ->and($config->dpmm)->toBe(StripeyHorseConfig::DEFAULT_DPMM);
});

test('rotation method sets rotation and returns self', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->rotation(90);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->rotation)->toBe(90);
});

test('labelWidthMm method sets width and returns self', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->labelWidthMm(100.0);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(100.0);
});

test('labelHeightMm method sets height and returns self', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->labelHeightMm(150.0);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->labelHeightMm)->toBe(150.0);
});

test('dpmm method sets dpmm and returns self', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->dpmm(12);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->dpmm)->toBe(12);
});

test('labelWidthPx method converts pixels to millimeters', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->labelWidthPx(800);

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(100.0);
});

test('labelHeightPx method converts pixels to millimeters', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->labelHeightPx(1200);

    $config = $builder->build();
    expect($config->labelHeightMm)->toBe(150.0);
});

test('labelSize method sets both width and height', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->labelSize(100.0, 150.0);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(100.0)
        ->and($config->labelHeightMm)->toBe(150.0);
});

test('labelSizePx method converts both width and height from pixels', function () {
    $builder = new StripeyHorseConfigBuilder();
    $result = $builder->labelSizePx(800, 1200);

    expect($result)->toBe($builder);

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(100.0)
        ->and($config->labelHeightMm)->toBe(150.0);
});

test('labelPreset method sets dimensions for 4x2 label', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->labelPreset('4x2');

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::SIZE_2_INCH);
});

test('labelPreset method sets dimensions for 6x4 label', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->labelPreset('6x4');

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::SIZE_6_INCH);
});

test('labelPreset method is case insensitive', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->labelPreset('4X6');

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::SIZE_6_INCH);
});

test('labelPreset throws exception for unknown preset', function () {
    $builder = new StripeyHorseConfigBuilder();

    expect(fn() => $builder->labelPreset('unknown'))
        ->toThrow(StripeyHorseException::class, 'Unknown label preset: unknown');
});

test('can chain multiple builder methods', function () {
    $config = StripeyHorseConfig::builder()
        ->labelPreset('4x2')
        ->rotation(270)
        ->dpmm(12)
        ->build();

    expect($config->labelWidthMm)->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::SIZE_2_INCH)
        ->and($config->rotation)->toBe(270)
        ->and($config->dpmm)->toBe(12);
});

test('can build config similar to test_client example', function () {
    $config = StripeyHorseConfig::builder()
        ->labelPreset('4x2')
        ->rotation(0)
        ->build();

    expect($config->labelWidthMm)->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::SIZE_2_INCH)
        ->and($config->rotation)->toBe(0);
});

test('pixel methods respect custom dpmm', function () {
    $builder = new StripeyHorseConfigBuilder();
    $builder->dpmm(16)
        ->labelWidthPx(1600);

    $config = $builder->build();
    expect($config->labelWidthMm)->toBe(100.0)
        ->and($config->dpmm)->toBe(16);
});
