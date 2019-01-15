<?php

function autoscroll()              { require("conf.php"); return $autoscroll; }
function version()                 { require("conf.php"); return $version; }
function db_jobs_path()            { require("conf.php"); return $db_jobs_path; }
function db_params_path()          { require("conf.php"); return $db_params_path; }
function db_history_path()         { require("conf.php"); return $db_history_path; }
function autotester_db_path()      { require("conf.php"); return $autotester_db_path; } //new
function autotester_results_path() { require("conf.php"); return $autotester_results_path; } //old
function autotester_results_bak()  { require("conf.php"); return $autotester_results_bak; } //old
function src_dir()                 { require("conf.php"); return $src_dir; }
function tmp_dir()                 { require("conf.php"); return $tmp_dir; }	
function client_data_dir()         { require("conf.php"); return $client_data_dir; }	
function product_dir()             { require("conf.php"); return $product_dir; }	
function httpd_pid()               { require("conf.php"); return $httpd_pid; }	
function httpd_log()               { require("conf.php"); return $httpd_log; }	
function access_log()              { require("conf.php"); return $access_log; }	
function php_log()                 { require("conf.php"); return $php_log; }	
function host()                    { require("conf.php"); return $host; }
function default_TTL()             { require("conf.php"); return $default_TTL; }
function product_storage_count()   { require("conf.php"); return $product_storage_count; }
function product_log_storage_count(){ require("conf.php");return $product_log_storage_count; }
function LogTestShow()             { require("conf.php"); return $LogTestShow; }
function debug_dir_name()          { require("conf.php"); return $debug_dir_name; }
function _system_dll()             { require("conf.php"); return $_system_dll; }
function hr()                      { require("conf.php"); return $hr; }

function GetSysDate()                  { return date("Y-m-d"); }
function GetSysTime()                  { return date("H:i:s"); }
function GetSysDateTime2($date, $time) { return "$date $time"; }
function GetSysDateTime()              { return date("Y-m-d H:i:s"); }

// DEBUG

function debug($param)
{
	require("conf.php");
	return (isset($debug) && is_array($debug) && isset($debug[$param])) ? $debug[$param] : NULL;
}

function product_is_debug($product_id)
{
	return (1 == stripos("_$product_id", '_dbg_'));
}

function timestamp_is_debug($time_stamp)
{
	return 20 == strlen($time_stamp) && '_' == $time_stamp[19];
}

function timestamp_add_debug_flag($time_stamp)
{
	if (19 == strlen($time_stamp))
		$time_stamp .= '_';
	
	assert(timestamp_is_debug($time_stamp));
	return $time_stamp;
}

function timestamp_remove_debug_flag($time_stamp)
{
	if (timestamp_is_debug($time_stamp))
		$time_stamp = rtrim($time_stamp, '_');
	
	assert(!timestamp_is_debug($time_stamp));
	return $time_stamp;
}

function is_night()
{
	require("conf.php");
	$curr_time = GetSysTime();
	if ($curr_time < $night_start || $night_end < $curr_time) {
		return false;
	}
	return true;
}

function worker_count()
{
	require("conf.php");
	if (is_night()) {
		return 1;
	}
	return $worker_count;
}

function setting($param)
{
	require("conf.php");
	return (isset($setting) && is_array($setting) && isset($setting[$param])) ? $setting[$param] : NULL;
}

function loglevel($param)
{
	require("conf.php");
	return (isset($loglevel) && is_array($loglevel) && isset($loglevel[$param])) ? $loglevel[$param] : NULL;
}

function trace_debug_options()
{
	require("conf.php");

	if (!isset($debug))
		return;

	echo "<table border=0>";
	foreach($debug as $option => $value)
	{
		if     (is_bool($value))   $value = $value ? 'true' : 'false';
		elseif (is_string($value)) $value = "'$value'";

		echo "<tr> <td><font size=-2> $option </font></td> <td><font size=-2> $value </font></td> </tr>\n";
	}
	echo "</table>";
}

function get_ip()
{
	exec('ipconfig',$catch);
	
	foreach($catch as $line) 
	if(stristr($line, 'IP Address') || stristr($line, 'IPv4 Address'))
	{
		list($t,$ip) = explode(':',$line);
		return trim($ip);
	}
	
	return "";
}

function get_sub_log($log_path, $sub_log_nr)
{
	$parts = pathinfo($log_path);
	assert(isset($parts['filename']));
	
	$name  = $parts['filename'];
	$dir   = isset($parts['dirname'])   ? "{$parts['dirname']}"    : ".";
	$ext   = isset($parts['extension']) ? ".{$parts['extension']}" : "";
	$log   = "$dir\\$name-sub$sub_log_nr$ext";
	return $log;
}

// SRC
function get_src_dir($product_id)
{
	return src_dir() . "\\_prj\\" . $product_id . "\\repo";
}

//function get_src_dir($worker_id, $debug=false)
//{
//	$src = src_dir();
//	$dir = "$src\\_$worker_id";
//	if ($debug) $dir .= 'D';
//	return $dir;
//}

//TMP

function get_product_dir($time_stamp)
{
	require("../conf/conf.php");
	$base_dir = product_dir();
	$dir_name = timestamp_is_debug($time_stamp) ? debug_dir_name() : time_to_filename($time_stamp);
	$dir      = "$base_dir\\$dir_name";

	return $dir;
}

//LOG

function get_daemon_log()
{
	$tmp = tmp_dir();
	$log = "$tmp\\daemon.log";
	return $log;
}

function get_worker_log($worker_id)
{
	$tmp = tmp_dir();
	$log = "$tmp\\worker$worker_id.log";

	return $log;
}

function get_command_log($time_stamp, $cmd_nr)
{
	$dir  = get_product_dir($time_stamp);
	$name = sprintf("%03d", $cmd_nr) . ".log";
	$log = "$dir\\$name";
	return $log;
}

//SCRIPT

function get_worker_tmp_batch_path($worker_id)
{
	$tmp  = tmp_dir();
	$path = "$tmp\\worker$worker_id-batch.cmd";
	return $path;
}

function get_worker_tmp_build_list_path($worker_id)
{
	$tmp  = tmp_dir();
	$path = "$tmp\\worker$worker_id-build_list.txt";
	return $path;
}

function get_worker_tmp_dep_list_path($worker_id)
{
	$tmp  = tmp_dir();
	$path = "$tmp\\worker$worker_id-dep_list.txt";
	return $path;
}

function get_worker_tmp_lng_list_path($worker_id)
{
	$tmp  = tmp_dir();
	$path = "$tmp\\worker$worker_id-lng_list.txt";
	return $path;
}

function get_worker_tmp_file($worker_id, $key)
{
	$tmp  = tmp_dir();
	$path = "$tmp\\worker$worker_id-$key.tmp";
	return $path;
}

//HELPER
function path_to_dos($path)
{
	$path = str_replace('/', '\\', $path);
	return $path;
}

function time_to_filename($time_stamp)
{
	$time_stamp = str_replace(':', '-', $time_stamp);
	$time_stamp = str_replace(' ', '_', $time_stamp);
	return $time_stamp;
}

function CorrectUserComment($comment)
{
	$what="/#(\d+)/";
	$to="<a href='http://bugzilla.matrixlt.local/show_bug.cgi?id=$1'>#$1</a>";
	$comment = preg_replace($what, $to, $comment);
	return $comment;
}

class EErrorStatus
{
	const QUIT    = -1;
	const OK      = 0;
	const ERROR   = 1;
	const WARNING = 2;
	const NOTICE  = 4;
}


?>