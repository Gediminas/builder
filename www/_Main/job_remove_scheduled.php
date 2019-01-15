<?php

$all      = isset($_GET['all'])      ? $_GET['all']      : 0;
$bcomment = isset($_GET['bcomment']) ? $_GET['bcomment'] : false;

echo header("Refresh: 5; url=../_Main/index.php?all=$all&bcomment=$bcomment");

require_once("../conf/conf_fnc.php");
require_once("../tools/file_tools.php");
require_once("../tools/lock.php");
require_once("../db/builder_db_jobs_fnc.php");
require_once("../db/builder_db_params_fnc.php");

echo "<H1>Removing scheduled (but not started) products</H1>";

$jobs = get_all_jobs();
$stop = FALSE;
//array_shift($jobs);

foreach ($jobs as $job)
{
	$job_id       = $job[0];
	$job          = get_job($job_id);
	$product_id   = $job['product'];
	$worker_id    = $job['worker'];
	$user_comment = $job['comment'];
	
	if (0 < $worker_id)
	{
		$lock_state = GetLockState("worker$worker_id");

		if (LockState::Locked == $lock_state)
		{
			//set_param("worker$worker_id", 'halt_user', 1);
			echo "SKIPPING: {$product_id}<br/>\n";
			//$stop = TRUE;
			continue;
		}
		else
		{
			echo "REMOVING DIED: {$product_id}<br/>\n";
			remove_job($job_id);
			//$stop = TRUE;
		}
	}
	else
	{
		echo "Removing: {$product_id}<br/>\n";
		remove_job($job_id);
	}
}

echo "<H1>[DONE]</H1>";

//if ($stop)
//	die("");

?>