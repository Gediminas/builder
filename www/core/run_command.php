<?php

require_once("../conf/conf_fnc.php");
require_once("../tools/check_errors.php");

function run_command($cmd_line_full, $cmd, $cmd_params, &$sys_params, &$cmd_nr)
{
	$time_started = $sys_params['time_started'];
	$command_log  = get_command_log($time_started, $cmd_nr++);
	$command      = str_replace(':', '_', $cmd);
	$command      = str_replace('~', '',  $command);
	$command      = "../commands/$command.php";
	
	
	_log(hr());
	_log($cmd_line_full);

	_log_reset("$command_log");
	_log_to($command_log, "$cmd_line_full");
	_log_to($command_log, "");
	_log_to($command_log, "Executing script command '$cmd'");
	_log_to($command_log, "");
	
	
	if (is_file($command))
	{
		echo $command;
		require($command);
	}
	else
	{
		$product_dir  = $sys_params['product_dir'];
		_log_to($command_log, "ERROR: $command not found...");
		SetFlag($product_dir, 'error');
	}
	
	_log(hr());
	
	$result = CheckErrors($command_log, $time_started);
	
	if (EErrorStatus::ERROR == $result)
	{
		$param_owner = $sys_params['param_owner'];
		set_param($param_owner, 'halt', 1);
		_log_to($command_log, "");
		_log_to($command_log, "SCRIPT WILL BE HALTED DUE TO ERRORS !!!");
		
		 if ($sys_params['ignore_halt'])
			_log_to($command_log, "(Currently 'ignore_halt' flag is set, script will be halted after unset)");
	}

	if (EErrorStatus::QUIT == $result)
	{
		_log_to($command_log, "");
		_log_to($command_log, "[EXITING]");
	}
	
	return $result;
}

?>