<?php
// ob_start("ob_gzhandler"); // does not work correctly with the "Refresh: 1"

require_once("../conf/conf_fnc.php");

if (autoscroll())
	echo header("Refresh: 1");
?>

<head>
<script>
	function on_load() {
		<?php
			 if (autoscroll())
			 	echo "setTimeout(window.location='#bottom', 20000);";
		?>
	}
</script>
</head>
<body onload='on_load()'>

<?php

$all          = isset($_GET['all'])   ? $_GET['all']   : 0;
$product_id   = isset($_GET['id'])    ? $_GET['id']    : NULL; // get logs by product id
$product_time = isset($_GET['time'])  ? $_GET['time']  : 0;    // get logs by product start time ( = product dir)

require_once("../tools/file_tools.php");
require_once("../tools/check_errors.php");
require_once("../tools/builder_script_fnc.php");
require_once("../db/builder_db_params_fnc.php");
require_once("../tools/date_time.php");

if (is_null($product_id) xor $product_time)
	die("ERROR: Either 'id' or 'time' parameter must be set in php link");

if (is_null($product_id))
{
	//Get 'id' from 'time'
	
	$product_dir = get_product_dir($product_time);
	if (!is_dir("$product_dir"))
		die("ERROR: Dir does not exist [$product_dir]");

	$product_id = GetValue("$product_dir", 'product_id');
	if (!$product_id)
		die("ERROR: 'product_id' not set in [$product_dir]");
}
else
{
	//Get 'time' from 'id'
	
	$product_time = get_param("product", "$product_id");
	if (!$product_time)
		die("ERROR: No logs found for procuct [$product_id]");

	$product_dir = get_product_dir($product_time);
	if (!is_dir("$product_dir"))
		die("ERROR: Dir does not exist [$product_dir]");
}


if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
	die("ERROR: No such product id [$product_id]");

echo "<title>$product_name</title>\n";
echo "<a href='../_Main/index.php?all=$all'>[back]</a>\n";
echo "<h2>$product_name</h2>\n";
echo "<i>product_time=[$product_time]</i><br/>\n";
echo "<i>product_dir=[$product_dir]</i>";
echo "<hr/>";
//-------------------------------------------------------------------

echo "<table border=0> 	<TR> <TH width=95> <TH>";
for ($cmd_nr = 0; ; $cmd_nr++)
{
	$command_log = get_command_log($product_time, $cmd_nr);
	
	if (!file_exists($command_log))
		break;

	$lines      = file($command_log);
	$err        = CheckLogFileForErrors($command_log);
	$command    = isset($lines[0]) ? trim($lines[0]) : "";
	
	if (empty($command))
		$command = "$command_log";
		
	$tmp1 = explode(': ', $command, 2);
	$time = isset($tmp1[1]) ? "$tmp1[0]" : "";
	$text = isset($tmp1[1]) ? "$tmp1[1]" : "$tmp1[0]";
	$pre  = "";
	$done = false;
	
	if ($time &&
		$text != "[DONE]" &&
		$text != "[FAILED]" &&
		$text != "[HALTED]")
	{
		$next_command_log = get_command_log($product_time, $cmd_nr+1);
		$time2 = "";
		
		if (file_exists($next_command_log))
		{
			$tmp_lines   = file($next_command_log);
			$tmp_command = isset($tmp_lines[0]) ? trim($tmp_lines[0]) : "";
			$tmp2 = explode(': ', $tmp_command, 2);
			$time2 = isset($tmp2[1]) ? "$tmp2[0]" : "";
			$time2 = strtotime($time2);
			$done = $time2;
		}

		if (!$time2)
		{
			$time2 = time();
		}

		$time1    = strtotime($time);
		$duration = $time2 - $time1;
		$duration = format_time($duration);
	}
	else
	{
		$duration = "";
	}
	
	switch ($err)
	{
	case EErrorStatus::ERROR:   $pre = "!!! "; $text = "<font style='BACKGROUND-COLOR: #FF8080'  color=black>$text</font>"; break;
	case EErrorStatus::WARNING: $pre = "!!! "; $text = "<font style='BACKGROUND-COLOR: #FFFF80'  color=black>$text</font>"; break;
	case EErrorStatus::NOTICE:  $pre = "";     $text = "<font style='BACKGROUND-COLOR: #EEEEEE'  color=black>$text</font>"; break;
	}

	$line_count = GetMainAndSubLogLineCount($command_log, $sub_line_count);
		
	$time_txt       = $done ? $time          : "<b>$time</b>";
	$duration_txt   = $done ? $duration      : "<b>$duration</b>";
	$line_count_txt = $done ? "#$line_count" : "<b>#$line_count</b>";
		
	echo "<tr>\n";
	echo "<td width=97 valign='center' align='left'>  <font size='-2', color='green'>$time_txt    </font></td>\n";
	echo "<td width=50 valign='center' align='right'> <font size='-2', color='black'>$duration_txt</font></td>\n";
	echo "<td width=10 valign='center' align='right'> <font size='-2', color='black'>$line_count_txt</font></td>\n";
	echo "<td> $pre<a href='show_file.php?fname=$command_log'>$text</a></td\n";	
	echo "</tr>\n";
}	
echo "</table>";

if (is_dir($product_dir))
{
	$other_logs = file_list("$product_dir", "log");
	if ($other_logs)
	{
		$ar_other_logs   = explode(';', $other_logs);
		$separator_added = false;
		
		foreach($ar_other_logs as $log_file)
		{
			$log_file = trim($log_file);
			
			if (0 == strlen($log_file))
				continue;
			
			if ('0' <= $log_file[0] && $log_file[0]<='9')
				continue;

			if (!$separator_added)
			{
				echo "<hr>";
				echo "<b>Log files: </b><BR/>\n";
				$separator_added = true;
			}
				
			$log_path = "$product_dir/$log_file";
			$err      = CheckLogFileForErrors($log_path);
			$pre      = "";

			switch ($err)
			{
			case EErrorStatus::ERROR:   $pre = "!!! "; $text = "<font style='BACKGROUND-COLOR: #FF8080'  color=black>$log_file</font>"; break;
			case EErrorStatus::WARNING: $pre = "!!! "; $text = "<font style='BACKGROUND-COLOR: #FFFF80'  color=black>$log_file</font>"; break;
			default:                                   $text = $log_file; break;
			}
			
			echo "$pre<a href='show_file.php?fname=$log_path'>$text</a><br/>\n";	
		}
	}
}

//-------------------------------------------------------------------
$status = GetValue("$product_dir", 'status');
echo "<br/>\n";
echo "<hr/>\n";
echo "STATUS: $status<br/>\n";	



//-------------------------------------------------------------------
/*
echo "<hr>";
$sumary_raw = ProductErrorSummary($product_id, $product_time);
$sumary = explode("\n", $sumary_raw);

foreach ($sumary as $log_line) 
{
	if (strlen($log_line)>1)
	{
		$err = CheckLineForErrors($log_line);

		switch ($err)
		{
		case EErrorStatus::ERROR:   echo '<font size ="-1" color="#880000">' . $log_line . "</font><br/>"; break;
		default:                    echo '<font size ="-1" color="#AA6000">' . $log_line . "</font><br/>"; break;
		}
	}
}//eof foreach
*/

?>

<a name='bottom'>&nbsp;</a>
</body>

