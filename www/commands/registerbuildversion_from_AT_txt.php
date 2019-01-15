<?php

//*1 RegisterBuildVersion_from_AT(txt_path)
//* -------------
//*2 txt_path - Autotester file 
//* -------------
//*3 Register build version from txt_path for reports and e-mail

$txt_path = path_to_dos($cmd_params[0]);
$txt      = file($txt_path, FILE_IGNORE_NEW_LINES); print_r($txt);
$log_path = path_to_dos($txt[1]); 
echo "LOG:$log_path";
$log      = file($log_path, FILE_IGNORE_NEW_LINES); print_r($log);
$version  = $log[1]; //PVC version 2.0 (Build 3900) unicode

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> txt_path  = [$txt_path]", false);
_log_to($command_log, "> log_path  = [$log_path]", false);
_log_to($command_log, "> VERSION   = [$version]", false);
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "");
_log_to($command_log, "Registering build version for reports...");

$tmp1     = explode(') ',       $version);  if (count($tmp1) != 2) { _log_to($command_log, "WARNING: Parse error, skipping..."); return; }
$tmp2     = explode(' (Build ', $tmp1[0]);  if (count($tmp2) != 2) { _log_to($command_log, "WARNING: Parse error, skipping..."); return; }
$build    = $tmp2[1];                       if (empty($build))     { _log_to($command_log, "WARNING: Parse error, skipping..."); return; }

$sys_params['build_nr'] = $build;

_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "> BUILD     = $build");
_log_to($command_log, "-----------------------------------------", false);
_log_to($command_log, "");
_log_to($command_log, "[DONE]");

?>