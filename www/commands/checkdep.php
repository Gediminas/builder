<?php

//*1 CheckDep(distr_bin_path, preserves)
//* -------------
//*2 distr_bin_path - 
//*2 preserves - 
//* -------------
//*3 Check dependency, remove unused files

$distr_bin_path = $cmd_params[0];
$preserves      = $cmd_params[1];
$worker_id      = $sys_params['worker_id'];

require_once("../tools/dependency.php");

//echo "check dir: [$distr_bin_path*]\n";
$removes     = dependList($command_log, $worker_id, "$distr_bin_path", "$preserves", 1);
$remove_list = explode(";", $removes);

// Remove unused binaries

foreach ($remove_list as $file_name) if (strlen($file_name)>1)
{
	$file_path = "$distr_bin_path/$file_name";
	unlink("$file_path");
	_log_to($command_log, "Removing dependency: [$file_path]");
}

// Cheking preserve list";
checkDependList($command_log, $distr_bin_path, $preserves);

// Cheking dll's in pakage
// There are missing:";

$missing = dependList($command_log, $worker_id, "$distr_bin_path", "$preserves", 3); // type3 shows missing dll's
if (strlen($missing) > 4)
	_log_to($command_log, "WARNING: Some files missing [$missing]");

?>