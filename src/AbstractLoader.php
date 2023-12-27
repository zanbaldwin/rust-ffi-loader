<?php declare(strict_types=1);

namespace ZanBaldwin\FFI;

abstract class AbstractLoader
{
    private static bool $musl;
    protected readonly string $rustProjectLocation;
    protected readonly string $sharedLibraryObject;
    protected readonly \FFI $ffi;

    /**
     * Override this method if the library header file is in a non-standard
     * location. Unlike the shared library object, the header filename is
     * determined by whatever string is used inside the `build.rs` file.
     */
    protected function getLibraryHeaderFilepath(): string
    {
        return sprintf('%s/lib%s.h', $this->rustProjectLocation, $this->crateName);
    }

    /**
     * @throws \ZanBaldwin\FFI\Exception\InvalidRustProjectException
     * @throws \ZanBaldwin\FFI\Exception\MissingHeaderFileException
     * @throws \ZanBaldwin\FFI\Exception\MissingSharedLibraryObjectException
     * @throws \ZanBaldwin\FFI\Exception\UnknownPlatformExtensionException
     * @throws \ZanBaldwin\FFI\Exception\MissingExtensionException
     */
    public function __construct(
        string $rustProjectLocation,
        public readonly string $crateName,
        public readonly bool $debug = false,
    ) {
        // @phpstan-ignore-next-line Want to make sure that the FFI extension is loaded, and not some user-land class of the same name.
        if (!class_exists(\FFI::class) || !method_exists(\FFI::class, 'cdef')) {
            throw new Exception\MissingExtensionException;
        }
        $this->rustProjectLocation = self::validateRustProjectLocation($rustProjectLocation);

        $libraryHeaderFilepath = $this->getLibraryHeaderFilepath();
        if (!file_exists($libraryHeaderFilepath) || !is_readable($libraryHeaderFilepath)) {
            throw Exception\MissingHeaderFileException::file($this->rustProjectLocation, $this->crateName);
        }

        self::$musl = Util::isAlpine() || Util::hasMusl();
        $this->sharedLibraryObject = $this->guessSharedLibraryLocation();
        $headerDefinition = file_get_contents($libraryHeaderFilepath) ?: throw Exception\MissingHeaderFileException::read(
            $this->rustProjectLocation,
            $this->crateName,
        );

        try {
            $this->ffi = \FFI::cdef($headerDefinition, $this->sharedLibraryObject);
        } catch (\FFI\Exception $e) {
            throw new Exception\LibraryLoadException($this->sharedLibraryObject, $e);
        }
    }

    /** @throws \ZanBaldwin\FFI\Exception\InvalidRustProjectException */
    protected static function validateRustProjectLocation(string $suppliedLocation): string
    {
        $rustProjectLocation = realpath($suppliedLocation);
        if (false === $rustProjectLocation || !is_dir($rustProjectLocation) || !is_readable($rustProjectLocation)) {
            throw Exception\InvalidRustProjectException::dir($suppliedLocation);
        }
        if (!file_exists($rustProjectLocation . '/Cargo.toml')) {
            throw Exception\InvalidRustProjectException::rust($suppliedLocation);
        }
        return $rustProjectLocation;
    }

    /**
     * @throws \ZanBaldwin\FFI\Exception\MissingSharedLibraryObjectException
     * @throws \ZanBaldwin\FFI\Exception\UnknownPlatformExtensionException
     */
    protected function guessSharedLibraryLocation(): string
    {
        $checked = [];
        $buildProfile = $this->debug ? 'debug' : 'release';
        $platformExtension = self::platformExtension();
        $customTargetDir = self::getCustomTargetDir();
        $targetTriple = self::guessTargetTriple();

        $shouldCheck = [
            $customTargetDir . '/%2$s/%3$s/lib%4$s.%5$s' => $customTargetDir !== null && $targetTriple !== null,
            // Always check custom target directory first, because that's likely
            // where Cargo built to.
            $customTargetDir . '/%3$s/lib%4$s.%5$s' => $customTargetDir !== null,
            // Always check the default target directory for the target triple that
            // PHP needs (rather than the default target triple that Cargo builds for).
            '%1$s/target/%2$s/%3$s/lib%4$s.%5$s' => $targetTriple !== null,
            '%1$s/../target/%2$s/%3$s/lib%4$s.%5$s' => $targetTriple !== null,
            // Then finally check the default target directory for the default
            // target triple that Cargo builds for.
            '%1$s/target/%3$s/lib%4$s.%5$s' => true,
            '%1$s/../target/%3$s/lib%4$s.%5$s' => true,
        ];

        foreach (array_keys(array_filter($shouldCheck)) as $possibleLibraryFilepath) {
            $checked[] = $libraryPath = sprintf(
                $possibleLibraryFilepath,
                $this->rustProjectLocation,
                $targetTriple,
                $buildProfile,
                $this->crateName,
                $platformExtension,
            );
            if (file_exists($libraryPath) && is_file($libraryPath) && is_readable($libraryPath)) {
                return $libraryPath;
            }
        }

        throw new Exception\MissingSharedLibraryObjectException($checked, $this->crateName);
    }

    protected static function getCustomTargetDir(): ?string
    {
        $customTargetDir = $_ENV['CARGO_TARGET_DIR'] ?? $_SERVER['CARGO_TARGET_DIR'] ?? getenv('CARGO_TARGET_DIR') ?: '';
        if (!is_string($customTargetDir) || '' === $customTargetDir || false === $customTargetDir = realpath($customTargetDir)) {
            return null;
        }
        return is_dir($customTargetDir) && is_readable($customTargetDir)
            ? $customTargetDir
            : null;
    }

    /**
     * It's quite likely that the Rust project will need to be built using a
     * different target triple than their default (eg, a developer uses macOS
     * as their operating system, but the PHP project runs on Linux inside a
     * Docker container).
     *
     * Only support the three main operating-systems (Windows, macOS, Linux) for
     * now on the popular architectures (Intel 32-bit, AMD 64-bit, and ARM 64-bit).
     */
    protected static function guessTargetTriple(): ?string
    {
        if (!defined('\PHP_OS_FAMILY')) {
            return null;
        }

        $arch = match (php_uname('m')) {
            'aarch64', 'aarch64_be', 'arm64' => 'aarch64',
            'x86_64', 'amd64' => 'x86_64',
            'i386', 'i686' => 'i686',
            default => null,
        };
        if (null === $arch) {
            return null;
        }

        return match (\PHP_OS_FAMILY) {
            // Do we choose MSVC or GNU for Windows? MSVC is the default for
            // `cargo build` on Windows, so we'll go for that and assume that if
            // they've built inside a container then they'll build for Linux anyway.
            'Windows' => sprintf('%s-pc-windows-msvc', $arch),
            'Darwin' => sprintf('%s-apple-darwin', $arch),
            // Dynamically-linked shared libraries can't run on Alpine/Musl based
            // systems, so always choose GNU and assume that the system has a
            // GLibC-compatibility layer installed (`apk add gcompat` on Alpine Linux).
            'Linux' => sprintf(
                '%s-unknown-linux-%s',
                $arch,
                self::$musl ? 'musl' : 'gnu',
            ),
            default => null,
        };
    }

    /** @throws \ZanBaldwin\FFI\Exception\UnknownPlatformExtensionException */
    protected static function platformExtension(): string
    {
        if (defined('\PHP_OS_FAMILY')) {
            // If you're running a version of PHP built for a different
            // operating system than it's currently running on then you have
            // bigger problems that trying to auto-detect a file extension.
            return match (\PHP_OS_FAMILY) {
                'Windows' => 'dll',
                'Darwin' => 'dylib',
                'Linux' => self::$musl ? 'a' : 'so',
                default => defined('\PHP_SHLIB_SUFFIX')
                    ? \PHP_SHLIB_SUFFIX
                    : new Exception\UnknownPlatformExtensionException,
            };
        }
        throw new Exception\UnknownPlatformExtensionException;
    }
}
