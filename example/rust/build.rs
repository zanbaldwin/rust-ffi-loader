extern crate cbindgen;

use cbindgen::Config;
use std::env;
use std::path::PathBuf;

fn main() {
    let crate_dir = env::var("CARGO_MANIFEST_DIR").unwrap();
    let package_name = env::var("CARGO_PKG_NAME").unwrap();
    let manifest_dir = env::var("CARGO_MANIFEST_DIR")
        .expect("Cargo to have set the CARGO_MANIFEST_DIR environment variable.");
    let output_file = PathBuf::from(manifest_dir)
        .join(format!("lib{package_name}.h"))
        .display()
        .to_string();

    println!("{output_file}");

    let mut config = Config::default();
    config.language = cbindgen::Language::C;

    cbindgen::Builder::new()
        .with_crate_and_name(crate_dir, package_name)
        .with_config(config)
        .generate()
        .expect("Unable to generate bindings.")
        .write_to_file(&output_file);
}
