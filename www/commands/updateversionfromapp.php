<?php

//*1 UpdateVersionFromApp(bin_path, path_to_app, base_date)
//* -------------
//*2 bin_path    - binaries *.exe and *.dll folder
//*2 path_to_app - app path to retrieve version from
//*2 base_date   - The date when project was started
//*2 company     - Version will be updated only for files from this company
//* -------------
//*3 1. Update exes and dlls version info
//*3 2. Register build version (days elapsed from 'base_date') for reports if 'base_date' is set


$bin_path   = path_to_dos($cmd_params[0]);
$app_path   = $cmd_params[1];
$base_date  = $cmd_params[2];
$company    = $cmd_params[3];
$worker_id  = $sys_params['worker_id'];
$curr_date  = date("Y-m-d");
$build      = (-1 == $base_date) ? 0 : round((strtotime($curr_date) - strtotime($base_date))/86400);
$exe_filter = "$bin_path\\*.exe";
$dll_filter = "$bin_path\\*.dll";

$outputFile = "version";
$cmd_get_version  = "$app_path -v $outputFile";

try
{
	exec($cmd_get_version);
    $result = file_get_contents($outputFile);
    //unlink($outputFile);
}
catch (Exception $e) {
	$error = $e->getMessage();;
	_log_to($command_log, "ERROR: $error");
}

$version = $result.'.'.$build . '.0';

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> VERSION   = [$version]", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> base_date = " . (-1 != $base_date ? "$base_date" : "NONE"));
_log_to($command_log, "> curr_date = $curr_date");
_log_to($command_log, "> build     = $build");
_log_to($command_log, "> company   = $company");
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> Binaries path = [$bin_path]", false);
_log_to($command_log, "> EXE filter    = [$exe_filter]", false);
_log_to($command_log, "> DLL filter    = [$dll_filter]", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "");


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Registering build version for reports...");

if (-1 != $base_date)
	$sys_params['build_nr'] = $build;
else
	_log_to($command_log, "Base-date not set, skipping...");


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Collecting binaries...");

$exes       = glob("$exe_filter");       //full paths of existing exes
$dlls       = glob("$dll_filter");       //full paths of existing dlls
$bins       = array_merge($exes, $dlls); //full paths of existing exes & dlls
$bins_count = count($bins);

if (!$bins_count)
{
	_log_to($command_log, "ERROR: No binaries found");
	return;
}


_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "Analyzing binaries with version info...");
_log_to($command_log, "");

$exe = realpath("../cmd/verpatch.exe");

if (!$exe)
{
	_log_to($command_log, "ERROR: Not found [../cmd/verpatch.exe]");
	return;
}

$run         = "_cmd_ver";
$tmp_version = get_worker_tmp_file($worker_id, 'version');
$tag_company = "VALUE \"CompanyName\", \"";
$ar_nores    = [];
$ar_company  = [];
$ar_failed   = [];
$ar_skipped  = [];

foreach($bins as $index => $bin)
{
	$nr       = $index + 1;
	$bin_name = basename("$bin");
	$cmd_get  = "$run /c $exe \"$bin\" /vo 1>\"$tmp_version\" 2>>\"$command_log\"";
	
	if (is_file("$tmp_version") && !unlink("$tmp_version"))
		_log_to($command_log, "WARNING: Failed to delete [$tmp_version], version info for [$bin_name] can be incorrectly set");
	
	if (!run_process($cmd_get, $result, NULL, 2*60))
	{
		_log_to($command_log, "ERROR: [$nr/$bins_count] $bin_name --> Process FAILED to start [$cmd_get]");
		$ar_failed[] = $bin;
		continue;
	}
	
	if ($result == 1) {
		//No version info
		_log_to($command_log, "[$nr/$bins_count] $bin_name --> (NO-RES)");
		$ar_nores[$bin] = 1;
		$ar_company[$bin] = '';
		continue;
	}

	assert(0 == $result);
	$ver_info = file_get_contents("$tmp_version");
		
	if (!$ver_info) {
		_log_to($command_log, "ERROR: [$nr/$bins_count] $bin_name --> Process FAILED to generate temp file, result [$result] [$cmd_get]");
		$ar_failed[] = $bin;
		continue;
	}

	$tmp_company = '';
		
	if ($pos = strpos($ver_info, $tag_company)) {
		$pos         += strlen($tag_company);
		$len          = strpos($ver_info, '"', $pos) - $pos;
		$tmp_company  = substr($ver_info, $pos, $len);
	}
	if ($tmp_company == "\\0") {
		$tmp_company = '';
	}
	_log_to($command_log, "[$nr/$bins_count] $bin_name --> '$tmp_company'");
	$ar_company[$bin] = $tmp_company;
}

///////////////////////////////////////////////////////////////////////////////////////////////

if (count($ar_company))
{
	_log_to($command_log, "");
	_log_to($command_log, "-----------------------------------------", false);
	_log_to($command_log, "CREATING or OVERWRITING version info [$version]:");
	_log_to($command_log, "");

	$index = 0;
	foreach($ar_company as $bin => $tmp_company)
		if ($tmp_company == $company)
		{
			$nr = $index + 1;
			$bin_name = basename("$bin");
			$cmd_set = '';
			
			if (isset($ar_nores[$bin])) {
				$cmd_set  = "$run /c $exe \"$bin\" /va \"$version\" 1>\"$tmp_version\" 2>>\"$command_log\"";
				_log_to($command_log, "[$nr] $bin_name ==> {$version} (CREATE)", false);
			}
			else {
				$cmd_set  = "$run /c $exe \"$bin\" \"$version\" 1>\"$tmp_version\" 2>>\"$command_log\"";
				_log_to($command_log, "[$nr] $bin_name ==> {$version} (update)", false);
			}
			
			if (!run_process($cmd_set, $result, NULL, 2*60)) {
				_log_to($command_log, "ERROR: Process failed [$cmd_set]");
			}
		}
		else
		{
			$ar_skipped[$bin] = $tmp_company;
		}
}

///////////////////////////////////////////////////////////////////////////////////////////////

if (count($ar_skipped))
{
	_log_to($command_log, "");
	_log_to($command_log, "-----------------------------------------", false);
	_log_to($command_log, "SKIPPED with non maching 'CompanyName':");
	_log_to($command_log, "");

	$nr = 0;
	foreach($ar_skipped as $bin => $tmp_company)
	{
		$nr++;
		$bin_name = basename("$bin");
		_log_to($command_log, "[$nr] $bin_name --> '$tmp_company'", false);
	}

	///////////////////////////////////////////////////////////////////////////////////////////////

	_log_to($command_log, "");
	_log_to($command_log, "-----------------------------------------", false);
	_log_to($command_log, "UNIQUE company names:");
	_log_to($command_log, "");

	foreach($ar_company as $tmp_company)
		$unique_companies[] = $tmp_company;
		
	$unique_companies = array_unique($unique_companies);
	sort($unique_companies);

	$nr = 0;
	foreach($unique_companies as $index => $tmp_company)
	{
		$nr++;
		_log_to($command_log, "[$nr] '$tmp_company'", false);
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////

if (count($ar_failed))
{
	_log_to($command_log, "");
	_log_to($command_log, "-----------------------------------------", false);
	_log_to($command_log, "SKIPPED failed:");
	_log_to($command_log, "");

	foreach($ar_failed as $index => $bin)
	{
		$nr = $index + 1;
		$bin_name = basename("$bin");
		_log_to($command_log, "ERROR: [$nr] $bin_name - version update failed", false);
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////

_log_to($command_log, "");
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "[DONE]");

?>