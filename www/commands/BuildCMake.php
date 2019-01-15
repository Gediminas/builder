<?php
	require_once("../../local/CMake/scripts/php/collect_paths.php");
	require_once("../tools/log.php");
	require_once("../../local/CMake/scripts/php/clean_cmake.php");
	
	$build_cfg_file = $cmd_params[0];
	$generator 		= $cmd_params[1];
	$rootdir		= $cmd_params[2];
	
	$build_list		= "$rootdir/Builder/local/temp/cmake_paths.txt";
	$cmakecommand	= "$rootdir/Builder/local/CMake/bin/cmake -G \"$generator\"";
	
	_log_reset($command_log);
	_log_to($command_log, "$cmd_line_full");
	echo "$cmakecommand";
	
	$curdir = getcwd();
	
	CollectPaths("$rootdir/$build_cfg_file", "$build_list");
	
	$file = file($build_list);
	_log_to($command_log, "Running $cmakecommand");
	
	foreach($file as $build_info)
	{
		$generate_path = trim($build_info);
		chdir($generate_path);
		
		echo "Generating in $generate_path\n";
		_log_to($command_log, "Generating in $generate_path");
		$output = exec("$cmakecommand");
		$pos = strpos($output, "CMakeOutput.log");
		if( $pos !== false) {
			_log_error("There were errors generating in $generate_path.");
		}
		else {
			_log_to($command_log, "$output");
		}
		cleancmake($generate_path);
	}
	
	
	chdir($curdir);
?>