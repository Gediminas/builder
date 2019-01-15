<?php

//**************************************************
// Build *.dsp using Microsoft Visual Studio C++ 6.0
//**************************************************


function ReadProjectConfigurationsVC6($path_dsp)
{
	$s_first = "!MESSAGE Possible choices for configuration are:";
	$s_next  = "\n!MESSAGE ";

	$content = file_get_contents($path_dsp);
	str_replace("\r", "", $content);

	$tmp1 = explode($s_first, $content);
	$tmp2 = array_pop($tmp1);
	$aStr = explode($s_next, $tmp2); // get configurations + remaining trash
	array_pop($aStr);                                                 // remove remaining trash

	$aCfg = array();

	foreach ($aStr as $i => $str)
	{
		$str = trim($str);
		
		if (!empty($str))
		{
			$aToken      = explode("\"", $str);
			$full        = $aToken[1];
			$tmp1        = explode("- Win32 ", $full);
			$tmp2        = array_pop($tmp1);
			$short       = strtolower($tmp2);
			$aCfg[$full] = $short;
		}
	}
	
	return $aCfg;
}

//=================================================================================
//=================== "OVERIDABLE" FUNCTIONS ======================================

function ReadProjectConfigurations($path_dsp)
{
	return ReadProjectConfigurationsVC6($path_dsp);
}

function GetOpenPrjCommand($prj)
{
	return "%COMSPEC% /c msdev \"$prj\" /Y3";
}

function GetBuildCommands($build_info, $rebuild, $log_file)
{
	$data = explode(" * ", $build_info);
	$prj  = $data[0];
	$cfg  = $data[1];
	
	$cmds = Array();
	$cmds[0] = "%COMSPEC% /c msdev \"$prj\" /MAKE \"$cfg\" /Y3";

	if ($rebuild)
	{
		$cmds[0] = $cmds[0] . " /REBUILD";
	}

	if (!is_null($log_file))
	{
		$cmds[0] = $cmds[0] . " /OUT \"$log_file\"";
	}

	return $cmds;
}

?>