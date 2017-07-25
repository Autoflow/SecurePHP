<?php

# SAPI: b7de49cbff2f4a28c142a4a9f0ff337e
# CLI:

if(function_exists('xdebug_disable')) { xdebug_disable(); }

include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');

$mysecure->enabled(0);

throw new Exception('disable test');

// EOF