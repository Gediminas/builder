<?php

echo header('Refresh: 1; url=../_System/index.php');

require_once("../conf/conf_fnc.php");
require_once("../tools/log.php");

$php_log = php_log();
_log_reset($php_log, $php_log);

?>

