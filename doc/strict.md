### Strict mode is the [default](default.md) handling of php errors after initialising the SecurePHP libraray.

The enviroment is set to:

* strict mode
    - E_WARNINGs and E_NOTICEs are turned to E_ERRORs
    - script will be terminated


To go back to default PHP error handling run:

```php
$mysecure->strict(false);
```