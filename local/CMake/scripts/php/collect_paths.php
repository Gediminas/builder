<?php

function ClearFile($file_path) {
	// Delete file

	$old_error_rep = error_reporting(E_ALL &~ E_WARNING);

	while (file_exists($file_path) && !unlink($file_path))
	{
		clearstatcache();
		echo "'$file_path' is locked (by another build?). Waiting 5 seconds...\n";
		sleep(5);
	}

	error_reporting($old_error_rep);

	// Create empty file

	$file = fopen($file_path, 'a');
	fclose($file);
}

function Correct(&$path)
{
	$path = realpath($path);
	$path = trim($path);
	$path = str_replace("\\", "/", "$path"); //common style
	//$path = str_replace("/", "\\", "$path");   //windows style
}

function ValidatePath(&$path)
{
	if (!is_file($path)) 
	{
		echo "ERROR: file ['$path'] not found\n";
		return false;
	}

	Correct($path);
	return true;
}

function ReadPaths($_file_path)
{
	$aPath    = array();

	if (!ValidatePath($_file_path)) {
		return $aPath;
	}
	
	$root_dir = pathinfo($_file_path, PATHINFO_DIRNAME);
	$aStr 	= file($_file_path);
	
	foreach ($aStr as $i => $str) 
	{
		$str = trim($str);
		if (!empty($str) && false === strpos($str, "//")) 
		{
			$str = $root_dir . "/" . $str;
			array_push($aPath, $str);
		}
	}
	
	return $aPath;
}

function CollectProjectPaths($build_cfg, $commands_txt) 
{
	$aPth = ReadPaths($build_cfg);
	foreach ($aPth as $i => $path) if (!empty($path)) 
	{
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if ($ext == 'cfg') {
			CollectProjectPaths($path, $commands_txt);
		}
		else {
			CheckAddPath($path, $commands_txt);
		}
	}
}

function CheckAddPath($path_dsp, $cmake_paths_txt)
{
	$cmake_dir 		 = dirname($path_dsp);
	$cmake_list_file = "$cmake_dir/CMakeLists.txt";
	
	if( !file_exists($cmake_list_file) ) {
		echo "File: ${cmake_list_file} does not exist\r\n";
	}
	
	$file 	   = fopen($cmake_paths_txt, 'a');
	fwrite($file, $cmake_dir."\r\n");
	fclose($file);
}

//===================================================================
//================= MAIN ============================================

function CollectPaths($build_cfg, $commands_txt) 
{
	ClearFile($commands_txt);
	CollectProjectPaths($build_cfg, $commands_txt);
}
?>