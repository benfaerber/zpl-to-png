<?php

use Faerber\ZplToPng\StripeyHorseException;

test('exception extends base exception', function () {
    $exception = new StripeyHorseException('test message');

    expect($exception)->toBeInstanceOf(Exception::class);
});

test('can create exception with message', function () {
    $exception = new StripeyHorseException('test message');

    expect($exception->getMessage())->toBe('test message');
});

test('can create exception with message and code', function () {
    $exception = new StripeyHorseException('test message', 123);

    expect($exception->getMessage())->toBe('test message')
        ->and($exception->getCode())->toBe(123);
});

test('can create exception with zpl content', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = new StripeyHorseException('test message', 0, null, $zpl);

    expect($exception->getZpl())->toBe($zpl);
});

test('getZpl returns null when no zpl provided', function () {
    $exception = new StripeyHorseException('test message');

    expect($exception->getZpl())->toBeNull();
});

test('invalidExecutablePath creates exception with correct message', function () {
    $exception = StripeyHorseException::invalidExecutablePath('/path/to/binary');

    expect($exception->getMessage())->toContain('Invalid executable path')
        ->and($exception->getMessage())->toContain('/path/to/binary');
});

test('invalidExecutablePath can include zpl', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = StripeyHorseException::invalidExecutablePath('/path/to/binary', $zpl);

    expect($exception->getZpl())->toBe($zpl);
});

test('processFailed creates exception with exit code and error output', function () {
    $exception = StripeyHorseException::processFailed(1, 'Error output here');

    expect($exception->getMessage())->toContain('stripey_horse failed')
        ->and($exception->getMessage())->toContain('exit code 1')
        ->and($exception->getMessage())->toContain('Error output here');
});

test('processFailed can include zpl', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = StripeyHorseException::processFailed(1, 'Error output', $zpl);

    expect($exception->getZpl())->toBe($zpl);
});

test('processStartFailed creates exception with correct message', function () {
    $exception = StripeyHorseException::processStartFailed();

    expect($exception->getMessage())->toContain('Failed to start stripey_horse process');
});

test('processStartFailed can include zpl', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = StripeyHorseException::processStartFailed($zpl);

    expect($exception->getZpl())->toBe($zpl);
});

test('unsupportedArchitecture creates exception with correct message', function () {
    $exception = StripeyHorseException::unsupportedArchitecture();

    expect($exception->getMessage())->toContain('Unable to determine architecture');
});

test('unsupportedArchitecture can include zpl', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = StripeyHorseException::unsupportedArchitecture($zpl);

    expect($exception->getZpl())->toBe($zpl);
});

test('toString includes message and file', function () {
    $exception = new StripeyHorseException('test message');
    $string = $exception->__toString();

    expect($string)->toContain('test message')
        ->and($string)->toContain('StripeyHorse:')
        ->and($string)->toContain('Stack trace:');
});

test('toString includes zpl when present', function () {
    $zpl = '^XA^FO100,100^A0N,50,50^FDTest^FS^XZ';
    $exception = new StripeyHorseException('test message', 0, null, $zpl);
    $string = $exception->__toString();

    expect($string)->toContain('ZPL: <<<')
        ->and($string)->toContain($zpl)
        ->and($string)->toContain('>>>');
});

test('toString does not include zpl when not present', function () {
    $exception = new StripeyHorseException('test message');
    $string = $exception->__toString();

    expect($string)->not->toContain('ZPL:');
});

test('can catch exception as StripeyHorseException', function () {
    try {
        throw StripeyHorseException::invalidExecutablePath('/invalid/path');
    } catch (StripeyHorseException $e) {
        expect($e)->toBeInstanceOf(StripeyHorseException::class)
            ->and($e->getMessage())->toContain('Invalid executable path');
    }
});

test('can catch exception as base Exception', function () {
    try {
        throw StripeyHorseException::processFailed(1, 'error');
    } catch (Exception $e) {
        expect($e)->toBeInstanceOf(Exception::class)
            ->and($e)->toBeInstanceOf(StripeyHorseException::class);
    }
});
