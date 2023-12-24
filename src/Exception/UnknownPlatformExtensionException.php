<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class UnknownPlatformExtensionException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(
            'Could not determine shared library file extension from current operating system.',
            self::ERROR_UNKNOWN_PLATFORM_EXTENSION,
            $previous,
        );
    }
}
