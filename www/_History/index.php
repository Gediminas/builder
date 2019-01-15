<?php
require_once("../common/header.php");

class CGenerateHistoryPage extends CGeneratePage
{
	protected function GenerateModule() 
	{
		$count               = isset($_GET['count'])               ? (int)$_GET['count']          : 100;
		$filter_product_id   = isset($_GET['filter_product_id'])   ? $_GET['filter_product_id']   : '';
		$filter_error_status = isset($_GET['filter_error_status']) ? $_GET['filter_error_status'] : '';
		$filter_build_status = isset($_GET['filter_build_status']) ? $_GET['filter_build_status'] : '';

		if ('' == $filter_error_status)
			$filter_error_status=-1;

		if ('' == $filter_build_status)
			$filter_build_status=-1;
			
		require_once("../conf/conf_fnc.php");
		require_once("../tools/builder_script_fnc.php");
		require_once("../tools/date_time.php");
		require_once("builder_db_history_fnc.php");

		//FIXME: reuse get_script_names()
		$ar_product = glob("../scripts/*.xml"); 
		foreach ($ar_product as $product_xml) if (strlen($product_xml))
		{
			$product_id = basename($product_xml, '.xml');
			
			if (get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
				$ar_names[$product_id] = $product_name;
		}

		asort($ar_names);
		//FIXME: END

		///////////////////////////
		// FILTER
		echo "<form>\n";
		echo "<div>\n";

		echo "<select name='filter_product_id'>\n";
		if (empty($filter_product_id))
			echo "<option value='' selected> - all - </option>\n";
		else
			echo "<option value=''> - all - </option>\n";
		foreach ($ar_names as $product_id => $product_name)
			if (get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
				if ($filter_product_id == $product_id)
					echo "<option value='$product_id' selected>$product_name</option>\n";
				else
					echo "<option value='$product_id'>$product_name</option>\n";
		echo "</select>\n";

		$ar_build_status = array('[ok]', '[halted]', '[died]');

		echo "<select name='filter_build_status'>\n";
		if (-1 == $filter_build_status)
			echo "<option value='-1' selected> - all - </option>\n";
		else
			echo "<option value='-1'> - all - </option>\n";
		foreach ($ar_build_status as $status => $title)
			if ($filter_build_status == $status)
				echo "<option value='$status' selected>$title</option>\n";
			else
				echo "<option value='$status'>$title</option>\n";
		echo "</select>\n";


		$ar_error_status = array('[OK]', '[ERROR]', '[WARNING]');

		echo "<select name='filter_error_status'>\n";
		if (-1 == $filter_error_status)
			echo "<option value='-1' selected> - all - </option>\n";
		else
			echo "<option value='-1'> - all - </option>\n";
		foreach ($ar_error_status as $status => $title)
			if ($filter_error_status == $status)
				echo "<option value='$status' selected>$title</option>\n";
			else
				echo "<option value='$status'>$title</option>\n";
		echo "</select>\n";

		$ar_error_status = array('[OK]', '[ERROR]', '[WARNING]');


		//echo "<input type='text'   name='error_status'>\n";
		echo "<input type='text' name='count' value={$count}>\n";
		echo "<input type='submit' value='filter'>\n";
		echo "</div>\n";
		echo "</form>\n";
		// END FILTER
		///////////////////////////

		echo "<hr/>\n";
		echo "<form action='compare_git.php' method=\"post\">\n";
		echo "<td>  <input type='submit' value='Compare GIT'/> </td>\n";
		//echo "<center>\n";
		//echo "<table border=0 cellspacing='0' ALIGN=center>\n";
		echo "\n<table border='1' cellspacing='0'>\n";
		echo "<tr class='header1'>\n";
		echo "<td width='50' ><center><b> ID             </b></center></td>\n";
		echo "<td width='20' ><center><b>                </b></center></td>\n";
		echo "<td width='300'><center><b> Project        </b></center></td>\n";
		echo "<td width='50' ><center><b> Build          </b></center></td>\n";
		echo "<td width='120'><center><b> Distribution   </b></center></td>\n";
		echo "<td width='120'><center><b> Autotester     </b></center></td>\n";
		echo "<td width='150'><center><b> Time started   </b></center></td>\n";
		echo "<td width='150'><center><b> Time finished  </b></center></td>\n";
		echo "<td width='300'><center><b> Comment        </b></center></td>\n";
		echo "<td width='130'><center><b> Duration       </b></center></td>\n";
		echo "</tr>\n";
		echo "<tr class='header2'>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b> (full log)     </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b> (distribution) </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "<td><center><b>                </b></center></td>\n";
		echo "</tr>\n";

		$filter_used = !empty($filter_product_id) || -1 != $filter_build_status || -1 != $filter_error_status;
	
		$ar_history    = $filter_used ? get_history() : get_history($count);
		$line_shown_nr = 0;
		$date_started  = date('Y-m-d');

		foreach ($ar_history as $history)
		{
			if ($count == $line_shown_nr)
				break;
			$id            = $history['id'];
			$product_id    = $history['product_id'];
			//$job_id        = $history['job_id'];
			$time_started  = $history['time_started'];
			$time_finished = $history['time_finished'];
			$time_AT       = $history['time_AT'];
			$duration      = $history['duration'];
			$build_nr      = $history['build_nr'];
			$error_status  = $history['error_status'];
			$user_comment  = $history['user_comment'];
			$distr_path    = $history['distr_path'];
			$build_status  = floor($error_status / 10);
			$error_status  = $error_status % 10;
			$def_color     = (EErrorStatus::ERROR == $error_status || 0 != $build_status) ? 'silver' : 'black';

			if (!empty($filter_product_id) && $filter_product_id != $product_id)
				continue;

			if (-1 != $filter_build_status && "$filter_build_status" != "$build_status")
				continue;

			if (-1 != $filter_error_status && "$filter_error_status" != "$error_status")
				continue;

			$time_started      = timestamp_remove_debug_flag($time_started);
			$prev_date_started = $date_started;
			$date_started      = date("Y-m-d", strtotime($time_started));

			if (0 == $build_status)
				$build_status_text = '';
			elseif (1 == $build_status)
				$build_status_text = 'halted';
			elseif (2 == $build_status)
				$build_status_text = 'died';
			else
				$build_status_text = "unknown$build_status";

			$link = trim($distr_path);
			$span = format_time($duration);
			
			get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script);

			//STYLE
			$evenodd = 'evenodd';
			$evenodd .= $line_shown_nr++ % 2; 
			$evenodd .= ($prev_date_started != $date_started) ? 'b' : 'n';
			echo "\n\n<tr class='row'>";
			
			echo "<td class='{$evenodd}'><font color='$def_color'>$id</font></td>\n";
			
			//echo "<td class='{$evenodd}'><font color='$def_color'></font></td>\n";
			echo "<td class='{$evenodd}'><input type='checkbox' name='$time_started'/></td>\n";

			echo "<td class='{$evenodd}'><font color='$def_color'>$product_name</font></td>\n";
			echo "<td class='{$evenodd}'><font color='$def_color'>$build_nr</font></td>\n";

			//time -> distr
			
			//Distribution -> build log
			
			$product_dir = get_product_dir($time_started);
			$link_log    = is_dir("$product_dir") ? "../_Main/show_log.php?time=$time_started" : NULL;

			$color = 'lightgreen';
			$text = '';
			if (empty($build_status_text) || ('halted' == $build_status_text && EErrorStatus::ERROR == $error_status))
			{
				switch ($error_status)
				{
				case EErrorStatus::ERROR: 
					//if (!empty($link_log))
					//	echo    "<a href='$link_log'> <font color='#AA2222'> ERROR </font> </a>\n";
					//else
					//	echo    "<font color='#AA2222'> error </font>\n";
					$color = 'red';
					$text  = 'error';
					break;
					
				case EErrorStatus::WARNING:
					//if (!empty($link_log))
					//	echo    "<a href='$link_log'> <font color='#FF8000'> WARNING </font> </a>\n";
					//else
					//	echo    "<font color='#FF8000'> warning </font>\n";
					$color = 'orange';
					$text  = 'warning';
					break;

				default:
					$color = empty($build_status_text) ? 'lightgreen' : 'magenta';
					
					//if (!empty($link_log))
					//	echo    "<a href='$link_log'> <font color='$color'> OK </font> </a>\n";
					//else
					//	echo    "<font color='$color'> ok </font>\n";
					$text  = 'ok';
					break;
				}	
			}
			else
			{
				//if (!empty($link_log))
				//	echo    "<a href='$link_log'> <font color='magenta'> " . strtoupper($build_status_text) . "</font> </a>\n";
				//else
				//	echo "<font color=magenta> $build_status_text </font>\n";
				$text  = 'ok';
				$color = 'magenta';
			}
			echo "<td bgcolor='{$color}'><center>";
			if ($link_log)
				echo "<a href='$link_log'> " . strtoupper($text) . "</a>\n";
			else
				echo "<a> {$text}</a>\n";
			echo "</center></td>";
			
				//if (!empty($link))
				//	echo    "<td><a href='$link' target='_blank'> <center><font color='#FF8000'> WARNING </font></center> </a></td>\n";
				//else
			//../AT/autotester_log.php?rundate=2011-01-06%2010:54:53
			
			//$timeAT = $time_AT;
			//if (!empty($timeAT))
			//{
			//	$timeAT[13] = ':';
			//	$timeAT[16] = ':';
			//	$timeAT  = str_replace('_', "%20", "$timeAT");
			//}
			//$link_AT = "../_AT/autotester_log.php?id=$timeAT";
			//echo "<td><a href='$link_AT'> <center><font color='#22AA22'>[N/A]</font></center> </a></td>\n";
			
			
			$exists = get_autotester_data_by_build_started($time_started, $AT_id, $AT_product, $AT_branch, $AT_errors, $AT_tests_failed, $AT_tests_count);
			if ($exists)
			{
				$AT_link = "../_AT/autotester_log.php?id={$AT_id}";
				$AT_color  = (0 < $AT_errors) ? 'red' : 'lightgreen';
				$AT_status = "{$AT_errors} [{$AT_tests_failed}/{$AT_tests_count}]";;
				echo "<td bgcolor='$AT_color'><a href='$AT_link'> <center><font >{$AT_status}</font></center> </a></td>\n";
			}
			else
			{
				echo "<td class='{$evenodd}'> </td>\n";
			}
			
			//echo    "<td><center><font color='#AA2222'>n/a</font></center></td>\n";

			if (!empty($link))
				echo    "<td class='{$evenodd}'><a href='$link'>$time_started</a></td>\n";
			else
				echo    "<td class='{$evenodd}'><font color='$def_color'>$time_started</font></td>\n";

			echo    "<td class='{$evenodd}'><font color='$def_color'>$time_finished</font></td>\n";
			
			$user_comment = CorrectUserComment($user_comment);
			echo "<td class='{$evenodd}'><font color='gray'>$user_comment</font> </td>\n";
			echo "<td class='{$evenodd}' align=right><font color='$def_color'>$span</font></td>\n";

			echo "</tr>\n\n";
		}
		echo "</table>\n<br/>";
		echo "</form>\n";

		$filter_product_id   = isset($_GET['filter_product_id'])   ? $_GET['filter_product_id']   : '';
		$filter_error_status = isset($_GET['filter_error_status']) ? $_GET['filter_error_status'] : '';
		$filter_build_status = isset($_GET['filter_build_status']) ? $_GET['filter_build_status'] : '';
		echo "<hr/>";
		if (-1 != $count)
			echo "<a href='?filter_product_id={$filter_product_id}&filter_error_status={$filter_error_status}&filter_build_status={$filter_build_status}&count=-1'>Show all</a>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
	}
	
	protected function GenerateHeadData() 
	{
		echo "<style type='text/css'>";

		echo "td.evenodd0n { background-color: white; color: black;   }";
		echo "td.evenodd1n { background-color: #E0FFFF; color: black; }";
		
		echo "td.evenodd0b { background-color: white;   color: black; border-top: 1px solid black;}";
		echo "td.evenodd1b { background-color: #E0FFFF; color: black; border-top: 1px solid black;}";

		echo "input[name='count'] { width:50px; }";

		echo "tr           td   { border-style:dotted; border-color:lightgray; border-width: 1px; }";
		echo "tr.header1   td   { background-color: lightgray; }";
		echo "tr.header2   td   { background-color: lightgray; }";
		echo "tr.row:hover td   { background-color: lightblue !important;; }";
		echo "tr.row:hover font { color: black !important; }";
		echo "</style>";
	}
}

$gen_page = new CGenerateHistoryPage();
$gen_page->Generate();

?>

