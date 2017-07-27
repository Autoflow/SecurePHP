<?php


include dirname(__FILE__) . '/../secure.php';
try
    {


    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);
    $mysecure->mute(false);

    $mysecure->config->from('Autoflow<securephp@autoflow.org>');
    $mysecure->config->admin('alex@autoflow.org');
    $mysecure->config->user('user@autoflow.org');
    $mysecure->config->add_cc('cc', 'cc@autoflow.org');

    $ticket = new \ErrorTicket('Database error', 'database not available');
    $ticket->send_to('admin>user,log');
    $ticket->raise();

    $ticket = new \Notice('Database update failed today', 'Retry in 30 minutes');
    $ticket->send_to('cc');
    $ticket->raise();

die();

    #$mysecure->debug(1);
    $mysecure->enabled(1);

    $mysecure->config->app('SecurePHP Testsuite');
    $mysecure->config->from('SecurePHP Testsuite <securephp@autoflow.org>');
    $mysecure->config->admin('securephp@autoflow.org');
    #$mysecure->config->user('operator@localhost');
    $mysecure->config->timeout( 30 );

    $mysecure->mute();
    #$mysecure->disable();

    #$e = new ErrorTicket('Testticket', 'Teststatus');
    #$e->raise();

    #ini_set('max_execution_time', 1);
    #sleep(2);

    #echo $x;

    #$e = new \SECUREPHP\E_UNCAUGHT('fataler Fehler', false);
    #$e1 = new Exception(1234, false, $e);
    #throw $e1;

    #$mysecure->end();
    }
catch(AUTOFLOW\SECUREPHP\E_INIT $e)
    {
    echo "INITFEHLER:" . $e;
    }
catch(AUTOFLOW\SECUREPHP\E_CONFIG $e)
    {
    echo "KONFIGURATIONSPROBLEM:" . $e;
    }
catch(EXCEPTION $e)
    {
    echo "LAUFZEITPROBLEM:" . $e;
    }
finally
    {
    #$mysecure->end();
    }

#$mysecure->config->set_from('securephp@autoflow.org');
#$mysecure->config->admin('securephp@autoflow.org');
#$mysecure->config->user('operator@localhost');
#$mysecure->config->add_user('manager', 'manager@localhost');
##$mysecure->config->set_timeout( 30 );



// EOF