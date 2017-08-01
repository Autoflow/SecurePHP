## Example usage within cronjobs starting every 5 minutes:

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

    // bundle and catch recurring errors every 30 minutes and
    // send them by Reminder report to admins email inbox.
    $mysecure->config->reminder(30 * 60);

     // try to create pdo object
     $db = new PDO(PDO_DSN, PDO_USER, PDO_PASSWORD);

    }

catch(PDOException $e)

    {

    // prevent from eof errors
    $mysecure->eof();

    // create error report
    $report = new ConfigError("connection to database failed", "script terminated on database error", $e);

    // ConfigError is now blocked.
    // Reminder will be send every 30 minutes
    // if error raises in meantime again.
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
* error ticket within C:\Bitnami\apache2\htdocs\GitHub\SecurePHP\2.0\tests\testcase.php, line 17
* description: connection to database failed
*
* current state: script terminated on database error
*
*/
```

### Individual timeouts

Timeout can be defined on ticket/report basis:

```php
$report = new ErrorTicket("...", "...");
$report->raise(int $timeout);
```

### Individual reminders

Reminder can be defined on ticket/report basis:

```php
$report = new ErrorTicket("...", "...");
$report->raise(int $timeout, int $reminder);
```