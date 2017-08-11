<?php

/*
 * This file is part of the SecurePHP package.
 *
 * (c) Alexander Münch <alex@autoflow.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__) . '/../secure.php';
try
    {


    $mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance(true, false);
    $mysecure->mute(false);

    #$mysecure->debug(1);

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

// EOF