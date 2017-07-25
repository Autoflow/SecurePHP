<?php

// TODO:
// 1) Wenn @, dann keine Fehlermeldung über den tatsächlichen Fehler
// 2) function send for mail merge
// 3) Trace in Mail
// 4) Verschiebe mute(), enabled(), disabled() etc. nach Config

include dirname(__FILE__) . '/../secure.php';
try
    {

    $mysecure = AUTOFLOWSECUREPHP\BOOTSTRAP::getInstance(true, false);


    $mysecure->mute(false);
    #$mysecure->debug(1);
    #$mysecure->debug(0);
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
catch(AUTOFLOWSECUREPHP\E_INIT $e)
    {
    echo "INITFEHLER:" . $e;
    }
catch(AUTOFLOWSECUREPHP\E_CONFIG $e)
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