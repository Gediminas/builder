<?php

//*1 Run(cmd_params)
//* -------------
//*2 cmd_params - Space separated list, e.g. Run(some.exe 'first_param' 'second_param')
//* -------------
//*3 Execute external application

// Random 2 empty parameters come, needs to be reviewed in cmd_params

require_once("../tools/run_process.php");

$TTL = $sys_params['TTL'];
$cmd = array_shift($cmd_params);
$arr_flags = explode(' ', $cmd);
$cmd = array_shift($arr_flags);
$cmd_params = array_slice($cmd_params, 0, count($arr_flags));

foreach(array_combine($cmd_params, $arr_flags) as $param => $flag) 
	if (0 < strlen($param)) {
	$cmd = $cmd . " $flag \"$param\"";
	}

run_process($cmd, $result, $command_log, $TTL);

if (0 != $result)
	_log_to($command_log, "WARNING: Process returned [$result] [$cmd]");
	
_log_to($command_log, "[DONE]");
?>