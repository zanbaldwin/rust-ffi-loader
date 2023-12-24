<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Example;

use ZanBaldwin\FFI\AbstractLoader;

class ArrayExternalLibrary extends AbstractLoader
{
    public function fill(int $length): string
    {
        return $this->ffi->fill((string) $length);
    }
}
