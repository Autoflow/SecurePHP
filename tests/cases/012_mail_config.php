<?php

# SAPI: d41d8cd98f00b204e9800998ecf8427e
# CLI:



include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');
$mysecure->mute(0);

$mysecure->config->from('alex@autoflow.org');
$mysecure->config->admin('alex@autoflow.org');
$mysecure->config->user('securephp@autoflow.org');
$mysecure->config->add_cc('alex', 'alex@autoflow.org');

$mysecure->eof();

// EOF