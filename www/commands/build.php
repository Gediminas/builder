<?php

//*1 Build(ide, build_info)
//* -------------
//*2 ide        - build environment, must exist file - /local/php/ide_<ide>.php
//*2 build_info - 
//* -------------
//*3 Build project

$ide        = $cmd_params[0];
$build_info = $cmd_params[1];
$TTL        = $sys_params['TTL'];

require_once("../../local/php/build.php");
require_once("../tools/run_process.php");
	
$rebuild = 1;
$build_cmds = get_build_commands("$ide", "$build_info", "$rebuild", NULL);

foreach($build_cmds as $cmd) if (0 < strlen($cmd))
{
	$cmd = str_replace('%COMSPEC%', '_cmd_build', $cmd);
	run_process($cmd, $result, $command_log, $TTL);
}

?>