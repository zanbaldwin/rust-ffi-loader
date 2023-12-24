<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class MissingHeaderFileException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(
        string $crateName,
        string $projectLocation,
        ?string $libraryHeaderFile = null,
        ?\Throwable $previous = null,
        ?int $code = null,
    ) {
        parent::__construct(sprintf(
            'Rust crate "%s" at project location "%s" does not contain the expected library header file "%s".'
            . \PHP_EOL . 'Consider using the "cbindgen" crate to automatically generate the header file during the build stage.',
            $crateName,
            $projectLocation,
            $libraryHeaderFile ?? sprintf('lib%s.h', $crateName),
        ), $code ?? 0, $previous);
    }

    public static function file(string $crateName, string $projectLocation, ?string $libraryHeaderFile = null): self
    {
        return new self($crateName, $projectLocation, $libraryHeaderFile, code: self::ERROR_MISSING_HEADER_FILE);
    }

    public static function read(string $crateName, string $projectLocation, ?string $libraryHeaderFile = null): self
    {
        return new self($crateName, $projectLocation, $libraryHeaderFile, code: self::ERROR_MISSING_HEADER_DEFINITION);
    }
}
