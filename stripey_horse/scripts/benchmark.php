#!/usr/bin/php
<?php

foreach (glob('./client/StripeyHorse/*.php') as $file) {
    require_once $file;
}

use App\Services\RateShopper\StripeyHorse\StripeyHorseClient;
use App\Services\RateShopper\StripeyHorse\StripeyHorseConfig;

class ZplBenchmark {
    private $testFiles = [
        './test_data/ups_1.zplbin',
        './test_data/endicia_1.zplbin',
        './test_data/fedex_1.zplbin',
        './test_data/usps_1.zplbin'
    ];

    private $iterations = 10;
    private $client;

    public function __construct($iterations = 10) {
        $this->iterations = $iterations;
        $this->client = new StripeyHorseClient('./builds/stripey_horse_amd64');
    }

    public function run() {
        echo "🐴 Stripey Horse ZPL Benchmark\n";
        echo "================================\n";
        echo "Iterations per test: {$this->iterations}\n";
        echo "Test files: " . count($this->testFiles) . "\n\n";

        $totalResults = [
            'php_client' => ['total_time' => 0, 'total_size' => 0, 'successes' => 0, 'failures' => 0],
            'labelary_api' => ['total_time' => 0, 'total_size' => 0, 'successes' => 0, 'failures' => 0]
        ];

        foreach ($this->testFiles as $testFile) {
            if (!file_exists($testFile)) {
                echo "⚠️  Test file not found: $testFile\n";
                continue;
            }

            echo "Testing: " . basename($testFile) . "\n";
            echo str_repeat('-', 40) . "\n";

            $phpResults = $this->benchmarkPhpClient($testFile);
            $apiResults = $this->benchmarkLabelaryApi($testFile);

            $this->displayResults($phpResults, $apiResults);

            $totalResults['php_client']['total_time'] += $phpResults['avg_time'];
            $totalResults['php_client']['total_size'] += $phpResults['avg_size'];
            $totalResults['php_client']['successes'] += $phpResults['successes'];
            $totalResults['php_client']['failures'] += $phpResults['failures'];

            $totalResults['labelary_api']['total_time'] += $apiResults['avg_time'];
            $totalResults['labelary_api']['total_size'] += $apiResults['avg_size'];
            $totalResults['labelary_api']['successes'] += $apiResults['successes'];
            $totalResults['labelary_api']['failures'] += $apiResults['failures'];

            echo "\n";
        }

        $this->displaySummary($totalResults);
    }

    private function benchmarkPhpClient($zplFile, $rotation = 0) {
        echo "  PHP Client ({$rotation}°): ";
        $zplContent = file_get_contents($zplFile);
        $times = [];
        $sizes = [];
        $successes = 0;
        $failures = 0;

        $config = StripeyHorseConfig::builder()->build();

        for ($i = 0; $i < $this->iterations; $i++) {
            $startTime = microtime(true);

            try {
                $imageData = $this->client->convertZplToRawImage($zplContent, $config);
                $endTime = microtime(true);

                $times[] = ($endTime - $startTime) * 1000; // Convert to milliseconds
                $sizes[] = strlen($imageData);
                $successes++;
                echo ".";
            } catch (Exception $e) {
                $failures++;
                echo "x";
            }
        }

        echo "\n";

        return [
            'avg_time' => count($times) > 0 ? array_sum($times) / count($times) : 0,
            'min_time' => count($times) > 0 ? min($times) : 0,
            'max_time' => count($times) > 0 ? max($times) : 0,
            'avg_size' => count($sizes) > 0 ? array_sum($sizes) / count($sizes) : 0,
            'successes' => $successes,
            'failures' => $failures
        ];
    }

    private function benchmarkLabelaryApi($zplFile, $rotation = 180) {
        echo "  Labelary API ({$rotation}°): ";
        $times = [];
        $sizes = [];
        $successes = 0;
        $failures = 0;

        for ($i = 0; $i < $this->iterations; $i++) {
            $startTime = microtime(true);

            $tempFile = tempnam(sys_get_temp_dir(), 'benchmark_');
            $cmd = sprintf(
                'curl -X POST -H "Accept: image/png" -H "X-Rotation: %d" -H "Content-Type: application/x-www-form-urlencoded" --data-binary "@%s" "http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/" -o %s -s -w "%%{http_code}"',
                $rotation,
                $zplFile,
                $tempFile
            );

            $httpCode = shell_exec($cmd);
            $endTime = microtime(true);

            if (trim($httpCode) === '200' && file_exists($tempFile) && filesize($tempFile) > 0) {
                $times[] = ($endTime - $startTime) * 1000; // Convert to milliseconds
                $sizes[] = filesize($tempFile);
                $successes++;
                echo ".";
            } else {
                $failures++;
                echo "x";
            }

            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        echo "\n";

        return [
            'avg_time' => count($times) > 0 ? array_sum($times) / count($times) : 0,
            'min_time' => count($times) > 0 ? min($times) : 0,
            'max_time' => count($times) > 0 ? max($times) : 0,
            'avg_size' => count($sizes) > 0 ? array_sum($sizes) / count($sizes) : 0,
            'successes' => $successes,
            'failures' => $failures
        ];
    }

    private function displayResults($phpResults, $apiResults) {
        echo "  Results:\n";
        echo "    PHP Client:\n";
        echo sprintf("      ⏱️  Time: %.1fms (min: %.1fms, max: %.1fms)\n",
            $phpResults['avg_time'], $phpResults['min_time'], $phpResults['max_time']);
        echo sprintf("      📏 Size: %d bytes\n", (int)$phpResults['avg_size']);
        echo sprintf("      ✅ Success: %d/%d (%.1f%%)\n",
            $phpResults['successes'], $this->iterations,
            ($phpResults['successes'] / $this->iterations) * 100);

        echo "    Labelary API:\n";
        echo sprintf("      ⏱️  Time: %.1fms (min: %.1fms, max: %.1fms)\n",
            $apiResults['avg_time'], $apiResults['min_time'], $apiResults['max_time']);
        echo sprintf("      📏 Size: %d bytes\n", (int)$apiResults['avg_size']);
        echo sprintf("      ✅ Success: %d/%d (%.1f%%)\n",
            $apiResults['successes'], $this->iterations,
            ($apiResults['successes'] / $this->iterations) * 100);

        if ($phpResults['avg_time'] > 0 && $apiResults['avg_time'] > 0) {
            $speedup = $apiResults['avg_time'] / $phpResults['avg_time'];
            if ($speedup > 1) {
                echo sprintf("    🚀 PHP Client is %.1fx faster\n", $speedup);
            } else {
                echo sprintf("    🌐 Labelary API is %.1fx faster\n", 1/$speedup);
            }
        }
    }

    private function displaySummary($totalResults) {
        echo "Overall Summary\n";
        echo "===============\n";

        $testCount = count($this->testFiles);

        echo "PHP Client (Local):\n";
        echo sprintf("  Average time per test: %.1fms\n", $totalResults['php_client']['total_time'] / $testCount);
        echo sprintf("  Average size per test: %d bytes\n", (int)($totalResults['php_client']['total_size'] / $testCount));
        echo sprintf("  Total successes: %d/%d\n", $totalResults['php_client']['successes'], $testCount * $this->iterations);

        echo "\nLabelary API (Remote):\n";
        echo sprintf("  Average time per test: %.1fms\n", $totalResults['labelary_api']['total_time'] / $testCount);
        echo sprintf("  Average size per test: %d bytes\n", (int)($totalResults['labelary_api']['total_size'] / $testCount));
        echo sprintf("  Total successes: %d/%d\n", $totalResults['labelary_api']['successes'], $testCount * $this->iterations);

        $phpAvg = $totalResults['php_client']['total_time'] / $testCount;
        $apiAvg = $totalResults['labelary_api']['total_time'] / $testCount;

        if ($phpAvg > 0 && $apiAvg > 0) {
            echo "\nOverall Performance:\n";
            if ($apiAvg > $phpAvg) {
                echo sprintf("  🏆 PHP Client is %.1fx faster overall\n", $apiAvg / $phpAvg);
            } else {
                echo sprintf("  🏆 Labelary API is %.1fx faster overall\n", $phpAvg / $apiAvg);
            }
        }
    }
}

// Parse command line arguments
$iterations = 10;
if ($argc > 1) {
    $iterations = (int)$argv[1];
    if ($iterations < 1) {
        echo "Usage: php benchmark.php [iterations]\n";
        echo "  iterations: Number of iterations per test (default: 10)\n";
        exit(1);
    }
}

try {
    $benchmark = new ZplBenchmark($iterations);
    $benchmark->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
