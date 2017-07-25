<?php

ini_set('display_errors', 1);

ini_set('error_log', "tests.log.txt");

ini_set('log_errors', 1);

# SAPI: d22b999b5c2bea03e1ba1c647191fd51
# CLI:

include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');
$mysecure->debug(1);
$mysecure->mute(0);
throw new Exception('mute test');


// EOF