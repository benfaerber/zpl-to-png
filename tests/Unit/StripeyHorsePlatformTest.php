<?php

use Faerber\ZplToPng\StripeyHorsePlatform;
use Faerber\ZplToPng\StripeyHorseException;

test('enum has correct cases', function () {
    $cases = StripeyHorsePlatform::cases();

    expect($cases)->toHaveCount(2)
        ->and($cases[0])->toBe(StripeyHorsePlatform::Amd64)
        ->and($cases[1])->toBe(StripeyHorsePlatform::Arm64);
});

test('amd64 has correct value', function () {
    expect(StripeyHorsePlatform::Amd64->value)->toBe('amd64');
});

test('arm64 has correct value', function () {
    expect(StripeyHorsePlatform::Arm64->value)->toBe('arm64');
});

test('isAmd64 returns true for amd64 platform', function () {
    expect(StripeyHorsePlatform::Amd64->isAmd64())->toBeTrue()
        ->and(StripeyHorsePlatform::Arm64->isAmd64())->toBeFalse();
});

test('isArm64 returns true for arm64 platform', function () {
    expect(StripeyHorsePlatform::Arm64->isArm64())->toBeTrue()
        ->and(StripeyHorsePlatform::Amd64->isArm64())->toBeFalse();
});

test('getDescription returns correct description for amd64', function () {
    expect(StripeyHorsePlatform::Amd64->getDescription())
        ->toBe('AMD/Intel 64-bit (x86_64)');
});

test('getDescription returns correct description for arm64', function () {
    expect(StripeyHorsePlatform::Arm64->getDescription())
        ->toBe('ARM 64-bit (aarch64)');
});

test('getAliases returns correct aliases for amd64', function () {
    $aliases = StripeyHorsePlatform::Amd64->getAliases();

    expect($aliases)->toBe(['x86_64', 'x64', 'amd64']);
});

test('getAliases returns correct aliases for arm64', function () {
    $aliases = StripeyHorsePlatform::Arm64->getAliases();

    expect($aliases)->toBe(['aarch64', 'arm64']);
});

test('getBitWidth returns 64 for amd64', function () {
    expect(StripeyHorsePlatform::Amd64->getBitWidth())->toBe(64);
});

test('getBitWidth returns 64 for arm64', function () {
    expect(StripeyHorsePlatform::Arm64->getBitWidth())->toBe(64);
});

test('matchesMachineType matches amd64 aliases', function () {
    expect(StripeyHorsePlatform::Amd64->matchesMachineType('x86_64'))->toBeTrue()
        ->and(StripeyHorsePlatform::Amd64->matchesMachineType('x64'))->toBeTrue()
        ->and(StripeyHorsePlatform::Amd64->matchesMachineType('amd64'))->toBeTrue();
});

test('matchesMachineType matches arm64 aliases', function () {
    expect(StripeyHorsePlatform::Arm64->matchesMachineType('aarch64'))->toBeTrue()
        ->and(StripeyHorsePlatform::Arm64->matchesMachineType('arm64'))->toBeTrue();
});

test('matchesMachineType is case insensitive', function () {
    expect(StripeyHorsePlatform::Amd64->matchesMachineType('X86_64'))->toBeTrue()
        ->and(StripeyHorsePlatform::Arm64->matchesMachineType('AARCH64'))->toBeTrue();
});

test('matchesMachineType handles whitespace', function () {
    expect(StripeyHorsePlatform::Amd64->matchesMachineType(' x86_64 '))->toBeTrue()
        ->and(StripeyHorsePlatform::Arm64->matchesMachineType(' arm64 '))->toBeTrue();
});

test('matchesMachineType returns false for non-matching types', function () {
    expect(StripeyHorsePlatform::Amd64->matchesMachineType('arm64'))->toBeFalse()
        ->and(StripeyHorsePlatform::Arm64->matchesMachineType('x86_64'))->toBeFalse();
});

test('fromMachineType returns correct platform for amd64 types', function () {
    expect(StripeyHorsePlatform::fromMachineType('x86_64'))->toBe(StripeyHorsePlatform::Amd64)
        ->and(StripeyHorsePlatform::fromMachineType('x64'))->toBe(StripeyHorsePlatform::Amd64)
        ->and(StripeyHorsePlatform::fromMachineType('amd64'))->toBe(StripeyHorsePlatform::Amd64);
});

test('fromMachineType returns correct platform for arm64 types', function () {
    expect(StripeyHorsePlatform::fromMachineType('aarch64'))->toBe(StripeyHorsePlatform::Arm64)
        ->and(StripeyHorsePlatform::fromMachineType('arm64'))->toBe(StripeyHorsePlatform::Arm64);
});

test('fromMachineType returns null for unknown types', function () {
    expect(StripeyHorsePlatform::fromMachineType('unknown'))->toBeNull()
        ->and(StripeyHorsePlatform::fromMachineType('i386'))->toBeNull()
        ->and(StripeyHorsePlatform::fromMachineType(''))->toBeNull();
});

test('getSystemInfo returns array with expected keys', function () {
    $info = StripeyHorsePlatform::getSystemInfo();

    expect($info)->toBeArray()
        ->toHaveKeys([
            'php_int_size',
            'php_os',
            'php_os_family',
            'uname_machine',
            'uname_system',
            'uname_release',
            'detected_platform'
        ]);
});

test('getSystemInfo contains valid data', function () {
    $info = StripeyHorsePlatform::getSystemInfo();

    expect($info['php_int_size'])->toBeInt()
        ->and($info['php_os'])->toBeString()
        ->and($info['php_os_family'])->toBeString()
        ->and($info['uname_machine'])->toBeString()
        ->and($info['detected_platform'])->toBeString();
});

test('detect returns a valid platform on 64-bit systems', function () {
    if (PHP_INT_SIZE !== 8) {
        expect(fn() => StripeyHorsePlatform::detect())
            ->toThrow(StripeyHorseException::class);
    } else {
        $platform = StripeyHorsePlatform::detect();
        expect($platform)->toBeInstanceOf(StripeyHorsePlatform::class);
    }
});

test('detect returns amd64 or arm64', function () {
    if (PHP_INT_SIZE === 8) {
        $platform = StripeyHorsePlatform::detect();
        expect($platform)->toBeIn([StripeyHorsePlatform::Amd64, StripeyHorsePlatform::Arm64]);
    } else {
        expect(true)->toBeTrue(); // Skip test on 32-bit
    }
});
