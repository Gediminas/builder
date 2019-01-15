<?php

//***************************************************
// Convert *.dsp --> *.sln
// Build *.sln using Microsoft Visual Studio C++ 10.0
//***************************************************

//#require_once("ide_vc6.php");
//namespace vc10conv;

function getRelativePath_($from, $to)
{
	$from = str_replace("\\", "/", $from);
	$to = str_replace("\\", "/", $to);
	
    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir)
	{
        // find first non-matching dir
        if($dir === $to[$depth])
		{
            // ignore this directory
            array_shift($relPath);
        }
		else
		{
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1)
			{
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else
			{
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

function getLibrariesSTLportPath($path)
{
	$exist = false;
	
	$path = str_replace("\\", "/", $path);
	
	$STLport = "Libraries/STLport/stlport";
	
	$path_to_return = "";
	
	while(!$exist)
	{
		$path_ = dirname($path);
		if($path_ == $path)
		{
			$path_to_return = "";
			break;
		}
		
		$path = $path_;
		$path_to_return = $path . "/" . $STLport;
		if(is_dir($path_to_return))
		{
			return $path_to_return;
		}
		
		if(strlen($path) == 0)
		{
			$path_to_return = "";
			break;
		}
	}
	
	return $path_to_return;
}

function CovertConfigurationVC6toVC10($vc6_conf)
{
	$vc10_conf = explode(" - Win32 ", $vc6_conf);
	$vc10_conf = $vc10_conf[1]."|Win32";
	return $vc10_conf;
}

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
	$vc10_conf = CovertConfigurationVC6toVC10($vc6_conf);
	$build     = $rebuild ? "/REBUILD" : "/BUILD";
	$phpPath   = dirname(dirname(__FILE__))."\\bin\\php.exe";
	$opt_pre   = "%COMSPEC% /c {$phpPath} \"".dirname(__FILE__)."\\vc10_settings.php\" \"{$vc10_proj}\" ";
	
	
	$cmds = Array();
	$pos = -1;

	////////////////////////////////////
	//  CONVERT VC6 --> VC10 ///////////
	
	$cmds[++$pos] = "%COMSPEC% /c vcupgrade \"$vc6_proj\""; //-nologo
	if (!is_null($log_file)) {
		$cmds[$pos] = $cmds[$pos] . " /OUT \"$log_file\"";
	}
	
	
	////////////////////////////////////
	//  OPTIONS  ///////////////////////
	
	// Compile
	$cmds[++$pos] = $opt_pre . "\"MultiProcessorCompilation\" \"true\"  \"true\" \"ClCompile\"";
	$cmds[++$pos] = $opt_pre . "\"MinimalRebuild\"            \"false\" \"true\" \"ClCompile\"";

	// STLPort
	$STLPortPath = getSTLPortIncludeRelativePath($vc10_proj);
	if (strlen($STLPortPath) != 0) {
		$STLport_lib_path = dirname($STLPortPath) . "/lib";
		$cmds[++$pos] = $opt_pre . "\"AdditionalIncludeDirectories\" \"$STLPortPath\"      \"false\" \"ClCompile\"";
		$cmds[++$pos] = $opt_pre . "\"AdditionalLibraryDirectories\" \"$STLport_lib_path\" \"false\" \"Link\"";
	}


	return $cmds;
}


?>