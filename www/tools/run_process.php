<?php

if (is_file("log.php"))
	include_once("log.php");

if (is_file("lock.php"))
	include_once("lock.php");

function __log($text)          { if (function_exists('_log'))       _log($text);       else echo "$text\n";}
function __log_error($text)    { if (function_exists('_log_error')) _log_error($text); else echo "ERROR: $text\n";}
function __log_to($log, $text) { if (function_exists('_log_to'))    if (!is_null($log)) _log_to($log, $text); }

function __wait_for_log_release($log)
{
	if (is_null($log))
		return;

	assert(!empty($log));
	
	if (function_exists('IsFileLockedForWriting') && IsFileLockedForWriting($log))
	{
		__log("Waiting childs to finish. (locked [$log])");
		while (IsFileLockedForWriting($log)) sleep(5);
		__log("Childs finished. (unlocked [$log])");
	}
}

function fgets_pending(&$stream_in)
{
	$read    = array($stream_in);
	$write   = NULL;
	$except  = NULL;
	$tv_sec  = NULL;
	
	if (!stream_select($read, $write, $except, $tv_sec) )
		return FALSE;
		
	$stdout = fgets($stream_in);
	
	if (!empty($stdout))
		__log("Process output: [$stdout]");
}

function terminate_process($process, $pipes, $cmd, $log, $duration, $timeout)
{
	__log_error("TIMEOUT. TERMINATING... (duration=$duration, timeout=$timeout)");

	proc_terminate($process);

	do
	{
		sleep(5);

		$info = proc_get_status($process);
		
		if ($info['running'])
			__log("Still running...");
	}
	while($info['running']);

	fgets_pending($pipes[1]);
	foreach ($pipes as $pipe) fclose($pipe);
	$result = proc_close($process);

	__log("Process TERMINATED");
	__log("RETURNED [$result]");
	
	__wait_for_log_release($log);
	__log_to($log, "ERROR: TIMEOUT (result=$result, duration=$duration, timeout=$timeout) [$cmd]");
	__log_to($log, "[TIMEOUT]");
	
	return $result;
}

function close_process($process, $pipes, $cmd, $log)
{
	foreach ($pipes as $pipe) fclose($pipe);
	$result = proc_close($process);
	__log("RETURNED [$result]");
	return $result;
}

function run_shell_process($cmd, &$result, $log, $timeout=NULL)
{
	$opt = NULL;
	return _run_process($cmd, $result, $log, $timeout, $opt);
}

function run_process($cmd, &$result, $log, $timeout=NULL)
{
	$opt = Array('bypass_shell' => true);
	return _run_process($cmd, $result, $log, $timeout, $opt);
}

function _run_process($cmd, &$result, $log, $timeout, $opt)
{
	$result   = -1;
	$cmd      = str_replace('%COMSPEC%', '_cmd', $cmd);
	$cmd_full = $cmd;

	if (!is_null($log))
	{
		$cmd_full = "$cmd_full >> \"$log\" 2>&1\"";
		__log_to($log, "");
		__log_to($log, $cmd_full);
	}
	
	__log("");
	__log("$cmd_full");

	$descriptorspec = array(0 => array("pipe", "r"),
							1 => array("pipe", "a"), 
							2 => array("pipe", "a"));
	$cwd = NULL;
	$env = NULL;

	$process = proc_open($cmd_full, $descriptorspec, $pipes, $cwd, $env, $opt);

	if (!$process || !is_resource($process))
	{
		__log_error("Process failed to start");
		__log_to($log, "ERROR: Process failed to start [$cmd]");
		return false;
	}

	$info = proc_get_status($process);
	
	__log("Process started (PID={$info['pid']}, timeout=$timeout)");
	__log_to($log, "Process started (PID={$info['pid']}, timeout=$timeout)");
	
	$time_start = time();
	
	do
	{
		fgets_pending($pipes[1]);

		$info = proc_get_status($process);
		if (!$info['running'])
			break;
		
		if (!is_null($timeout) && $timeout < ($duration = time() - $time_start))
		{
			$result = terminate_process($process, $pipes, $cmd, $log, $duration, $timeout);
			return false;
		}
		
		usleep(10000);//0.01s
	}
	while(!feof($pipes[1]));

	$result = close_process($process, $pipes, $cmd, $log);
	return true;
}

?>