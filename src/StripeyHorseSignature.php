<?php

namespace Faerber\ZplToPng;

/**
* A signature send from `stripey-horse`.
* Allows the user to make sure its the proper binary. 
*/
class StripeyHorseSignature {
    const EXPECTED_APP_NAME = "stripey-horse";

    private function __construct(
        public readonly string $app,
        public readonly string $version,
        public readonly string $signature,
    )
    {
        
    }

    public static function fromProcessOutput(string $processOutput): self {
        if (! self::jsonValidate($processOutput)) {
            throw StripeyHorseException::invalidExecutablePath("Invalid signature from stripey-horse!");
        }

        $data = json_decode($processOutput);
        return new self(
            app: $data->app,
            version: $data->version,
            signature: $data->signature,
        );
    }

    public function isExpected(): bool {
        return $this->app === self::EXPECTED_APP_NAME;
    }

    /** For older PHP versions */ 
    public static function jsonValidate(string $jsonData): bool {
        json_encode($jsonData);
        return json_last_error() === JSON_ERROR_NONE;
    } 
}
