<html>
<head></head>
<body>
<?php


// SAPI TEST

define('EOL', "\n");

$path = dirname(__FILE__) . "/cases/";
$dir = "/cases/";

if(isset($_GET['action']) AND 'update' == $_GET['action'])
    {
    $file = $_GET['file'];
    $md5 = $_GET['md5'];

    $f = file_get_contents($path . $file);
    $f = preg_replace("/SAPI: ([a-z0-9]*)/", 'SAPI: ' . $md5, $f);
    file_put_contents($path . $file, $f);
    }

$files = scandir($path);
$files = count($files);

$c = 0;
$d = dir($path);
echo "\nTeststart ...";

echo "<table>";
while (false !== ($entry = $d->read())) {


    if(!strpos($entry, '.')) { $files--; continue; }
    if('.sqlite' == substr($entry, -7)) { $files--; continue; }
    if('.log.txt' == substr($entry, -8)) { $files--; continue; }

    $c++;

    echo "<tr>";


    $f = file_get_contents($path . $entry);
    preg_match("/SAPI: ([a-z0-9]*)/",$f, $m);

    #ob_start();
    #require $path .  $entry;
    #$output = ob_get_clean();

    $url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $p = pathinfo($url);

    $ch = curl_init('http://' .$p['dirname'] . DIRECTORY_SEPARATOR . 'cases' . DIRECTORY_SEPARATOR . $entry);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);

    if($m[1] != $md5 = md5($output))
        {
        echo '<td><b style="color:red">Fehler</b>:</td>';
        $links = '<td>[<a href="'. $_SERVER["PHP_SELF"] .'?action=update&file='.$entry.'&md5='.$md5.'">übernehmen</a>] [<a href="./'.$dir . $entry.'" target="_new">öffnen</a>]</td>';
        }
    else
        {
        echo '<td><b style="color:green">OK</b>: </td>';
        $links = '<td> [<a href="'.$_SERVER["PHP_SELF"] .'" target="_new">öffnen</a>]</td>';
        #$links = "<td></td>";
        }

    if($m[1] != md5($output))
        {
        echo '<td>' . $entry . "</td><td>($m[1], $md5)</td>" . $links;
        }
    else
        {
        echo '<td>' . $entry . '</td>' . $links;
        }
echo "</tr>";

    }
echo "</table>";
echo EOL . "Ergebnis: $c Tests von $files beendet.\n.";
$d->close();

// EOF ?>
</body>
</html>