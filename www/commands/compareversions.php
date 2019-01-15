<?php

//*1 CompareVersion(file_path1, file_path2)
//* -------------
//*2 file_path1 - Path to *.exe or *.dll
//*2 file_path2 - Path to *.exe or *.dll
//* -------------

$file_path1 = path_to_dos($cmd_params[0]);
$file_path2 = path_to_dos($cmd_params[1]);
$worker_id  = $sys_params['worker_id'];

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> Path1: [$file_path1]", false);
_log_to($command_log, "> Path2: [$file_path2]", false);
_log_to($command_log, "-----------------------------------------", false);

$exe = realpath("../cmd/verpatch.exe");

if (!is_file($file_path1))
{
	_log_to($command_log, "ERROR: Not found [$file_path1]");
	return;
}

if (!is_file($file_path2))
{
	_log_to($command_log, "ERROR: Not found [$file_path2]");
	return;
}

if (!$exe)
{
	_log_to($command_log, "ERROR: Not found [verpatch.exe]");
	return;
}


$tag_fileversion = "FILEVERSION"; //e.g. "FILEVERSION	5,0,143,0"
$run             = "_cmd_ver";
$tmp_version     = get_worker_tmp_file($worker_id, 'version');
$cmd1            = "$run /c $exe \"$file_path1\" /vo 1>\"$tmp_version\" 2>>\"$command_log\"";
$cmd2            = "$run /c $exe \"$file_path2\" /vo 1>\"$tmp_version\" 2>>\"$command_log\"";

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (is_file("$tmp_version") && !unlink("$tmp_version"))
	_log_to($command_log, "WARNING: Failed to delete [$tmp_version], version info for [$bin_name] can be incorrectly set");

if (!run_process($cmd1, $result, NULL, 2*60))
{
	_log_to($command_log, "ERROR: [$file_path1] --> Process FAILED to start [$cmd1]");
	return;
}

if (1 == $result)
{
	_log_to($command_log, "ERROR: [$file_path1] --> No version info [$cmd1]");
	return;
}

	
assert(0 == $result);
$ver_info = file_get_contents("$tmp_version");

if (!$ver_info)
	_log_to($command_log, "ERROR: [$file_path1] --> Process FAILED to generate temp file, result [$result] [$cmd1]");

$version1 = "";
	
if ($pos = strpos($ver_info, $tag_fileversion))
{
	$pos     += strlen($tag_fileversion);
	$len      = strpos($ver_info, 0x0D, $pos) - $pos;
	$version1 = substr($ver_info, $pos, $len);
	$version1 = trim($version1);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
if (is_file("$tmp_version") && !unlink("$tmp_version"))
	_log_to($command_log, "WARNING: Failed to delete [$tmp_version], version info for [$bin_name] can be incorrectly set");

if (!run_process($cmd2, $result, NULL, 2*60))
{
	_log_to($command_log, "ERROR: [$file_path2] --> Process FAILED to start [$cmd2]");
	return;
}

if (1 == $result)
{
	_log_to($command_log, "ERROR: [$file_path2] --> No version info [$cmd2]");
	return;
}

	
$version2 = "";
	
if ($pos = strpos($ver_info, $tag_fileversion))
{
	$pos     += strlen($tag_fileversion);
	$len      = strpos($ver_info, 0x0D, $pos) - $pos;
	$version2 = substr($ver_info, $pos, $len);
	$version2 = trim($version1);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

_log_to($command_log, "> Path1 version: [$version1]", false);
_log_to($command_log, "> Path2 version: [$version2]", false);
_log_to($command_log, "");

$result = ("$version1" == "$version2");

if ($result)
	_log_to($command_log, "> VERSIONS ARE EQUAL", false);
else
	_log_to($command_log, "> ERROR: VERSIONS DO NOT MATCH [$version1 != $version2] [$file_path1] [$file_path2]", false);

_log_to($command_log, "");
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, ($result ? "[EQUAL]" : "[NOT EQUAL]"));

?>