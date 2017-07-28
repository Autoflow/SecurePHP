## Example usage:

```php

try
    {

    // start Autoflow\SecurePHP
    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);

    // display report
    $mysecure->mute(false);

    // Create a new batch report
    $batchreport = new \BatchReport('batch report');

    // load demo data
    if ($dh = opendir($dir = 'cases/'))
        {

        // some counters for later use
        $succeded = 0;
        $failed = 0;

        // filter accepted php files
        while (($file = readdir($dh)) !== false)
            {

            if('php' != pathinfo($file, PATHINFO_EXTENSION))
                {

                // Create an error ticket
                $batchfile = new \ErrorTicket('current file ' . $file . ' was not accepted');
                $batchfile->set_state('rejected');
                $batchfile->set_note('operation on file aborted');
                $failed++;

                }
            else
                {

                // Create an success ticket
                $batchfile = new \SuccessTicket($file);
                $batchfile->set_note('accepted file format');
                $batchfile->set_state('operation succeded');
                $succeded++;

                }

            // Disable trace and debug data
            $batchfile->details(false);

            // Add ticket to report
            $batchreport->add($batchfile);

            }
        closedir($dh);
        }

    // Set summary
    $batchreport->set_state("Operation succeded on {$succeded} files, {$failed} failed with errors");

    // Send report to mail inbox, log or display
    $batchreport->raise();


```

### Result:

```text
/**
* SecurePHP
*
* BatchReport
* [27-Jul-2017 17:27:22]
*
* send by: C:/Bitnami/apache2/htdocs/GitHub/SecurePHP/2.0/tests/testcase.php
*
* description: batch report
*
* current state: Operation succeded on 12 files, 3 failed with errors
*
* table of contents:
*
*
* 1) ErrorTicket
*
* description: current file . was not accepted
*
* notes: operation on file aborted
*
* current state: rejected
*
*
* 2) ErrorTicket
*
* description: current file .. was not accepted
*
* notes: operation on file aborted
*
* current state: rejected
*
*
* 3) SuccessTicket
*
* description: 001_include.php
*
* notes: accepted file format
*
* current state: operation succeded
*
*
* 4) SuccessTicket
*
* description: 001_include_not_mute.php
*
* notes: accepted file format
*
* current state: operation succeded
*

[....]

*
* 14)SuccessTicket
*
* description: 012_mail_config.php
*
* notes: accepted file format
*
* current state: operation succeded
*
*
* 15)ErrorTicket
*
* description: current file tests.log.txt was not accepted
*
* notes: operation on file aborted
*
* current state: rejected
*
*
*/
```