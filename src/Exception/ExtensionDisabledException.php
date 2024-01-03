<?php declare(strict_types=1);

namespace ZanBaldwin\FFI\Exception;

class ExtensionDisabledException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(string $sharedLibraryObject, \FFI\Exception $previous)
    {
        $message = match (ini_get('ffi.enable')) {
            'false', false => 'The FFI extension is disabled on this PHP installation. Edit the `ffi.enable` PHP configuration setting to enable it.',
            'preload' => 'The FFI extension on this PHP installation only allows loading shared libraries in preloaded PHP scripts. Edit the `ffi.enable` PHP configuration setting to enable it.',
            // The FFI extension should allow loading, but it couldn't. Much be an unknown library load error.
            default => throw new LibraryLoadException($sharedLibraryObject, $previous),
        };
        parent::__construct($message, 0, $previous);
    }
}
