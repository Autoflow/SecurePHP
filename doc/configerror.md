## Example usage:

```php

try {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display errors
    $mysecure->mute(false);

    // try to create pdo object
    $dbh = new PDO(PDO_DSN, PDO_USER, PDO_PASSWORD);

    }

catch(PDOException $e)

    {

    // create error report
    $report = new ConfigError("database error (PDOException)","connection to database failed.", $e);
    $report->set_config_file('<not available> or <path to config file>');
    $report->add_config_param('DSN', PDO_DSN);
    $report->add_config_param('User', PDO_USER);
    $report->add_config_param('Pass', PDO_PASS ? "****" : "not available");
    $report->raise();

    }

```

### Result:

```text
/**
* SecurePHP
*
* ConfigError
* [27-Jul-2017 15:27:27]
*
* concerned application: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* description: pdo database error
*
* current state: could not connect to database
*
* config notes:
* maybe outdated configuration settings
*
* path to config file: pdo_settings.php
*
* current configuration:
*
* 1) DSN: PDO_DSN
* 2) User: PDO_USER
* 3) Pass: ****

* previously:
*
* PDOException within C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 15
* description: invalid data source name
*
* trace:
* 0 C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php (15) PDO->__construct('PDO_DSN', 'PDO_USER', 'PDO_PASSWO..')
* {main}
*
*/
```