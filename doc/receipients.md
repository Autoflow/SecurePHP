### Ticket receipients

Ticket/report receipients can easily be defined by send_to()-method.
Receipients can be defined as is or by receipients list devided by ">" character.
On notification error the next receipient will get informed until ticket or report
is successfully delivered.

### Examples:

Define a mail receipient order, write to log independent off.

```php
$report = new ErrorTicket("...", "...");
$report->send_to('admin>user>cc,log');
$report->raise();
```

No mail receipient order, inform all independent off.

```php
$report = new ErrorTicket("...", "...");
$report->send_to('user, admin, log');
$report->raise();
```

Inform all channels:

```php
$report = new ErrorTicket("...", "...");
$report->send_to('all');
$report->raise();
```