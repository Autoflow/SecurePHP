<?php

# SAPI: d41d8cd98f00b204e9800998ecf8427e
# CLI:

if(function_exists('xdebug_disable')) { xdebug_disable(); }

include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');

$mysecure->mute(0);


@$x;

$mysecure->eof();

// EOF