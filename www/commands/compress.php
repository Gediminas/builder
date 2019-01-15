<?php

//*1 Compress(src, archive)
//* -------------
//*2 src - folder to compress
//*2 archive - result archive file
//* -------------
//*3 Compress folder to archive using 7-zip

$src     = $cmd_params[0];
$archive = $cmd_params[1];
$TTL     = $sys_params['TTL'];

require_once("../tools/run_process.php");


$run = "_cmd_7z"; //realpath("../../cmd/_cmd_7z");
$exe = realpath("../cmd/7za.exe");
$cmd = "$run /c \"$exe a -t7z -mx9 -mmt \"$archive\" \"$src\" -y";

run_process($cmd, $result, $command_log, $TTL);

?>