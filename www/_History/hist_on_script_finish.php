<?php

function hist_on_script_finish($sys_params)
{
	_log("------------------------------------------------------------------------", false);
	_log("hist_on_script_finish():");
	
	$job_id        = $sys_params['job_id'];
	$product_id    = $sys_params['product_id'];
	$time_started  = $sys_params['time_started'];
	$time_finished = $sys_params['time_finished'];
	$duration      = $sys_params['duration'];
	$product_dir   = $sys_params['product_dir'];
	$user_comment  = $sys_params['user_comment'];
	$param_owner   = $sys_params['param_owner'];
	$time_AT       = $sys_params['time_AT'];
	$build_nr      = $sys_params['build_nr'];
	$distr_path    = $sys_params['distr_path'];
	$error_status  = GetFlag($product_dir, 'error')   ? 1 : (GetFlag("$product_dir", 'warning') ? 2 : 0);
	$build_status  = GetValue($product_dir, 'status');
	$halt_status   = get_param($param_owner, 'halt_user') || get_param($param_owner, 'halt');
	$mixed_status  = $error_status;

	if ($halt_status)
		$build_status = 'halted';
		
	$build_status = strtolower($build_status);
	
	switch ($build_status)
	{
	case 'done':     break;
	case 'building': break;
	case 'halted':  $mixed_status += 10; break;
	case 'died':    $mixed_status += 20; break;
	default:        _log_warning("build_status=$build_status"); break;
	}

	_log("> halt_status  = [$halt_status]", false);
	_log("> error_status = [$error_status]", false);
	_log("> build_status = [$build_status]", false);
	_log("> mixed_status = [$mixed_status]", false);
	
	if (!product_is_debug($product_id))
	{
		require_once("builder_db_history_fnc.php");
		add_history($job_id, $product_id, $time_started, $time_finished, $time_AT, $duration, $build_nr, $mixed_status, $distr_path, $user_comment);
	}

	_log("------------------------------------------------------------------------", false);
}

?>