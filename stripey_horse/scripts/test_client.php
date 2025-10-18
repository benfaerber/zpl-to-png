#!/usr/bin/php
<?php
foreach (glob('./client/StripeyHorse/*.php') as $file) {
    require_once $file;
}

use App\Services\RateShopper\StripeyHorse\StripeyHorseClient;
use App\Services\RateShopper\StripeyHorse\StripeyHorseConfig;

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
    $converter = new ZplConverter('./builds/stripey_horse_amd64');
    
    $converter->convertAndSave(
        './test_data/ups_1.zplbin',
        '4x6',
        './test_data/php_client_output.png'
    );
    
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
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
