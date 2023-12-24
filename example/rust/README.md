# Bugs

There is a known bug in PHP where an active Zend test observer (such as XDebug)
causes FFI to fail initialization. This was [fixed in commit c727f29][bugfix],
which appears in versions `8.2.14+` and `8.3.1+`. It does not appear to affect
versions `8.1.x` and below.

[bugfix]: https://github.com/php/php-src/commit/c727f2994257ebae17d992808b334d95c95de2f1 "Fix GH-12905: FFI::new interacts badly with observers"
