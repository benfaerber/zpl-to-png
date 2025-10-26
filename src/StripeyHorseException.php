<?php

namespace Faerber\ZplToPng; 
use Exception;

/**
 * Base exception for all StripeyHorse-related errors.
 */
class StripeyHorseException extends Exception {
    protected ?string $zpl = null;

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null, ?string $zpl = null) {
        parent::__construct($message, $code, $previous);
        $this->zpl = $zpl;
    }

    /** Gets the ZPL code that caused the exception. */
    public function getZpl(): ?string {
        return $this->zpl;
    }

    /** Creates an exception for an invalid executable path. */
    public static function invalidExecutablePath(string $path, ?string $zpl = null): self {
        return new self("Invalid executable path: {$path}", 0, null, $zpl);
    }

    /** Creates an exception for a failed process execution. */
    public static function processFailed(int $exitCode, string $errorOutput, ?string $zpl = null): self {
        return new self("stripey_horse failed with exit code {$exitCode}: {$errorOutput}", 0, null, $zpl);
    }

    /** Creates an exception for when the process fails to start. */
    public static function processStartFailed(?string $zpl = null): self {
        return new self("Failed to start stripey_horse process", 0, null, $zpl);
    }

    /** Creates an exception for an unsupported system architecture. */
    public static function unsupportedArchitecture(?string $zpl = null): self {
        return new self("Unable to determine architecture", 0, null, $zpl);
    }

    public function __toString(): string {
        $output = sprintf(
            "StripeyHorse: %s in %s:%d\nStack trace:\n%s",
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        );

        if ($this->zpl !== null) {
            $output .= "\nZPL: <<<" . $this->zpl . ">>>";
        }

        return $output;
    }
}
