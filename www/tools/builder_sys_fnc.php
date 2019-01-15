<?php

require_once(dirname(__FILE__) . '/../conf/conf_fnc.php');
require_once(dirname(__FILE__) . '/../tools/log.php');
require_once(dirname(__FILE__) . '/../db/builder_db_params_fnc.php');

function StartDaemon()
{
	_log_warning("STARTING daemon", '');

	$log = get_daemon_log();

	if (LockState::Locked == GetLockState('daemon'))
	{
		_log_warning("Daemon already started");
		//$log = get_sub_log($log);
	}
	elseif (is_file($log) && !@unlink($log))
	{
		_log_error("$log is locked");
		//$log = get_sub_log($log);
	}
	else
	{
		
		_log_reset($log, "$log");

		$php = "__php-win-BUILDER-DAEMON";
		$cmd = realpath("../core/builder_daemon.php");
		$run = "start /B $php \"$cmd\" >> \"$log\" 2>&1";

		_log_to($log, "$run");
		pclose(popen($run, "r"));
	}
}

function StopDaemon()
{
	_log_warning("STOPPING daemon", '');
	set_param('daemon', 'halt', true);
	
	
	for ($seconds = 1; LockState::Locked == GetLockState('daemon'); $seconds++)
	{
		sleep(1);
		_log("waiting: $seconds s.");
	}

	_log("STOPPED");
}

function RestartDaemon()
{
	StopDaemon();
	StartDaemon();
}

?>