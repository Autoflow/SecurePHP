## Example usage:

```php

try {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

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