<?php

# SAPI: 683c550b73809773c6f6f215da47bd6e
# CLI:


include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');

$mysecure->debug(1);
$mysecure->mute(0);

ini_set('memory_limit', 1);
phpinfo();

$mysecure->eof();

// EOF