<?php

require_once("../core/send_mail.php");

function main_on_script_finish($sys_params)
{
	_log("------------------------------------------------------------------------", false);
	_log("main_on_script_finish():");
	foreach ($sys_params as $name => $value)
		_log("> $name = $value", false);

	$worker_id           = $sys_params['worker_id'];
	$job_id              = $sys_params['job_id'];
	$param_owner         = $sys_params['param_owner'];
	$product_id          = $sys_params['product_id'];
	$product_dir         = $sys_params['product_dir'];
	//$time_build_started  = $sys_params['time_started'];
	$time_build_finished = $sys_params['time_finished'];
	$duration            = $sys_params['duration'];
	$user_comment        = $sys_params['user_comment'];
	$mail_command_log    = $sys_params['mail_command_log'];
	$last_command_log    = $sys_params['last_command_log'];
	$halted              = get_param($param_owner, 'halt_user') || get_param($param_owner, 'halt');
	$logs                = array(get_daemon_log(), get_worker_log($worker_id), httpd_log(), php_log());
	
	foreach ($logs as $log) if (is_file("$log"))
		Copy("$log", "$product_dir/" . basename($log));

	//$bAT = get_autotester_data_by_build_started($time_build_started, $AT_id, $AT_product, $AT_branch, $AT_total_errors, $AT_tests_failed, $AT_tests_count);
	//$sys_params['AT_product'] = $bAT ? $AT_product : "$time_build_started";

	remove_job($job_id);
	
	if (!$halted)
		set_param('product_span', $product_id,     $duration);            //for index.php last duration
	
	set_param($param_owner,   'time_finished', $time_build_finished); //for daemon
	SetValue($product_dir, 'status',   $halted ? 'halted' : 'done');
	SetValue($product_dir, 'comment',  $user_comment);
	SetFlag($product_dir, "_{$product_id}_");

	$sent = send_mail_on_finish($sys_params, $mail_command_log);
	
	if (!$sent)
	{
		SetFlag($product_dir, 'warning');
	}
	
	_log_to($last_command_log, $halted ? "[HALTED]" : "[DONE]");  
	//_log('[FINISHED]', false);
	_log("------------------------------------------------------------------------", false);
}

?>