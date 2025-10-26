#!/usr/bin/php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Faerber\ZplToPng\StripeyHorseClient;
use Faerber\ZplToPng\StripeyHorseConfig;

class ZplConverter {
    private StripeyHorseClient $client;

    public function __construct(string $binaryPath) {
        $this->client = new StripeyHorseClient($binaryPath);
    }

    public function convertAndSave(string $zplFile, string $labelPreset, string $outputFile, int $rotate = 0): void {
        echo "Converting ZPL to image ({$labelPreset})...\n";

        $zplContent = file_get_contents($zplFile);

        $config = StripeyHorseConfig::builder()
            ->labelPreset($labelPreset)
            ->rotation($rotate)
            ->build();

        $imageData = $this->client->convertZplToRawImage($zplContent, $config);

        echo "Image generated successfully!\n";
        echo "Image size: " . strlen($imageData) . " bytes\n";

        file_put_contents($outputFile, $imageData);
        echo "Image saved to: {$outputFile}\n\n";
    }
}

try {
    $converter = new ZplConverter($argv[1]);

    $converter->convertAndSave(
        './test_data/test_4x2.zplbin',
        '4x2',
        './test_data/php_client_output_small.png'
    );



    $converter->convertAndSave(
        './test_data/clean_seed.zplbin',
        '4x2',
        './test_data/php_client_output_clean_180.png',
        rotate: 270
    );


    $converter->convertAndSave(
        './test_data/clean_seed.zplbin',
        '6x4',
        './test_data/clean_seed_output.png'
    );

    $converter->convertAndSave(
        './test_data/horse.zplbin',
        '6x4',
        './test_data/horse_output.png',
        rotate: 90,
    );

    $converter->convertAndSave(
        './test_data/pumpkin.zplbin',
        '6x4',
        './test_data/pumpkin_output.png'
    );

    $converter->convertAndSave(
        './test_data/fedex.zplbin',
        '6x4',
        './test_data/fedex_output.png'
    );
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
