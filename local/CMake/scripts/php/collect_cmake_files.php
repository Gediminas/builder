<?php

require_once("collect_paths.php");

$build_cfg     = $argv[1];
$commands_txt  = $argv[2];

CollectPaths($build_cfg, $commands_txt);

?>