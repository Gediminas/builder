<?php

$all      = isset($_GET['all'])      ? $_GET['all']      : 0;
$bcomment = isset($_GET['bcomment']) ? $_GET['bcomment'] : false;
$job_id   = isset($_GET['job_id'])   ? $_GET['job_id']   : false;

echo header("Refresh: 0; url=../_Main/index.php?all=$all&bcomment=$bcomment");

require_once("../conf/conf_fnc.php");
require_once("../tools/file_tools.php");
require_once("../tools/lock.php");
require_once("../db/builder_db_jobs_fnc.php");
require_once("../db/builder_db_params_fnc.php");

//echo "<H1>Removing product from jobs</H1>";

if (!isset($job_id) || 0 == strlen($job_id))
	die("ERROR: No product ID passed");

$job          = get_job($job_id);
$product_id   = $job['product'];
$worker_id    = $job['worker'];
$user_comment = $job['comment'];

if (0 < $worker_id)
{
	$lock_state = GetLockState("worker$worker_id");

	if (LockState::Locked == $lock_state)
	{
		echo "<H1>HALTING product <br/>\n [$product_id; $user_comment], worker [$worker_id]</H1>";
		set_param("worker$worker_id", 'halt_user', 1);
	}
	else
	{
		echo "<H1>REMOVING product <br/>\n [$product_id; $user_comment], worker [$worker_id]</H1><br/>\n";
		remove_job($job_id);
	}
}
else
{
	echo "<H1>REMOVING NOT STARTED product <br/>\n [$product_id; $user_comment]</H1><br/>\n";
	remove_job($job_id);
}

echo "<H1>[DONE]</H1>";

?>