<?php

//=================================================================================
//=================== "OVERIDABLE" FUNCTIONS ======================================
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

function ReadProjectConfigurations($path_dsp)
{
	return ReadProjectConfigurationsVC10($path_dsp);
}

function GetOpenPrjCommand($prj)
{
	return "%COMSPEC% /c devenv \"$prj\""; //-nologo
}

function GetBuildCommands($build_info, $rebuild, $log_file)
{
	$data = explode(" * ", $build_info);
	$vc10_proj  = $data[0];
	$vcupgrade = 'c:\Program Files (x86)\Microsoft Visual Studio 14.0\Common7\IDE\devenv';
	$upgrade = '/Upgrade';

	$cmds = Array();

	////////////////////////////////////
	//  CONVERT VC10 --> VC15 ///////////
	
	$cmds[0] = "%COMSPEC% /c \"\"$vcupgrade\" \"$vc10_proj\"  $upgrade\""; //-nologo
	
	echo $cmds[0];
	
		
	return $cmds;
}


?>