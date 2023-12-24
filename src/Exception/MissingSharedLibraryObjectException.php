<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class MissingSharedLibraryObjectException extends \RuntimeException implements ExceptionInterface
{
    /** @param string[] $checked */
    public function __construct(
        array $checked,
        string $crateName,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Rust crate "%s" does not contain the expected shared library object. '
            . 'The following locations were checked:' . \PHP_EOL . '%s',
            $crateName,
            implode(\PHP_EOL, $checked),
        ), self::ERROR_MISSING_SHARED_LIBRARY_OBJECT, $previous);
    }
}
