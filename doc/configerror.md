## Example usage:

```php

try {

    // Start SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // Create PDO Object (try)
    $dbh = new PDO(PDO_DSN, PDO_USER, PDO_PASSWORD);
    }

catch(PDOException $e)
    {
    $report = new ConfigError("db error (PDOException)","connection to database failed.", $e);
    $report->set_config_file('<not available> or <path to config file>');
    $report->add_config_param('DSN', PDO_DSN);
    $report->add_config_param('User', PDO_USER);
    $report->add_config_param('Pass', PDO_PASS ? "****" : "not available");
    $report->raise();
    }

```