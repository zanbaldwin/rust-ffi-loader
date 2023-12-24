<?php declare(strict_types=1);

use ZanBaldwin\FFI\Example\ArrayExternalLibrary;

require_once __DIR__ . '/../vendor/autoload.php';

$kernelProjectDir = realpath(__DIR__ . '/..');
$arrayExternalLibrary = new ArrayExternalLibrary(
    sprintf('%s/../rust', $kernelProjectDir),
    'array',
    true,
);
$filledJsonArray = $arrayExternalLibrary->fill(5);
echo $filledJsonArray . \PHP_EOL;
