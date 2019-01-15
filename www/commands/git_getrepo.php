<?php

//*1 GIT:GetRepo(repo_path, dest_path)
//* -------------
//*2 repo_path - repository path('master' branchh will be used). dest_path - destination path.
//* -------------
//*3 Get repo_path using git to the dest_path.

$repo_path = $cmd_params[0];
$dest_path = $cmd_params[1];

require_once("../tools/run_process.php");

_log_to($command_log, "Destination path: ".$dest_path);
_log_to($command_log, "Repository path: ".$repo_path);

if ( file_exists($dest_path) )
{
	_log_to($command_log, "Destination path exist. ");
	
	$cmd = "cd ".$dest_path;
	$batch = new CRunBatch($sys_params);
	$batch->Add($cmd);
	$batch->Add("DEL .\.git\index.lock /F /S 2>NUL");
	$batch->Add("call git clean -d -f -x");
	$batch->Add("call git checkout -f master");
	$batch->Add("call git pull");
	$batch->Flush($cmd_nr);
}
else
{
	_log_to($command_log, "Destination path does not exist. ");
	$cmd = "git clone ".$repo_path." ".$dest_path; 
	$batch = new CRunBatch($sys_params);
	$batch->Add($cmd);
	_log_to($command_log, "Run:  ".$cmd);
	$batch->Flush($cmd_nr);
}	

_log_to($command_log, "GIT:GetRepo: [DONE].");

?>