<?php declare(strict_types=1);

namespace ZanBaldwin\FFI;

class Util
{
    public static function isAlpine(): bool
    {
        if (file_exists('/etc/os-release') && is_readable('/etc/os-release')) {
            return str_contains(strtolower(file_get_contents('/etc/os-release') ?: ''), 'alpine');
        }
        return false;
    }

    public static function hasMusl(): bool
    {
        return function_exists('shell_exec') && (
            str_contains(strtolower(`ldd --version`), 'musl')
            || str_contains(strtolower(`getconf GNU_LIBC_VERSION`), 'unknown variable')
        );
    }
}
