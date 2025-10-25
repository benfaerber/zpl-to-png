<?php
namespace Faerber\ZplToPng;

enum StripeyHorsePlatform: string {
    /** AMD 64: the platform a linux laptop / docker runs on */
    case Amd64 = "amd64";
    /** ARM 64: some azure servers use arm */
    case Arm64 = "arm64";

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
}
