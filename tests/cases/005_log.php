<?php

# SAPI: f5e5cc14d4f6603d2fd1dbecd29bcc80
# CLI:



include_once(dirname(__FILE__) . '/../functiontests.inc.php');
$mysecure = AUTOFLOW\SECUREPHP\BOOTSTRAP::getInstance($logfile, false);
$mysecure->config->locale('T');
$mysecure->mute(0);

if(!file_exists($logfile)) throw new \Exception('Logdatei existiert nicht');

$handle = fopen($logfile, "w"); //w Überschreibt die Datei
fclose($handle);

$mysecure->config->shutdown_function= function() use ($mysecure, $logfile)
    {

    $file = file($logfile);
    unset($file[0]);
    var_dump($file);

    $mysecure->eof();
    };


echo $y;



// EOF