<?php

require_once("collect_builds.php");

$ide_version   = $argv[1];
$build_cfg     = $argv[2];
$config_list   = $argv[3];
$commands_txt  = $argv[4];

CollectBuilds($ide_version, $build_cfg, $config_list, $commands_txt);

?>