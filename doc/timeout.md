## Example usage within cronjobs:

```php

try
    {

    // init Autoflow/SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // log errrors only
    $mysecure->mute(true);

    // define mail send options
    $mysecure->config->from('SecurePHP<securephp@yourdomain>');

    // define a admin error reports go to
    $mysecure->config->admin('admin@yourdomain');

    // cronjob starts every 5 minutes
    $mysecure->config->timeout(5 * 60);

    // catch recurring errors every 30 minutes only and
    // send them by ([TimerAlert](doc/timeralert.md)) to
    // admins email inbox.
    $mysecure->config->reminder(30 * 60);

     // try to create pdo object
     $db = new PDO(PDO_DSN, PDO_USER, PDO_PASSWORD);

    }
catch(PDOException $e)

    {

    // create error report
    $report = new ConfigError("database error","connection to database failed.", $e);

    // error report will now be send every 30 minutes
    $report->raise();

    }

```

### Result:

```console
/**
* SecurePHP
*
* TimerAlert
* [27-Jul-2017 20:31:18]
*
* send by: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* description: reminder
*
* current state: you will get notified every 30 minutes when same errors are still present
*
* table of contents:
*
* 1) ErrorTicket
*
* Fehlerticket within C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 17
* description: Database error
*
* current state: database not available
*
*/
```