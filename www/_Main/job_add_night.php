<?php
$all        = isset($_GET['all'])      ? $_GET['all']      : 0;
$bcomment   = isset($_GET['bcomment'])   ? $_GET['bcomment']   : false;
echo header("Refresh: 5; url=../_Main/index.php?all=$all&bcomment=" . rawurlencode($bcomment));
echo "COMMENT: $bcomment<br/>\n";
echo "PRODUCT: Night build pack<br/>\n";

require_once("../tools/night_buils_fnc.php");

verify_temp_folder();
add_night_builds($bcomment);

echo "<center><h1>Builds added </h1></center><br/>\n";

if (!debug('manual_proc_start'))
	StartDaemon();
?>