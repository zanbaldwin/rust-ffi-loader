<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class InvalidRustProjectException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(
        public readonly string $suppliedLocation,
        ?\Throwable $previous = null,
        ?int $code = null,
    ) {
        parent::__construct(sprintf(
            'The location "%s" is not a valid, readable Rust project.',
            $this->suppliedLocation,
        ), $code ?? 0, $previous);
    }

    public static function dir(string $suppliedLocation): self
    {
        return new self($suppliedLocation, code: self::ERROR_PROJECT_LOCATION_INVALID);
    }

    public static function rust(string $suppliedLocation): self
    {
        return new self($suppliedLocation, code: self::ERROR_NOT_A_RUST_PROJECT);
    }
}
