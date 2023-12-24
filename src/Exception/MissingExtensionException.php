<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class MissingExtensionException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('The FFI extension is not available on this PHP installation.', 0, $previous);
    }
}
