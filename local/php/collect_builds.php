<?php
require_once("file_utils.php");

function ExplodeTrimLower($str, $delim=',') {
	$arr = explode($delim, $str);
	foreach ($arr as $i => &$item) $item = strtolower(trim($item));
	usort($arr, 'strcasecmp');
	return $arr;
}

function ResetFile($file_path) {
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

function CollectProjects($build_cfg, $configurations, $commands_txt) {
	$aPth = ReadPathsFromFile($build_cfg);
	foreach ($aPth as $i => $path) if (!empty($path)) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if ($ext == 'cfg') {
			CollectProjects($path, $configurations, $commands_txt);
		}
		else {
			CheckAddProject($path, $configurations, $commands_txt);
		}
	}
}

function CheckAddProject($path_dsp, $configurations, $commands_txt) {
	$aCfg = ReadProjectConfigurations($path_dsp);
	$file = fopen($commands_txt, 'a');
	foreach ($aCfg as $full_conf => $short_conf) {
		if (IsConfigurationsIncluded($short_conf, $configurations)) {
			$cmd = $path_dsp." * ".$full_conf;
			fwrite($file, $cmd."\r\n");
		}
	}
	fclose($file);
}

function IsConfigurationsIncluded($short_conf, $configurations) {
	if (in_array("[all]", $configurations)) {
		return true;
	}
	if (in_array($short_conf, $configurations)) {
		return true;
	}
	return false;
}

//===================================================================
//================= MAIN ============================================

function CollectBuilds($ide_version, $build_cfg, $config_list, $commands_txt) {
	echo "Using \"$ide_version.php\"\n\n";

	if (!include_once("$ide_version.php"))
	{
		echo "ERROR: Couldn't load $ide_version.php (CollectBuilds)\n";
		return false;
	}

	$configurations = ExplodeTrimLower("$config_list");

	ResetFile($commands_txt);
	CollectProjects($build_cfg, $configurations, $commands_txt);

	return true;
}
?>