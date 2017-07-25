<?php

# SAPI: fc05d8454db228ce4443bc262363f5fb
# CLI:

include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');

$mysecure->debug(1);

throw new Exception('debug test');

// EOF