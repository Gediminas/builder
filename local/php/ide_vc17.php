<?php

//***************************************************
// Build *.sln using Microsoft Visual Studio C++ 15.0
//***************************************************

function ReadProjectConfigurationsVC17($path_dsp)
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
		$aCfg[$str] = strtolower($str);
	}
	return $aCfg;
}

//=================================================================================
//=================== "OVERIDABLE" FUNCTIONS ======================================

function ReadProjectConfigurations($path_dsp)
{
	return ReadProjectConfigurationsVC17($path_dsp);
}

function GetOpenPrjCommand($prj)
{
	$devenv = 'c:\Program Files (x86)\Microsoft Visual Studio\2017\Professional\Common7\IDE\devenv';
	return "%COMSPEC% /c \"\"$devenv\" \"$prj\"\""; //-nologo
}

function GetBuildCommands($build_info, $rebuild, $log_file)
{
	$data  		= explode(" * ", $build_info);
	$dsp   		= $data[0];
	$conf  		= $data[1];
	
	if( isset($data[2]) ) {
		$platform = $data[2];
	}
	else {
		$platform = "";
	}
	//$sln   = preg_replace('"\.vcxproj$"', '.sln', $dsp);
	$sln   = $dsp;
	$build = $rebuild ? "rebuild" : "build";
	$exe   = 'c:\Program Files (x86)\Microsoft Visual Studio\2017\Professional\MSBuild\15.0\Bin\MSBuild.exe';
	
	//Build flags
	//https://msdn.microsoft.com/en-us/library/ms164311.aspx
	
	$cmds = Array();
	
	//Platform is not empty only for wxwidgets 64bit build.
	if( !empty($platform) ) {
		$cmds[0] = "%COMSPEC% /c \"\"{$exe}\" \"{$sln}\" /m /v:m /t:{$build} /p:PlatformToolset=v141 /p:Configuration=\"{$conf}\" /p:Platform=\"{$platform}\"";// > \"{$log_file}\"";
	}
	else {
		$cmds[0] = "%COMSPEC% /c \"\"{$exe}\" \"{$sln}\" /m /v:m /t:{$build} /p:PlatformToolset=v141 /p:Configuration=\"{$conf}\"";// > \"{$log_file}\"";
	}
	return $cmds;
}


?>
