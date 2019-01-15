<?php

require_once("run_command.php");
require_once("../conf/conf_fnc.php");
require_once("../tools/file_tools.php");
require_once("../tools/builder_script_fnc.php");
require_once("../db/builder_db_params_fnc.php");
require_once("run_command_block.php");
require_once("../common/modules.php");


function apply_const($line, $sys_params, $cmd_nr, &$sub_log_nr)
{
	foreach ($sys_params as $key => $value)
	{
		$line = str_replace("@$key@", $value, $line);
	}
	
	$worker_id     = $sys_params['worker_id'];
	$product_id    = $sys_params['product_id'];
	$user_comment  = $sys_params['user_comment'];
	$time_started  = $sys_params['time_started'];
	$time_filename = time_to_filename($time_started);

	
	$src_dir         = $sys_params['source_dir'];
	$product_dir     = get_product_dir($time_started);
	$client_data_dir = client_data_dir();
	$date            = GetSysDate();
	$time            = GetSysTime();

	if (GetFlag("$product_dir", 'error'))       $errstatus = "Error";	
	elseif (GetFlag("$product_dir", 'warning')) $errstatus = "Warning";	
	else                                        $errstatus = "OK";	

	$line = str_replace("@root@",        $src_dir,          $line);
	$line = str_replace("@temp@",        $product_dir,      $line);
	$line = str_replace("@client_data@", $client_data_dir,  $line);
	
	
	$line = str_replace("@id@",        $product_id,         $line);
	$line = str_replace("@time@",      $time,               $line);
	$line = str_replace("@bcomment@",  $user_comment,       $line);
	$line = str_replace("@date@",      $date,               $line);
	$line = str_replace("@timestamp@", $time_filename, $line);
	$line = str_replace("@ATDB@",      autotester_db_path(), $line);
	
	$line = str_replace("@host@",      host(),              $line);
	$line = str_replace("@errstatus@", $errstatus,          $line);

	if (stripos('_' . $line, '@ip@'))
	{
		$line = str_replace("@ip@", get_ip(), $line);
	}
	
	if (stripos('_' . $line, '@maillist@') ||
		stripos('_' . $line, '@name@')      )
	{
		get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script);
		$product_name = str_replace("(", "", $product_name);
		$product_name = str_replace(")", "", $product_name);

		$line = str_replace("@maillist@", $product_mailto, $line);
		$line = str_replace("@name@",     $product_name,               $line);

	}

	if (stripos('_' . $line, '@log@'))
	{
		$cmd_sub_log = get_sub_log(get_command_log($time_started, $cmd_nr), $sub_log_nr ++);
		$line = str_replace("@log@", "$cmd_sub_log", $line);
	}
	
	return $line;
}



class enum_cmd_type
{
    const comment = 0;
	const batch   = 1;
    const script  = 2;
};
	
function create_product_dir(&$time_started, $debug)
{
	while (true)
	{
		$time_started = GetSysDateTime();
		
		if ($debug)
			$time_started = timestamp_add_debug_flag($time_started);
			
		$product_dir  = get_product_dir($time_started);
		$created      = check_create_dir($product_dir);
		
		if ($debug)
		{
		    $handle = opendir($product_dir);
			while($file = readdir($handle))
				if(is_file("$product_dir/$file"))
					unlink("$product_dir/$file");
		    closedir($handle);
			return "$product_dir";
		}
		
		if ($created)
			return "$product_dir";

		sleep(1);
	}
	
	assert(false);
	return false;
}
	
function run_product($worker_id, $product_id, $job_id, $user_comment)
{
	$sys_params                 = array();
	$sys_params['DEBUG']        = product_is_debug($product_id);
	$sys_params['worker_id']    = $worker_id;
	$sys_params['product_id']   = $product_id;
	$sys_params['job_id']       = $job_id;
	$sys_params['user_comment'] = $user_comment;
	$sys_params['param_owner']  = "worker$worker_id";
	$sys_params['source_dir']   = get_src_dir($product_id); //get_src_dir($worker_id, $sys_params['DEBUG']);
	$sys_params['product_dir']  = create_product_dir($time_started, $sys_params['DEBUG']);
	$sys_params['time_started'] = $time_started;
	$sys_params['worker_pid']   = getMyPid();
	$sys_params['worker_log']   = get_worker_log($worker_id);
	$sys_params['daemon_log']   = get_daemon_log();
	$sys_params['TTL']          = default_TTL();

	get_product_info($product_id, $sys_params['product_xml'], $sys_params['product_name'], $product_mutex, $sys_params['product_comment'], $product_enabled, $sys_params['product_night'], $sys_params['product_mailto'], $product_script);
	check_create_dir($sys_params['source_dir']);

	$product_script = str_replace("'", "\"", $product_script);
	$script_lines   = explode("\n", $product_script);
	$cmd_nr         = 0;
	
	foreach_call("../_*/*_on_script_start.php", $sys_params);

	run_command_block($cmd_nr, $script_lines, $sys_params);
	
	$sys_params['time_finished']    = GetSysDateTime();
	$sys_params['duration']         = strtotime($sys_params['time_finished']) - strtotime(timestamp_remove_debug_flag($sys_params['time_started']));
	$sys_params['mail_command_log'] = get_command_log($time_started, $cmd_nr++);
	$sys_params['last_command_log'] = get_command_log($time_started, $cmd_nr++);
	
	foreach_call("../_*/*_on_script_finish.php", $sys_params);
}

?>