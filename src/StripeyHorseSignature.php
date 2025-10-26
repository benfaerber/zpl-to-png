<?php

namespace Faerber\ZplToPng;

/**
* A signature send from `stripey-horse`.
* Allows the user to make sure its the proper binary.
*/
class StripeyHorseSignature {
    public const EXPECTED_APP_NAME = "stripey-horse";

    private function __construct(
        public readonly string $app,
        public readonly string $version,
        public readonly string $signature,
    ) {
    }

    /** Creates a signature instance from the process output JSON. */
    public static function fromProcessOutput(string $processOutput): self {
        if (! self::jsonValidate($processOutput)) {
            throw new StripeyHorseException("Invalid signature!");
        }

        $data = json_decode($processOutput);

        if (! is_object($data)) {
            throw new StripeyHorseException("Invalid signature format!");
        }

        if (! isset($data->app) || ! is_string($data->app)) {
            throw new StripeyHorseException("Invalid signature: missing or invalid app field!");
        }

        if (! isset($data->version) || ! is_string($data->version)) {
            throw new StripeyHorseException("Invalid signature: missing or invalid version field!");
        }

        if (! isset($data->signature) || ! is_string($data->signature)) {
            throw new StripeyHorseException("Invalid signature: missing or invalid signature field!");
        }

        return new self(
            app: $data->app,
            version: $data->version,
            signature: $data->signature,
        );
    }

    /** Checks if the signature matches the expected application name. */
    public function isExpected(): bool {
        return $this->app === self::EXPECTED_APP_NAME;
    }

    /** Validates JSON string for compatibility with older PHP versions. */
    public static function jsonValidate(string $jsonData): bool {
        json_decode($jsonData);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
