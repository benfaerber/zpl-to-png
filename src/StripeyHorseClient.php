<?php

namespace App\Services\RateShopper\StripeyHorse;

/**
* A client to use the `stripey_horse` exe from PHP.
* Converts ZPL to a PNG image locally
*/
class StripeyHorseClient {
    private string $executablePath;

    public function __construct(string $executablePath) {
        $this->executablePath = $executablePath;
        if (! file_exists($this->executablePath)) {
            throw StripeyHorseException::invalidExecutablePath($this->executablePath);
        }

        if (! is_executable($this->executablePath)) {
            throw StripeyHorseException::invalidExecutablePath($this->executablePath);
        }
    }

    /**
    * Builds a StripeyHorseClient by looking up the binary path for the detected platform.
    * 
    * @param callable(StripeyHorsePlatform): string $binaryLookup A function that takes a StripeyHorsePlatform and returns the binary path string
    * @return self A new StripeyHorseClient instance
    */
    public static function buildWithBinaryLookup(callable $binaryLookup): self {
        $platform = StripeyHorsePlatform::detect(); 
        $binaryPath = $binaryLookup($platform);
        return new self($binaryPath);
    }

    /**
    * Converts ZPL into a PNG image with given settings.
    * @return string a raw PNG binary string
    */
    public function convertZplToRawImage(
        string $zplContent,
        StripeyHorseConfig $config,
    ): string {
        $command = $this->buildCommand($config);
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $options = [
            'timeout' => 10
        ];

        $process = proc_open(
            $command, 
            $descriptorSpec, 
            $pipes,
            options: $options,
        );

        if (! is_resource($process)) {
            throw StripeyHorseException::processStartFailed($zplContent);
        }

        fwrite($pipes[0], $zplContent);
        fclose($pipes[0]);

        $imageData = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw StripeyHorseException::processFailed($exitCode, $errorOutput, $zplContent);
        }

        return $imageData;
    }

    private function buildCommand(StripeyHorseConfig $config): string {
        $jsonPayload = $config->toJsonPayload();
        return sprintf(
            '%s --config %s',
            escapeshellarg($this->executablePath),
            escapeshellarg($jsonPayload),
        );
    }
}
