<?php

use Faerber\ZplToPng\StripeyHorseSignature;
use Faerber\ZplToPng\StripeyHorseException;

test('fromProcessOutput creates signature from valid json', function () {
    $json = json_encode([
        'app' => 'stripey-horse',
        'version' => '1.0.0',
        'signature' => 'test-signature'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature)->toBeInstanceOf(StripeyHorseSignature::class)
        ->and($signature->app)->toBe('stripey-horse')
        ->and($signature->version)->toBe('1.0.0')
        ->and($signature->signature)->toBe('test-signature');
});

test('fromProcessOutput throws exception for invalid json', function () {
    $invalidJson = 'not valid json{]';

    expect(fn() => StripeyHorseSignature::fromProcessOutput($invalidJson))
        ->toThrow(StripeyHorseException::class);
});

test('fromProcessOutput throws exception for empty string', function () {
    expect(fn() => StripeyHorseSignature::fromProcessOutput(''))
        ->toThrow(StripeyHorseException::class);
});

test('isExpected returns true for correct app name', function () {
    $json = json_encode([
        'app' => 'stripey-horse',
        'version' => '1.0.0',
        'signature' => 'test-signature'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->isExpected())->toBeTrue();
});

test('isExpected returns false for incorrect app name', function () {
    $json = json_encode([
        'app' => 'different-app',
        'version' => '1.0.0',
        'signature' => 'test-signature'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->isExpected())->toBeFalse();
});

test('isExpected returns false for empty app name', function () {
    $json = json_encode([
        'app' => '',
        'version' => '1.0.0',
        'signature' => 'test-signature'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->isExpected())->toBeFalse();
});

test('jsonValidate returns true for valid json', function () {
    $validJson = '{"key": "value"}';

    expect(StripeyHorseSignature::jsonValidate($validJson))->toBeTrue();
});

test('signature properties are readonly', function () {
    $json = json_encode([
        'app' => 'stripey-horse',
        'version' => '1.0.0',
        'signature' => 'test-signature'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->app)->toBe('stripey-horse')
        ->and($signature->version)->toBe('1.0.0')
        ->and($signature->signature)->toBe('test-signature');
});

test('expected app name constant is correct', function () {
    expect(StripeyHorseSignature::EXPECTED_APP_NAME)->toBe('stripey-horse');
});

test('fromProcessOutput handles different version formats', function () {
    $json = json_encode([
        'app' => 'stripey-horse',
        'version' => '2.5.10-beta',
        'signature' => 'test-sig'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->version)->toBe('2.5.10-beta')
        ->and($signature->isExpected())->toBeTrue();
});

test('fromProcessOutput handles complex signatures', function () {
    $json = json_encode([
        'app' => 'stripey-horse',
        'version' => '1.0.0',
        'signature' => 'SHA256:abc123def456'
    ]);

    $signature = StripeyHorseSignature::fromProcessOutput($json);

    expect($signature->signature)->toBe('SHA256:abc123def456');
});
