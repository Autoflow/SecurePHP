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
    $mysecure->config->admin('admin@yourdomain');

    // set your users adress
    $mysecure->config->user('user@yourdomain');

    // set further receipients
    $mysecure->config->add_cc('cc', 'cc@yourdomain');

    // now you are ready to create and send reports to email inboxes
    $ticket = new \ErrorTicket('Database error', 'database not available');

    // try sending this report to admin first, on failure try sending to user
    // report will be logged to log file additionally
    $ticket->send_to('admin>user,log');
    $ticket->raise();

    // send some information to users additionally
    // this report will not be logged
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