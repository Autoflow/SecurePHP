This is the default mode after initialising the SecurePHP libraray.
The enviroment is set to
* mute
- no errors will be reported except to log and email.
* strict
- E_WARNINGs and E_NOTICEs are turned to E_ERRORs

To display errors or see them in console environment (CLI) disable [mute mode](doc/mute.md)
To go back to default PHP error handling unset run
```php
$mysecure->strict(false);
```