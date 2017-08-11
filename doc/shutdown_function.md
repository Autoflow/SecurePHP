## Example usage:

```php

try {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // create a new report
    $errors = new BatchReport('processed files');

    // provide a shutdown function (crash report)
    $mysecure->config->shutdown_function = function() use ($errors)
        {
        // clear caches ie.
        $errors->set_state('the application crashed due to an error');
        $errors->raise();
        };

    // some application crash
    throw new Exception('application crashed');

    // will never be reached
    $errors->raise();

    }

```

### Result:

```text
/**
* SecurePHP
*
* BatchReport
* [01-Aug-2017 13:53:39]
*
* send by: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* description: processed files
*
* current state: the application crashed due to an error
*
*/
```