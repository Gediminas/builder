<?php

//*1 BuildCfg($build_cfg_file, $build_configs)
//* -------------
//*2 build_cfg_file - contains (\n separated) relative (from src root) paths to build projects, e.g. build/product1.cfg
//*2 build_configs - comma separated list of configs, e.g. 'debug,static' will build 'Debug', 'DLL Debug', 'Static_release', etc
//* -------------
//*3 Collect build projects+configurations

$build_cfg_file = $cmd_params[0];
$build_configs  = $cmd_params[1];

if( isset($cmd_params[2]) ) {
	$platform = $cmd_params[2];
}
else {
	$platform = "";
}

//$prj_extension  = empty($cmd_params[2]) ? 'dsp' : $cmd_params[2]; //not needed?

require_once("../../local/php/collect_builds.php");
require_once("../core/run_command_block.php");

$worker_id        = $sys_params['worker_id'];
$src_dir          = $sys_params['source_dir'];
$ide              = isset($sys_params['ide']) ? $sys_params['ide'] : false;
$build_list       = get_worker_tmp_build_list_path($worker_id);
$sub_script_lines = Array();

if (!$ide)
	$ide = "ide_vc6";   //default ide version, can be overriden by SetEnv(),
										//local\php\ide_*.php must exist

if (CollectBuilds("$ide", "$src_dir/$build_cfg_file", "$build_configs", "$build_list"))
{
	$file = file($build_list);
	$count = 0;

	foreach($file as $build_info)
	{
		$build_info = trim($build_info);
		if( strpos($platform, 'x64') !== false ) {
			$build_info .= " * x64";
		}
		$cmd = 'Build("' . $ide . '","' . $build_info . '")';
		$sub_script_lines[++$count] = $cmd;
	}
}

$sub_script_line_count = count($sub_script_lines);

_log_reset($command_log);
_log_to($command_log, "$cmd_line_full [$sub_script_line_count]");

if (!$sub_script_line_count)
{
	_log_to($command_log, "ide: $ide");
	_log_to($command_log, "ERROR: No configurations found!. Halting...");
	set_param("worker$worker_id", 'halt', 1);
	return;
}

foreach ($sub_script_lines as $sub_cmd)
	_log_to($command_log, $sub_cmd, false);

_log_to($command_log, "");
_log_to($command_log, "Running CFG commands");


$old_ignore_halt = $sys_params['ignore_halt'];
$sys_params['ignore_halt'] = 1;

run_command_block($cmd_nr, $sub_script_lines, $sys_params);

$sys_params['ignore_halt'] = $old_ignore_halt;

?>