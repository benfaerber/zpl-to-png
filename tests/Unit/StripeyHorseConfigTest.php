<?php

use Faerber\ZplToPng\StripeyHorseConfig;
use Faerber\ZplToPng\StripeyHorseConfigBuilder;

test('can create config with default values', function () {
    $config = new StripeyHorseConfig();

    expect($config->rotation)->toBe(0)
        ->and($config->labelWidthMm)->toBe(StripeyHorseConfig::DEFAULT_WIDTH_MM)
        ->and($config->labelHeightMm)->toBe(StripeyHorseConfig::DEFAULT_HEIGHT_MM)
        ->and($config->dpmm)->toBe(StripeyHorseConfig::DEFAULT_DPMM);
});

test('can create config with custom values', function () {
    $config = new StripeyHorseConfig(
        rotation: 90,
        labelWidthMm: 100.0,
        labelHeightMm: 150.0,
        dpmm: 12
    );

    expect($config->rotation)->toBe(90)
        ->and($config->labelWidthMm)->toBe(100.0)
        ->and($config->labelHeightMm)->toBe(150.0)
        ->and($config->dpmm)->toBe(12);
});

test('builder method returns a builder instance', function () {
    $builder = StripeyHorseConfig::builder();

    expect($builder)->toBeInstanceOf(StripeyHorseConfigBuilder::class);
});

test('toArray returns correct array structure', function () {
    $config = new StripeyHorseConfig(
        rotation: 270,
        labelWidthMm: 50.8,
        labelHeightMm: 101.6,
        dpmm: 8
    );

    $array = $config->toArray();

    expect($array)->toBe([
        'labelWidthMm' => 50.8,
        'labelHeightMm' => 101.6,
        'dpmm' => 8,
        'rotation' => 270,
    ]);
});

test('toJsonPayload returns valid json', function () {
    $config = new StripeyHorseConfig(
        rotation: 90,
        labelWidthMm: 101.6,
        labelHeightMm: 152.4,
        dpmm: 8
    );

    $json = $config->toJsonPayload();

    expect($json)->toBeJson()
        ->and(json_decode($json, true))->toBe([
            'labelWidthMm' => 101.6,
            'labelHeightMm' => 152.4,
            'dpmm' => 8,
            'rotation' => 90,
        ]);
});

test('pixelsToMillimeters converts correctly', function () {
    $pixels = 800;
    $dpmm = 8;

    $mm = StripeyHorseConfig::pixelsToMillimeters($pixels, $dpmm);

    expect($mm)->toBe(100.0);
});

test('pixelsToMillimeters uses default dpmm when not specified', function () {
    $pixels = 800;

    $mm = StripeyHorseConfig::pixelsToMillimeters($pixels);

    expect($mm)->toBe(100.0);
});

test('millimetersToPixels converts correctly', function () {
    $mm = 100.0;
    $dpmm = 8;

    $pixels = StripeyHorseConfig::millimetersToPixels($mm, $dpmm);

    expect($pixels)->toBe(800);
});

test('millimetersToPixels uses default dpmm when not specified', function () {
    $mm = 100.0;

    $pixels = StripeyHorseConfig::millimetersToPixels($mm);

    expect($pixels)->toBe(800);
});

test('millimetersToPixels rounds correctly', function () {
    $mm = 100.4;
    $dpmm = 8;

    $pixels = StripeyHorseConfig::millimetersToPixels($mm, $dpmm);

    expect($pixels)->toBe(803);
});

test('label size constants are correct', function () {
    expect(StripeyHorseConfig::SIZE_2_INCH)->toBe(50.8)
        ->and(StripeyHorseConfig::SIZE_3_INCH)->toBe(76.2)
        ->and(StripeyHorseConfig::SIZE_4_INCH)->toBe(101.6)
        ->and(StripeyHorseConfig::SIZE_5_INCH)->toBe(127.0)
        ->and(StripeyHorseConfig::SIZE_6_INCH)->toBe(152.4);
});

test('label sizes array contains common presets', function () {
    expect(StripeyHorseConfig::LABEL_SIZES)->toHaveKeys([
        '2x4', '4x2', '4x6', '6x4', '4x4', '3x5', '5x3'
    ]);
});

test('4x2 label preset has correct dimensions', function () {
    $preset = StripeyHorseConfig::LABEL_SIZES['4x2'];

    expect($preset['width'])->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($preset['height'])->toBe(StripeyHorseConfig::SIZE_2_INCH);
});

test('6x4 label preset has correct dimensions', function () {
    $preset = StripeyHorseConfig::LABEL_SIZES['6x4'];

    expect($preset['width'])->toBe(StripeyHorseConfig::SIZE_4_INCH)
        ->and($preset['height'])->toBe(StripeyHorseConfig::SIZE_6_INCH);
});
