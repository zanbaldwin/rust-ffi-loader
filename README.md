# Rust FFI Loader for PHP

Create a new Rust library project:

```shell
cargo new --lib mything "<rust-project>"
cd "<rust-project>"
cargo add --dev cbindgen
cp "<rust-ffi-loader>/example/rust/build.rs" "<rust-project>/build.rs"
```

Add the following to your `Cargo.toml`:

```toml
[package]
build = "build.rs"

[lib]
crate-type = ["cdylib"]

[build-dependencies]
cbindgen = "0.26"
```

Build the Cargo project to generate both the C header file and shared library object.

```shell
# For example, if running inside a Linux container: x86_64-unknown-linux-gnu
cargo build --target "<target-triple-of-target-php-machine>"
```

Call the Rust library within PHP via FFI:

```php
use ZanBaldwin\FFI\AbstractLoader;

class MyThing extends AbstractLoader
{
    public function doAThingInRust(): string
    {
        return $this->ffi->doAThingInRust();
    }
}

$myThing = new MyThing('/path/to/your/cargo/project', 'crate_name');
$myThing->doAThingInRust();
```
