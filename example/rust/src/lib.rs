use std::ffi::{c_char, CStr, CString};

/// This is the function we want to import into PHP via FFI.
///
/// # Safety
/// This function is unsafe because it takes a raw pointer as an argument.
#[no_mangle]
pub unsafe extern "C" fn fill(input: *const c_char) -> *const c_char {
    let input = unsafe { CStr::from_ptr(input) }.to_str().unwrap();

    let count = input.parse::<usize>().unwrap();
    let mut vec = Vec::new();
    for i in 0..count {
        vec.push(i);
    }

    let json = serde_json::to_string(&vec).unwrap();
    CString::new(json).unwrap().into_raw()
}
