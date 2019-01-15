<?php

//*1 GIT:GetClean(branch)
//* -------------
//*2 branch - git branch, if NULL then 'master' is used
//* -------------
//*3 Get build sources using git

$branch = $cmd_params[0];
$TTL    = $sys_params['TTL'];

require_once("../tools/run_process.php");

if ( is_null($branch) )
{
	$branch = "master";
}

$src_dir = $sys_params['source_dir'];
$batch   = realpath("../cmd/git/get_clean.cmd");
$run     = "_cmd_git_get"; //realpath("../../cmd/_cmd_git_get");
$cmd     = "$run /c $batch \"$src_dir\" \"$branch\"";

run_process($cmd, $result, $command_log, $TTL);

?>