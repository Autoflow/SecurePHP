<?php

# SAPI: 3ce0e96689bc8ea521464bc85f1cf099
# CLI:


include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');

$mysecure->mute(0);

ini_set('max_execution_time', 1);
sleep(2);

$mysecure->eof();

// EOF