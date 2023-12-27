<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

use FFI\Exception as FFIException;
use ZanBaldwin\FFI\Util;

class LibraryLoadException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(
        string $sharedLibraryObject,
        FFIException $previous,
    ) {
        $message = sprintf('Could not load Rust-based shared library "%s".', $sharedLibraryObject);
        $message .= ' Please ensure the build target for the Rust project matches the environment this PHP installation runs on.';

        $is82Bug = (\PHP_VERSION_ID >= 80200 && \PHP_VERSION_ID < 80214);
        $is83Bug = (\PHP_VERSION_ID >= 80300 && \PHP_VERSION_ID < 80301);
        if ($is82Bug || $is83Bug) {
            $message .= ' If you are running a Zend test observer (such as XDebug), upgrade to PHP 8.2.14+ or 8.3.1+.';
        }

        if (Util::isAlpine() || Util::hasMusl()) {
            $message .= ' If you are running PHP on an Alpine/Musl-based operating system, make sure to install a GLibC-compatibility layer (eg, `apk add gcompat`).';
        }

        parent::__construct($message, $previous->getCode(), $previous);
    }
}
