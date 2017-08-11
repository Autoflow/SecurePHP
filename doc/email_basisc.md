## Example usage:

```php

try {

    // init Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    //
    // basic email configuration
    //

    // set your senders adress if not done so within php.ini
    $mysecure->config->from('Autoflow <securephp@autoflow.org>');

    // set your systems administrators adress
    $mysecure->config->admins('admin@yourdomain');

    // set your users adress
    $mysecure->config->users('user@yourdomain');

    // set further receipients
    $mysecure->config->cc('cc1', 'cc@yourdomain');
    $mysecure->config->cc('cc2', 'cc@yourdomain');

    // now you are ready to create and send reports to email inboxes
    $ticket = new \ErrorTicket('Database error', 'database not available');

    // try sending this report to admin first, on failure try sending to user
    // report will be logged to log file additionally
    $ticket->send_to('admin>user,log');
    $ticket->raise();

    // send ticket to all cc's additionally
    $ticket = new \Notice('Database update failed today', 'Retry in 30 minutes');
    $ticket->send_to('cc');
    $ticket->raise();

    }

    [...]

```

### Result:

```text

Emails should be send now [...]

```