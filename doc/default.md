### When starting the library PHP will be set to a silent and more sensitive mode.

The PHP environment will be set as follows:

* [strict mode](doc/strict.md)
    - E_NOTICEs are turned to E_ERRORs and script will be halted
    - E_WARNINGs will be turned to E_ERRORs and script will be halted

* [mute mode](doc/mute.md)
    - display errors will be deactivated for CLI and SAPI environment

This settings can be easily changed to a less tight handling.
See the docs for [strict mode](strict.md) and [mute mode](mute.md).