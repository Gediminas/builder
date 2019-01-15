<?php

//*1 CheckLng(lng_path, bin_path)
//* -------------
//*2 lng_path - language files *.lng folder
//*2 bin_path - binaries *.exe and *.dll folder
//* -------------
//*3 Check if language files used by exes and dlls, remove not used language files

$lng_path   = path_to_dos($cmd_params[0]);
$bin_path   = path_to_dos($cmd_params[1]);
$worker_id  = $sys_params['worker_id'];

require_once("../conf/conf_fnc.php");

$lng_list   = get_worker_tmp_lng_list_path($worker_id);
$exe_filter = "$bin_path\\*.exe";
$dll_filter = "$bin_path\\*.dll";
$lng_filter = "$lng_path\\*\\*.lng";

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> Language path: [$lng_path]", false);
_log_to($command_log, "> Binaries path: [$bin_path]", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> EXE filter: [$exe_filter]", false);
_log_to($command_log, "> DLL filter: [$dll_filter]", false);
_log_to($command_log, "> LNG filter: [$lng_filter]", false);
_log_to($command_log, "> LNG list:   [$lng_list]", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "");

$exes       = glob("$exe_filter");       //full paths of existing exes
$dlls       = glob("$dll_filter");       //full paths of existing dlls
$bins       = array_merge($exes, $dlls); //full paths of existing exes & dlls
$lngs       = glob("$lng_filter");       //full paths of all existing lngs
$bins_count = count($bins);
$lngs_count = count($lngs);
$lngs_used  = array();                   //names of lngs used by dlls

if (!$bins_count)
{
	_log_to($command_log, "ERROR: No binaries found");
	return;
}


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Collecting names of languages used by binaries...");
_log_to($command_log, "");

foreach($bins as $index => $bin)
{
	$nr       = $index + 1;
	$bin_name = basename("$bin");
	
	$exe = realpath("../cmd/ListLng.exe");
	$run = "_cmd_lng";
	$cmd = "$run /c $exe $bin >\"$lng_list\" 2>>\"$command_log\"";

	if (!run_process($cmd, $result, NULL, 1*60))
		return;
		
	if (0 == $result)
	{
		$lines = file("$lng_list", FILE_IGNORE_NEW_LINES);
		
		if ($lines && isset($lines[0]) && !empty($lines[0]))
		{
			$lng = $lines[0];
			_log_to($command_log, "[$nr/$bins_count] $bin_name --> $lng");
			$lngs_used[] = $lng;
		}
		else
			_log_to($command_log, "[$nr/$bins_count] $bin_name --> None");
	}
	else
		_log_to($command_log, "ERROR: [$nr/$bins_count] [$bin_name] FAILED to detect lng file");
}

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Grouping existing languages by folders and collecting unique names...");
_log_to($command_log, "");

$lngs_exist = [];
$lngs_exist_by_dir = [];
foreach($lngs as $lng)
{
	$lng_name                      = basename($lng);
	$lng_dir                       = dirname($lng);
	$lngs_exist_by_dir[$lng_dir][] = $lng_name;
	$lngs_exist[]                  = $lng_name;
}

sort($lngs_exist);
$lngs_exist = array_unique($lngs_exist);
//_log_to($command_log, implode(', ', $lngs_exist));


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Checking for missmaching languages between different language folders (LT/NL/etc.)...");
_log_to($command_log, "");

foreach($lngs_exist_by_dir as $lng_dir => $lng_names)
	if ($missing_in_dir = array_udiff($lngs_exist, $lng_names, 'strcasecmp'))
		_log_to($command_log, "NOTICE: Language missmach, missing in [$lng_dir]: " . implode(', ', $missing_in_dir));


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Checking for not used languages...");

foreach($lngs_exist_by_dir as $lng_dir => $lng_names)
	if ($removes = array_udiff($lng_names, $lngs_used, 'strcasecmp'))
	{
		_log_to($command_log, "");
		
		foreach($removes as $remove)
		{
			_log_to($command_log, "Removing: $lng_dir\\$remove");
			
			if (!unlink("$lng_dir\\$remove"))
				_log_to($command_log, "ERROR: Failed to remove not used language [$lng_dir\\$remove]");
		}
	}

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Checking for missing languages...");

foreach($lngs_exist_by_dir as $lng_dir => $lng_names)
	if ($missings = array_udiff($lngs_used, $lng_names, 'strcasecmp'))
	{
		_log_to($command_log, "");
		
		foreach($missings as $missing)
			_log_to($command_log, "ERROR: MISSING language [$lng_dir\\$missing]");
	}


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "[DONE]");

?>