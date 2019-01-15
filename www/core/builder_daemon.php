<?php

require_once ("../conf/conf_fnc.php");
require_once("../tools/log.php");
require_once("send_mail.php");

set_time_limit(20);

//_log(get_daemon_log());
_log("DAEMON STARTING...");

set_php_error_handler(get_daemon_log());

require_once("../tools/lock.php");
require_once("../db/builder_db_jobs_fnc.php");
require_once("../db/builder_db_params_fnc.php");
require_once("../tools/builder_script_fnc.php");
require_once("../tools/night_buils_fnc.php");
//TestLock();

if (LockState::Locked == GetLockState('daemon'))
{
	$daemon_pid = get_param("daemon", 'daemon_pid');
	_log_die("DAEMON already running PID = $daemon_pid");
}

if (!Lock('daemon', $the_key))
{
	$daemon_pid = get_param("daemon", 'daemon_pid');
	_log_warning("Lock failed");
	_log_die("DAEMON already running PID = $daemon_pid");
}

require_once("../tools/file_tools.php");

$daemon_pid = getMyPid();
_log("DAEMON STARTED (pid=$daemon_pid)");

remove_params('daemon');
set_param('daemon', 'daemon_pid', $daemon_pid);
set_param('daemon', 'started',    GetSysDateTime());

set_time_limit(0);
$tick  = 0;
$sleep = 2;
$rare  = 10*60/$sleep; //~1 time per 10 min.

while(!get_param('daemon', 'halt'))
{
	$tick = ++$tick % $rare;

	if (3 <= loglevel('daemon')) _log("Alive");
	set_param('daemon', 'check', GetSysDateTime());

	if (0 == $tick) eliminate_old_produts();
	if (0 == $tick) check_night_build();
	
	$removed_count = remove_not_started_jobs();
	if (0 != $removed_count)
		_log_error("Removed not started job(s), count [$removed_count]");

	eliminate_dead_workers();
	
	if ($job_id = check_for_new_job())
	{
		if ($worker_id = find_free_worker())
		{
			$reserved_count = reserve_job_for_worker($job_id, $worker_id);
			
			if (1 == $reserved_count)
			{
				if (1 <= loglevel('daemon')) _log("Reservation for worker [$worker_id] - job [$job_id] ");
				start_worker($job_id, $worker_id);
			}
			else
				_log_warning("Reservation failed for job [$job_id], worker [$worker_id], result [$reserved_count]");
		}
	}

	sleep($sleep);
}

_log_warning("Daemon stopped by user");
UnLock('daemon', $the_key);

//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////

function check_for_new_job()
{
	$busy_product_ids = Array();
	$busy_mutexes     = Array();
	$busy_jobs        = get_busy_jobs();
	
	foreach ($busy_jobs as $busy_job) {
		$product_id = $busy_job['product'];
		if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script, $product_info)){
			continue;
		}

		$busy_product_ids[$product_id] = TRUE;
			
		if (!empty($product_mutex)) {
			$busy_mutexes[$product_mutex] = TRUE;
		}
	}
		
	if (isset($busy_mutexes['GLOBAL']))
		return FALSE;
		
	$free_jobs = get_free_jobs();
	foreach ($free_jobs as $free_job)
	{
		$product_id = $free_job['product'];
		
		if (isset($busy_product_ids[$product_id])) {
			continue; //Prevent 2 same projects to be build in parallel
		}
		
		if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script, $product_info)) {
			continue;
		}

		if (isset($busy_mutexes[$product_mutex])) {
			continue;
		}
			
		if ($product_mutex == 'GLOBAL' && 0 < count($busy_jobs))
		{
			continue;
		}
				
		$job_id = $free_job['id'];
		if (2 <= loglevel('daemon')) _log("New job found product_id=$product_id, job_id=$job_id");
		return $job_id;
	}
	
//	if ($job = get_first_free_job())
//	{
//		$job_id     = $job['id'];
//		$product_id = $job['product'];
//		
//		if (2 <= loglevel('daemon')) _log("New job found product_id=$product_id, job_id=$job_id");
//		return $job_id;
//	}

	if (3 <= loglevel('daemon')) _log("No job");
	return FALSE;
}

function find_free_worker()
{
	for($worker_id = 1; $worker_id <= worker_count(); $worker_id ++)
	{
		if (LockState::Unlocked == GetLockState("worker$worker_id"))
		{
			if (1 <= loglevel('daemon')) _log("Found free worker [$worker_id]");
			return $worker_id;
		}
	}
	
	if (2 <= loglevel('daemon')) _log("No free worker found");
	return false;
}

function start_worker($job_id, $worker_id)
{
	$log = get_worker_log($worker_id);
	_log_reset($log, "$log");

	if (1 <= loglevel('daemon')) _log("Starting worker [$worker_id] for job_id [$job_id]");

	$php = "__php-win-BUILDER-WORKER -f";
	$cmd = realpath("../core/builder_worker.php");
	$run = "start /B $php \"$cmd\" $worker_id >> \"$log\" 2>&1";
	
	_log_to($log, "$run");

	if (debug('manual_proc_start'))
		return;

	if (2 <= loglevel('daemon')) _log($run);
	pclose(popen($run, "r"));
	sleep(1); //Just in case
	
	send_mail_on_start();
}

function eliminate_dead_workers()
{
	if (2 <= loglevel('daemon')) _log("Searching for dead workers");
	
	for ($worker_id = 1; $worker_id <= worker_count(); $worker_id++)
	{
		$lock_state = GetLockState("worker$worker_id");

		if (LockState::LockedBroken != $lock_state)
			continue;

		$product_id       = get_param("worker$worker_id", 'product_id');
		$product_dir_time = get_param("worker$worker_id", 'product_dir_time');
		$product_dir      = get_product_dir($product_dir_time);
		
		if ($product_id)
		{
			if (is_dir("$product_dir"))
			{
				$time_finished = get_param("worker$worker_id", 'time_finished');
				
				if ($time_finished)
				{
					_log_warning("Worker [$worker_id] finished, remained locked.");
				}
				else
				{
					_log_error("Dead worker [$worker_id] found");
					SetValue("$product_dir", 'status', 'died');
				}

				$daemon_log = get_daemon_log();
				$worker_log = get_worker_log($worker_id);

				_log("Removing worker [$worker_id] from jobs");
				
				$job = get_job_by_worker($worker_id);
				remove_job_by_worker($worker_id);
				send_mail_on_die($worker_id, $job);

				$logs = array(get_daemon_log(), get_worker_log($worker_id), httpd_log(), php_log());
				foreach ($logs as $log) if (is_file("$log"))
				{
					$log_name = basename($log);
					Copy("$log", "$product_dir/$log_name");
				}
				
				if (!WipeLock("worker$worker_id"))
					_log_warning("Could not release worker [$worker_id] working on product [$product_id], some process still holds him");
			}
			else
			{
				//Worker not running, product dir does not exist. Waiting for worker to start?
				_log_error("Worker [$worker_id] not running, product does not exist!  Waiting for worker to start?   product_dir=[$product_dir], product_dir_time=[$product_dir_time], product_id=$product_id");
				break;
			}
		}
		else
		{
			_log_warning("Worker [$worker_id] does not work, removing from jobs");

			$job = get_job_by_worker($worker_id);
			remove_job_by_worker($worker_id);
			send_mail_on_die($worker_id, $job);

			if (!WipeLock("worker$worker_id"))
				_log_error("Could not release worker [$worker_id] with no product started");
		}
	}
}

function eliminate_old_produts()
{
	$product_base_dir = product_dir();
	
	if (!is_dir("$product_base_dir"))
		return;
	
	$map_product_count  = Array();
	$ar_product_dir_name = scandir($product_base_dir, 1); //1=SCANDIR_SORT_DESCENDING
	
	foreach($ar_product_dir_name as $product_dir_name) if ('.' != $product_dir_name && '..' != $product_dir_name)
	{
		$product_dir_name = trim($product_dir_name);
		$product_dir      = "$product_base_dir\\$product_dir_name";
		$product_id       = GetValue($product_dir, 'product_id');
		$product_count    = isset($map_product_count[$product_id]) ? $map_product_count[$product_id] : 0;
		$map_product_count[$product_id] = ++$product_count;
		$purge            = (product_log_storage_count() < $product_count);
		$clean            = $purge ? false : (product_storage_count() < $product_count);
		
		if (!$clean && !$purge)
			continue;
			
		if ($clean && GetFlag($product_dir, 'cleaned'))
			continue;

		$action = $purge ? 'Purging' : 'Cleaning';
		
		if (!$product_id)
			_log_error("{$action} UNDEFINED old product [$product_id] dir [$product_dir]");
		elseif (loglevel('daemon'))
			_log("{$action} old product [$product_id] [$product_dir]");
				
		if ($purge)
		{
			delete_dir_tree($product_dir);
		}
		else
		{
			ASSERT($clean);
			
			$subs = scandir($product_dir);
			//echo "'{$product_dir}' contains " .  count($subs) . " subfolders+files\n";
			foreach($subs as $sub) if ('.' != $sub && '..' != $sub)
			{
				$sub = $product_dir. "\\" . trim($sub);
				
				if (is_dir($sub))
				{				
					echo " Removing folder ['{$sub}']\n";
					delete_dir_tree($sub);
				}
				else
				{
					$ext = pathinfo($sub, PATHINFO_EXTENSION);
					
					if (!empty($ext) && $ext != 'log')
					{				
						echo " Removing file ['{$sub}']\n";
						unlink($sub);
					}
				}
			}
			SetFlag($product_dir, 'cleaned');
		}
	}
}

function check_night_build()
{
	if (3 <= loglevel('daemon')) _log("Check night build, last [" . get_param('night', 'date') . "]");

	if (!is_night()) {
		return;
	}
	
	$last_run_date  = get_param('night', 'date');
	$curr_date      = GetSysDate();
	$curr_time      = GetSysTime();
	
	if ($curr_date == $last_run_date)
		return;

	if (count(get_all_jobs()))
	{
		if (2 <= loglevel('daemon')) _log("Cannot start night build - running builds found");
		return;
	}
		
	add_night_builds('Night build');
	
	set_param('night', 'date', $curr_date);
	set_param('night', 'time', $curr_time);
}

?>