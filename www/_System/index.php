<?php
require_once("../common/header.php");

class CGenerateSystemPage extends CGeneratePage
{
	protected function GenerateModule() 
	{
		require_once("../conf/conf_fnc.php");
		require_once("../db/builder_db_params_fnc.php");
		require_once("../tools/date_time.php");
		require_once("../tools/file_tools.php");
		
		if (loglevel('info'))
		{
			trace_debug_options();
			$daemon_start = get_param('daemon', 'started');
			$daemon_check = get_param('daemon', 'check');
			$night_date   = get_param('night',  'date');
			$night_time   = get_param('night',  'time');
			$night_build  = "{$night_date} {$night_time}";
			$curr_time    = GetSysDateTime();

			$daemon_start_diff = format_time(strtotime($curr_time) - strtotime($daemon_start));
			$daemon_check_diff = format_time(strtotime($curr_time) - strtotime($daemon_check));
			$night_build_diff  = format_time(strtotime($curr_time) - strtotime($night_build));
			
			echo "<table border=0>";
			echo "<col width=100 />";
			echo "<col width=170 />";
			echo "<tr> <td> daemon start </td> <td>$daemon_start </td> <td align=right> $daemon_start_diff ago </td> </tr>\n";
			echo "<tr> <td> night-build  </td> <td>$night_build  </td> <td align=right> $night_build_diff ago </td> </tr>\n";
			echo "<tr> <td> seen alive   </td> <td>$daemon_check </td> <td align=right> $daemon_check_diff ago </td> </tr>\n";
			echo "<tr> <td> NOW          </td> <td>$curr_time    </td> </tr>\n";
			echo "</table>";
			echo "<br/>\n";
		}
		
		echo "<table border='0'>";

		//$daemon_log = getRelativePath(realpath("."), get_daemon_log());
		$daemon_log = get_daemon_log();
		$php_log    = php_log();
		$httpd_log  = httpd_log();
		$httpd_pid  = httpd_pid();
		$access_log = access_log();

		$php_info   = "info.php";
		
		echo "<tr><td/ width=20><td><b>Logs</b></td></tr>";

		echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$access_log>  ACCESS</a></td>       </tr>";

		echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$daemon_log> Daemon</a></td>             <td>Log and related DB/PHP errors</td></tr>";
		
		for ($worker_id = 1; $worker_id <= worker_count(); $worker_id ++)
		{
			//$worker_log = getRelativePath(realpath("."), get_worker_log($worker_id));
			$worker_log = get_worker_log($worker_id);
			echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$worker_log> Worker $worker_id</a></td> <td>Log and related DB/PHP errors</td></tr>";
		}
		
		echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$php_log>    PHP errors</a></td>        <td>PHP errors that was not redirected to daemon/worker log files</td></tr>";
		echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$httpd_log>  HTTPD log</a></td>         <td>Can contain uncauth PHP errors also</td></tr>";
		echo "<tr><td/ width=20><td><a href=../_Main/show_file.php?fname=$httpd_pid>  HTTPD pid</a></td></tr>";

		echo "<tr><td/><td><hr/></td></tr>\n";
		echo "<tr><td/ width=20><td><a href=$php_info> php_info </a></td></tr>";
		
		echo "<tr><td/><td><hr/></td></tr>";
		//echo "<tr><td/ width=20><td><b>Daemon:</b></td></tr>";
		echo "<tr><td/ width=20><td><a href=reset_php_log.php> Reset PHP errors </a> </td></tr>";
		
		echo "<tr><td/ width=20><td><b>Daemon:</b></td></tr>";
		echo "<tr><td/ width=20><td><a href='../core/start_stop_daemon.php?action=1'> Start daemon   </a> </td><td>(Will reset previous log)</td></tr>";
		echo "<tr><td/ width=20><td><a href='../core/start_stop_daemon.php?action=2'> Stop daemon    </a> </td><td>---</td></tr>";
		echo "<tr><td/ width=20><td><a href='../core/start_stop_daemon.php?action=3'> Restart daemon </a> </td><td>(Will reset log)</td></tr>";

		echo "<tr><td/><td><hr/></td></tr>";
		echo "<tr><td/ width=20><td><b>Server:</b></td></tr>";
		echo "<tr><td/ width=20><td><a href='../core/restart.php'>                    Restart SERVER </a>	</td></tr>";

		echo "<tr><td/><td><hr/></td></tr>";
		echo "<tr><td/ width=20><td><b>Bugs:</b></td></tr>";
		echo "<tr><td/ width=20><td><a href='bug_wildcard_filter.php'>  wildcard filter</a></td></tr>";

		echo "<tr><td/><td><hr/></td></tr>";
		echo "<tr><td/ width=20><td><a href='http://wiki.matrixlt.local/mediawiki/index.php/AutoBuildSystem'>Help</a></td></tr>";
		echo "</table>";
	}
}

$gen_page = new CGenerateSystemPage();
$gen_page->Generate();

?>

