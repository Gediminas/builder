<?php

require_once("build.php");

$ide_version = $argv[1];
$build_info  = $argv[2];
$rebuild     = $argv[3];
$result_file = $argv[4];

build($ide_version, $build_info, $rebuild, $result_file);

?>