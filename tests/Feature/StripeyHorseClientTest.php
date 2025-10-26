<?php

use Faerber\ZplToPng\StripeyHorseClient;
use Faerber\ZplToPng\StripeyHorseConfig;
use Faerber\ZplToPng\StripeyHorseException;
use Faerber\ZplToPng\StripeyHorsePlatform;
use Faerber\ZplToPng\StripeyHorseSignature;

beforeEach(function () {
    // Get binary path from environment variable or use default
    $this->binaryPath = getenv('STRIPEY_HORSE_BINARY') ?: '/usr/local/bin/stripey-horse';

    // Check if binary exists and is executable
    $this->binaryExists = file_exists($this->binaryPath) && is_executable($this->binaryPath);

    if (!$this->binaryExists) {
        $this->markTestSkipped("stripey-horse binary not found at {$this->binaryPath}. Set STRIPEY_HORSE_BINARY env var or install stripey-horse.");
    }
});

describe('Client Instantiation', function () {
    test('can create client with valid binary path', function () {
        $client = new StripeyHorseClient($this->binaryPath);

        expect($client)->toBeInstanceOf(StripeyHorseClient::class);
    });

    test('can build client with binary path', function () {
        $client = StripeyHorseClient::buildWithBinaryPath($this->binaryPath);

        expect($client)->toBeInstanceOf(StripeyHorseClient::class);
    });

    test('can build client with binary lookup', function () {
        $client = StripeyHorseClient::buildWithBinaryLookup(function (StripeyHorsePlatform $platform) {
            return $this->binaryPath;
        });

        expect($client)->toBeInstanceOf(StripeyHorseClient::class);
    });

    test('throws exception for non-existent binary path', function () {
        $nonExistentPath = '/path/to/nonexistent/binary';

        new StripeyHorseClient($nonExistentPath);
    })->throws(StripeyHorseException::class);

    test('throws exception for non-executable binary', function () {
        $nonExecutablePath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($nonExecutablePath, 'test content');
        chmod($nonExecutablePath, 0644); // Make it non-executable

        try {
            new StripeyHorseClient($nonExecutablePath);
        } finally {
            unlink($nonExecutablePath);
        }
    })->throws(StripeyHorseException::class);

    test('throws exception for invalid executable (not stripey-horse)', function () {
        $invalidBinary = '/bin/ls'; // Valid executable but not stripey-horse

        new StripeyHorseClient($invalidBinary);
    })->throws(StripeyHorseException::class);
});

describe('Signature Retrieval', function () {
    test('can retrieve signature from binary', function () {
        $client = new StripeyHorseClient($this->binaryPath);

        $signature = $client->getSignature();

        expect($signature)
            ->toBeInstanceOf(StripeyHorseSignature::class)
            ->and($signature->isExpected())->toBeTrue();
    });

    test('signature contains version information', function () {
        $client = new StripeyHorseClient($this->binaryPath);

        $signature = $client->getSignature();

        expect($signature->version)->toBeString()
            ->and(strlen($signature->version))->toBeGreaterThan(0);
    });
});

describe('ZPL to PNG Conversion', function () {
    test('can convert simple ZPL to PNG', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = new StripeyHorseConfig();

        // Simple ZPL code
        $zplContent = '^XA^FO50,50^A0N,50,50^FDHello World^FS^XZ';

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            // Check PNG magic bytes
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert ZPL from test file', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('4x2')
            ->build();

        $zplContent = file_get_contents(__DIR__ . '/../../test_data/test_4x2.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with 6x4 label preset', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('6x4')
            ->build();

        $zplContent = file_get_contents(__DIR__ . '/../../test_data/horse.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with rotation', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('4x2')
            ->rotation(90)
            ->build();

        $zplContent = file_get_contents(__DIR__ . '/../../test_data/test_4x2.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with 180 degree rotation', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('4x2')
            ->rotation(180)
            ->build();

        $zplContent = file_get_contents(__DIR__ . '/../../test_data/clean_seed.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with 270 degree rotation', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('4x2')
            ->rotation(270)
            ->build();

        $zplContent = file_get_contents(__DIR__ . '/../../test_data/clean_seed.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with custom dimensions', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = new StripeyHorseConfig(
            rotation: 0,
            labelWidthMm: 100.0,
            labelHeightMm: 150.0,
            dpmm: 8
        );

        $zplContent = '^XA^FO50,50^A0N,50,50^FDCustom Size^FS^XZ';

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('can convert with different dpmm', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = new StripeyHorseConfig(
            rotation: 0,
            labelWidthMm: StripeyHorseConfig::SIZE_4_INCH,
            labelHeightMm: StripeyHorseConfig::SIZE_2_INCH,
            dpmm: 12 // Higher resolution
        );

        $zplContent = '^XA^FO50,50^A0N,50,50^FDHigh DPI^FS^XZ';

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });

    test('produces different output for different rotations', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $zplContent = file_get_contents(__DIR__ . '/../../test_data/test_4x2.zplbin');

        $config0 = StripeyHorseConfig::builder()->labelPreset('4x2')->rotation(0)->build();
        $config90 = StripeyHorseConfig::builder()->labelPreset('4x2')->rotation(90)->build();

        $image0 = $client->convertZplToRawImage($zplContent, $config0);
        $image90 = $client->convertZplToRawImage($zplContent, $config90);

        // Images should be different when rotated
        expect($image0)->not->toBe($image90)
            ->and(strlen($image0))->toBeGreaterThan(0)
            ->and(strlen($image90))->toBeGreaterThan(0);
    });
});

describe('Error Handling', function () {
    test('throws exception for invalid ZPL content', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = new StripeyHorseConfig();

        // Empty or invalid ZPL might cause errors
        $invalidZpl = '';

        $client->convertZplToRawImage($invalidZpl, $config);
    })->throws(StripeyHorseException::class);

    test('can handle complex ZPL files', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = StripeyHorseConfig::builder()
            ->labelPreset('6x4')
            ->build();

        // Test with pumpkin ZPL which should be complex
        $zplContent = file_get_contents(__DIR__ . '/../../test_data/pumpkin.zplbin');

        $imageData = $client->convertZplToRawImage($zplContent, $config);

        expect($imageData)->toBeString()
            ->and(strlen($imageData))->toBeGreaterThan(0)
            ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
    });
});

describe('Integration Tests', function () {
    test('can convert and verify all test ZPL files', function () {
        $client = new StripeyHorseClient($this->binaryPath);

        $testFiles = [
            ['file' => 'test_4x2.zplbin', 'preset' => '4x2'],
            ['file' => 'clean_seed.zplbin', 'preset' => '4x2'],
            ['file' => 'horse.zplbin', 'preset' => '6x4'],
            ['file' => 'pumpkin.zplbin', 'preset' => '6x4'],
        ];

        foreach ($testFiles as $test) {
            $zplContent = file_get_contents(__DIR__ . '/../../test_data/' . $test['file']);
            $config = StripeyHorseConfig::builder()
                ->labelPreset($test['preset'])
                ->build();

            $imageData = $client->convertZplToRawImage($zplContent, $config);

            expect($imageData)->toBeString()
                ->and(strlen($imageData))->toBeGreaterThan(0)
                ->and(substr($imageData, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
        }
    });

    test('client can be reused for multiple conversions', function () {
        $client = new StripeyHorseClient($this->binaryPath);
        $config = new StripeyHorseConfig();

        $zpl1 = '^XA^FO50,50^A0N,50,50^FDFirst^FS^XZ';
        $zpl2 = '^XA^FO50,50^A0N,50,50^FDSecond^FS^XZ';
        $zpl3 = '^XA^FO50,50^A0N,50,50^FDThird^FS^XZ';

        $image1 = $client->convertZplToRawImage($zpl1, $config);
        $image2 = $client->convertZplToRawImage($zpl2, $config);
        $image3 = $client->convertZplToRawImage($zpl3, $config);

        expect($image1)->toBeString()
            ->and($image2)->toBeString()
            ->and($image3)->toBeString()
            ->and(strlen($image1))->toBeGreaterThan(0)
            ->and(strlen($image2))->toBeGreaterThan(0)
            ->and(strlen($image3))->toBeGreaterThan(0);
    });
});
