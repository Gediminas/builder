<?php

//*1 Decompress(archive, dst)
//* -------------
//*2 archive - result archive file
//*2 dst - folder to decompress
//* -------------
//*3 Decompress archive to folder using 7-zip

$archive = $cmd_params[0];
$dst     = $cmd_params[1];
$TTL     = $sys_params['TTL'];

require_once("../tools/run_process.php");


$run = "_cmd_7z"; //realpath("../../cmd/_cmd_7z");
$exe = realpath("../cmd/7za.exe");
$cmd = "$run /c \"$exe e -o\"$dst\" \"$archive\" -y";

run_process($cmd, $result, $command_log, $TTL);

?>