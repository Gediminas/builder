<?php

function main_on_script_start(&$sys_params)
{
	if (!isset($sys_params['ignore_halt'])) $sys_params['ignore_halt'] = 0;
	if (!isset($sys_params['time_AT']))     $sys_params['time_AT']     = "";
	if (!isset($sys_params['build_nr']))    $sys_params['build_nr']    = "";
	if (!isset($sys_params['distr_path']))  $sys_params['distr_path']  = "";

	_log("------------------------------------------------------------------------", false);
	_log("main_on_script_start():");
	foreach ($sys_params as $name => $value)
		_log("> $name = $value", false);
	_log("------------------------------------------------------------------------", false);
	
	
	$time_started = $sys_params['time_started'];
	$product_id   = $sys_params['product_id'];
	$product_dir  = $sys_params['product_dir'];
	$user_comment = $sys_params['user_comment'];
	$param_owner  = $sys_params['param_owner'];

	remove_params($param_owner);
	set_param($param_owner, 'time_started',     $time_started);
	set_param($param_owner, 'product_id',       $product_id);     //for daemon
	set_param($param_owner, 'product_dir_time', $time_started);   //for daemon
	
	set_param("product",    "$product_id",      "$time_started"); //for index.php find last logs

	SetValue($product_dir, 'product_id', $product_id); //for daemon
	SetValue($product_dir, 'status',     'building');  //for daemon / index.php error status

	_log("------------------------------------------------------------------------", false);
}

?>