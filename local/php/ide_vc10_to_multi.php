<?php

//***************************************************
// Build *.sln using Microsoft Visual Studio C++ 10.0
//***************************************************

function ReadProjectConfigurationsVC10($path_dsp)
{
	$aCfg = array();
	$content = file_get_contents($path_dsp);
	
	$tmp1 = explode("<Configuration>", $content);
	array_shift($tmp1);
	
	foreach ($tmp1 as $tmp2)
	{
		$tmp3 = explode("</Configuration>", $tmp2);
		$str  = array_shift($tmp3);
		$str  = trim($str);
		//echo $str.' ';
		$aCfg[$str] = strtolower($str);
	}
	
	//print_r($aCfg);
	return $aCfg;
}

//=================================================================================
//=================== "OVERIDABLE" FUNCTIONS ======================================

function ReadProjectConfigurations($path_dsp)
{
	return ReadProjectConfigurationsVC10($path_dsp);
}

function GetOpenPrjCommand($prj)
{
	return "%COMSPEC% /c devenv \"$prj\""; //-nologo
}

function getSTLPortIncludeRelativePath($targetFile)
{
	$target_dir = dirname($targetFile) . "/";
	$stl = getLibrariesSTLportPath($targetFile);
	
	if(strlen($stl) == 0)
	{
		return "";
	}
	
	$stl .= "/";
	
	$relative_path = getRelativePath_($target_dir, $stl);
	$relative_path = rtrim($relative_path, "/");
	return $relative_path;
}

function GetBuildCommands($build_info, $rebuild, $log_file)
{
	$data = explode(" * ", $build_info);
	$vc6_proj  = $data[0];
	$vc6_conf  = $data[1];
	$vc10_proj = preg_replace('"\.dsp$"', '.vcxproj', $vc6_proj);
	$vc10_conf = $vc6_conf;//CovertConfigurationVC6toVC10($vc6_conf);
	$build     = $rebuild ? "/REBUILD" : "/BUILD";
	$phpPath   = dirname(dirname(__FILE__))."\\bin\\php.exe";
	$opt_pre   = "%COMSPEC% /c {$phpPath} \"".dirname(__FILE__)."\\vc10_settings.php\" \"{$vc10_proj}\" ";

	$cmds = Array();
	// Compile
	$cmds[0] = $opt_pre . "\"MultiProcessorCompilation\" \"true\" \"true\" \"ClCompile\"";
	$cmds[1] = $opt_pre . "\"MinimalRebuild\"            \"false\" \"true\" \"ClCompile\"";
	return $cmds;
}


?>