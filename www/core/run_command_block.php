<?php

require_once("run_batch.php");
require_once("../db/builder_db_params_fnc.php");

function ParseCommand($cmd_line, &$cmd_type, &$cmd, &$params)
{
	$cmd_line = trim($cmd_line);
	$cmd_type = NULL;
	$cmd      = NULL;
	$params   = Array();
	
	if (0 == strlen($cmd_line))
	{
		return false;
	}
	
	if ($cmd_line[0] == "#" ||
		$cmd_line[0] == "'" ||
		($cmd_line[0] == "/" && $cmd_line[1] == "/"))
	{
		$cmd_type = enum_cmd_type::comment;
	}
	else if ($cmd_line[0] == ">")
	{
		$cmd_type = enum_cmd_type::batch;
		$cmd      = substr($cmd_line, 1);
	}
	else
	{
		$cmd_type = enum_cmd_type::script;
		
		$tmp1 = explode("(",$cmd_line);
		$cmd  = strtolower(array_shift($tmp1));
		$tmp2 = implode("(", $tmp1);
		
		if(isset($tmp2))
		{
			$tmp3 = explode(")",$tmp2);
			array_pop($tmp3);
			$tmp4 = implode(")", $tmp3);

			$paramtmp = explode('"', $tmp4);
		}
	
		$params[0] = isset($paramtmp[1])  ? trim($paramtmp[1])  : NULL;
		$params[1] = isset($paramtmp[3])  ? trim($paramtmp[3])  : NULL;
		$params[2] = isset($paramtmp[5])  ? trim($paramtmp[5])  : NULL;
		$params[3] = isset($paramtmp[7])  ? trim($paramtmp[7])  : NULL;
		$params[4] = isset($paramtmp[9])  ? trim($paramtmp[9])  : NULL;
		$params[5] = isset($paramtmp[11]) ? trim($paramtmp[11]) : NULL;
		$params[6] = isset($paramtmp[13]) ? trim($paramtmp[13]) : NULL;
		$params[7] = isset($paramtmp[15]) ? trim($paramtmp[15]) : NULL;
	}
	
	$cmd = trim($cmd);
	return true;
}

function increment_param($owner, $param, $count=1)
{
	$value = get_param($owner, $param);
	$value += $count;
	set_param($owner, $param, $value);
}

function run_command_block(&$cmd_nr, $script_lines, &$sys_params)
{
	_log("COMMAND BLOCK BEGIN");

	$worker_id    = $sys_params['worker_id'];
	$product_id   = $sys_params['product_id'];
	$param_owner  = $sys_params['param_owner'];
	$time_started = $sys_params['time_started'];
	$batch        = new CRunBatch($sys_params);
	$sub_log_nr   = 0;
	
	increment_param($param_owner, 'ln_count', count($script_lines));
	
	foreach ($script_lines as $full_cmd_line)
	{
		if (get_param($param_owner, 'halt') && !$sys_params['ignore_halt'])
			break;
			
		if (get_param($param_owner, 'halt_user'))
			break;
			
		increment_param($param_owner, 'ln_curr');

		//FIXME: workaround
		if (ParseCommand($full_cmd_line, $cmd_type, $cmd, $cmd_params) && $cmd_type == enum_cmd_type::script)
			$sub_log_nr = 0;
		
		$full_cmd_line = apply_const($full_cmd_line, $sys_params, $cmd_nr, $sub_log_nr);
		
		if (!ParseCommand($full_cmd_line, $cmd_type, $cmd, $cmd_params))
		{
			if ($batch->Flush($cmd_nr))
				$sub_log_nr = 0;
			continue;
		}

		switch($cmd_type)
		{
		case enum_cmd_type::comment:
			break;
				
		case enum_cmd_type::batch:
			$batch->Add($cmd);
			break;
				
		case enum_cmd_type::script:
			if ($batch->Flush($cmd_nr))
				$sub_log_nr = 0;
			
			if (get_param($param_owner, 'halt') && !$sys_params['ignore_halt'])
				break;
			
			if (get_param($param_owner, 'halt_user'))
				break;
			
			if (EErrorStatus::QUIT == run_command($full_cmd_line, $cmd, $cmd_params, $sys_params, $cmd_nr))
				return -1;
				
			$batch->UpdateSysParams($sys_params); // workaround if prev. command changed TTL do update sys_params for the batch script
			
			break;
			
		default:
			break;
		}
	}
			
	$batch->Flush($cmd_nr);
		
	_log("COMMAND BLOCK END");
	return $cmd_nr;
}

?>