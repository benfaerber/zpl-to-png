<?php

namespace Faerber\ZplToPng;

enum StripeyHorsePlatform: string {
    /** AMD 64: the platform a linux laptop / docker runs on */
    case Amd64 = "amd64";
    /** ARM 64: some azure servers use arm */
    case Arm64 = "arm64";

    /**
     * Detect the current platform architecture
     *
     * @return self The detected platform
     * @throws StripeyHorseException If the architecture is not supported
     */
    public static function detect(): self {
        if (PHP_INT_SIZE === 8) {
            $uname = php_uname('m');
            if (str_contains($uname, 'arm') || str_contains($uname, 'aarch64')) {
                return self::Arm64;
            }

            return self::Amd64;
        }

        throw StripeyHorseException::unsupportedArchitecture();
    }

    /**
     * Check if this is an AMD64 platform
     */
    public function isAmd64(): bool {
        return $this === self::Amd64;
    }

    /**
     * Check if this is an ARM64 platform
     */
    public function isArm64(): bool {
        return $this === self::Arm64;
    }

    /**
     * Get a human-readable description of the platform
     */
    public function getDescription(): string {
        return match ($this) {
            self::Amd64 => 'AMD/Intel 64-bit (x86_64)',
            self::Arm64 => 'ARM 64-bit (aarch64)',
        };
    }

    /**
     * Get alternative names for this architecture
     *
     * @return array<string>
     */
    public function getAliases(): array {
        return match ($this) {
            self::Amd64 => ['x86_64', 'x64', 'amd64'],
            self::Arm64 => ['aarch64', 'arm64'],
        };
    }

    /**
     * Get the bit width of the platform
     */
    public function getBitWidth(): int {
        return match ($this) {
            self::Amd64 => 64,
            self::Arm64 => 64,
        };
    }

    /**
     * Check if a given machine type string matches this platform
     */
    public function matchesMachineType(string $machineType): bool {
        $normalized = strtolower(trim($machineType));

        return in_array($normalized, $this->getAliases(), true) || $normalized === $this->value;
    }

    /**
     * Get system information for debugging
     *
     * @return array<string, mixed>
     */
    public static function getSystemInfo(): array {
        return [
            'php_int_size' => PHP_INT_SIZE,
            'php_os' => PHP_OS,
            'php_os_family' => PHP_OS_FAMILY,
            'uname_machine' => php_uname('m'),
            'uname_system' => php_uname('s'),
            'uname_release' => php_uname('r'),
            'detected_platform' => self::detect()->value,
        ];
    }

    /**
     * Try to detect platform from a machine type string
     *
     * @param string $machineType The machine type string (e.g., from uname -m)
     * @return self|null The detected platform or null if not recognized
     */
    public static function fromMachineType(string $machineType): ?self {
        foreach (self::cases() as $platform) {
            if ($platform->matchesMachineType($machineType)) {
                return $platform;
            }
        }

        return null;
    }
}
