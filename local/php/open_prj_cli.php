<?php

$ide_version = $argv[1];
$prj_path    = $argv[2];

require_once("$ide_version.php");

$cmd = GetOpenPrjCommand($prj_path);
`$cmd`

?>