<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

interface ExceptionInterface
{
    public const ERROR_PROJECT_LOCATION_INVALID = 1;
    public const ERROR_NOT_A_RUST_PROJECT = 2;
    public const ERROR_MISSING_HEADER_FILE = 3;
    public const ERROR_MISSING_HEADER_DEFINITION = 4;
    public const ERROR_MISSING_SHARED_LIBRARY_OBJECT = 5;
    public const ERROR_UNKNOWN_PLATFORM_EXTENSION = 6;
}
