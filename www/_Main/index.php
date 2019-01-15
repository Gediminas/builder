<?php
require_once("../conf/conf_fnc.php");
echo header(autoscroll() ? "Refresh: 5" : "Refresh: 120");

$bcomment  = false;
$user_ip   = false;
$user_host = false;

require_once("../common/header.php");
class CGenerateMainPage extends CGeneratePage
{
	protected function GenerateModule() 
	{
		require_once("../conf/conf_fnc.php");
		require_once("../tools/file_tools.php");
		require_once("../conf/conf_fnc.php");
		require_once("../tools/builder_script_fnc.php");
		require_once("../tools/date_time.php");
		require_once("../db/builder_db_jobs_fnc.php");
		require_once("../db/builder_db_params_fnc.php");
		require_once("../_History/builder_db_history_fnc.php");
	
		global $bcomment;
		global $user_ip;
		global $user_host;
		
		$jobs = get_all_jobs();
		$launched_products = Array();
		
		$all      = isset($_GET['all'])      ? $_GET['all']      : 0; 
		$bcomment = isset($_GET['bcomment']) ? $_GET['bcomment'] : false;

		global $uip;
		$user_ip   = $uip;
		$user_host = gethostbyaddr($user_ip);
		//echo "IP: $user_ip, HOST: $user_host<br/>";

		if (!$bcomment) 
		{
			$tmp       = explode(".", $user_host);
			$bcomment = $tmp[0] . ': ';
		}
		if (!$bcomment) 
		{
			$bcomment = "[$user_ip]";
		}

		$current_duration = 0;
		$total_estimation = 0;
	
	echo "<form name=user_comment>\n";
	echo "          <INPUT type='hidden' name='all'      value='" . $all                    . "'> \n";
	echo "User comment: <INPUT type='text'   name='bcomment' value='" . rawurldecode($bcomment) . "' size=150 onkeyup='sync_bcomment()'> \n";
	echo "<br/> \n";
	echo "</form>\n";
	
		echo "\n<table name='job_queue_table' border=0>\n";
		foreach($jobs as $job)
		{
			$job_id             = $job['id'];
			$product_id         = $job['product'];
			$worker_id          = $job['worker'];
			$time_added_started = $job['time_added'];
			$product_bcomment   = $job['comment'];
			$order_nr           = $job['order_nr'];

			$product_bcomment = CorrectUserComment($product_bcomment);

			$launched_products[$product_id] = 1;

			if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
				continue;
			
			$product_time     = "n/a";//$row['time_last'];
			echo "<tr class='queue'>\n";
			
			if (empty($worker_id))
			{
				echo "<td width=10><font size=-2>&nbsp</font></td>\n";
			}
			else
			{
				$worker_id_abs = abs($worker_id);
				$log = realpath(get_worker_log($worker_id_abs));
				echo "<td width=10> <a href='show_file.php?fname=$log'><font size=-2>#$worker_id_abs</font></a> </td>\n";
			}

			$product_status        = '';
			$duration      = '---';
			$product_status_txt = '';
			$last_duration = get_param('product_span', $product_id);
			$total_estimation += $last_duration;
			$last_duration = $last_duration ? format_time($last_duration) : '---';
			$cmd = '';
			$lll = '';
			$b_high = $b_up = $b_down = $b_low = FALSE;
			
			if (0 < $worker_id)
			{
				$halt = get_param("worker$worker_id", 'halt_user', NULL);
				
				if ($halt)
				{
					$product_status = "halting";
				}
				else
				{
					if (0 < $worker_id)
					{
						$time_added_started = get_param("worker$worker_id", 'time_started');
						$ln_count           = get_param("worker$worker_id", 'ln_count');
						$ln_curr            = get_param("worker$worker_id", 'ln_curr') + 1;
						$progress           = (0 == $ln_count) ? 0 : round(100 * $ln_curr / $ln_count); 
						
						$command_log        = GetLastLog($time_added_started);
						$line_count         = GetMainAndSubLogLineCount($command_log, $sub_line_count);
						$product_status     = "[$ln_curr/$ln_count]";

						$product_dir        = get_product_dir($time_added_started);
						$error              = GetFlag($product_dir, 'error');
						$warning            = GetFlag($product_dir, 'warning');
						$color              = $error ? 'red' : ($warning ? 'orange' : 'green');

						$time_added_started = trim($time_added_started, '_');
						$time_started_int   = strtotime($time_added_started);
						$duration           = time() - $time_started_int;
						$current_duration   = $duration;
						$duration           = format_time($duration);
						
						$time_added_started = "<font color='black'> $time_added_started </font>";
						
						$product_status_txt = "<font size='-2' color='$color'> &nbsp;#$line_count </font>";

						if (0 < $sub_line_count)
						{
							$last_log_lines = file($command_log);
							$command = $last_log_lines[0];
							$tmp1 = explode(': ', $command, 2);
							//$time = isset($tmp1[1]) ? "$tmp1[0]" : "";
							$cmd = (isset($tmp1[1]) ? $tmp1[1] : $tmp1[0]);
							$lll = "{$last_log_lines[$line_count-1]}";
							$product_status = "<abbr title='CURRENT COMMAND: {$cmd}'> $product_status </abbr>";
							$product_status_txt = "<abbr title='LAST LOG LINE: {$lll}'> $product_status_txt </abbr>";
						}
					}
				}
				
				echo "<td width=75 align='left'> <font size='-2'> $product_status </font> $product_status_txt</td>\n";
			}
			elseif ($worker_id < 0)
			{
				$product_status   = 'starting';
				echo "<td width=75 align='left'> <font size='-2'> $product_status </font> $product_status_txt</td>\n";
			}
			else
			{
				switch ($order_nr)
				{
				case -1:
					break;
				case 0:
					$product_status_txt = "<font size=-2 color='red'>high</font>";
					$b_down = $b_low = TRUE;
					break;
				case 9999:
					$product_status_txt = "<font size=-2 color='blue'>low</font>";
					$b_high = $b_up = TRUE;
					break;
				default:
					$b_high = $b_up = $b_down = $b_low = TRUE;
					break;
				}
				
				//window.location.href=\"job_change_order.php\"
				echo "<td width=75 align='right'>";
				//echo "<button  onclick='alert(\'aaaa\');'>^</button>\n";
				echo "$product_status_txt";
				echo "</td>\n";
			}
			
			$additional_info = empty($product_mutex) ? "" : "<font size='-2' color='gray'>[$product_mutex]</font>";
			
			echo "<td width=200 align='left'  > <font size='-1'><nobr> <a href='../_Main/show_log.php?id=$product_id&all=$all&bcomment=" . rawurlencode($bcomment) . "'>{$product_name}</a> {$additional_info}</nobr></font> </td>\n";


			echo "<td width=120>";
			echo "<nobr>";
			echo "<form name='job_remove' action='' onsubmit='document.user_comment.submit();'>\n";
			//echo "<td width=10 ><center><font size='-1'> ";
			//echo $order_nr; //DEBUG
			echo "<INPUT type='hidden' name='all'      value='" . $all                    . "'> \n";
			echo "<INPUT type='hidden' name='bcomment' value='" . rawurldecode($bcomment) . "'> \n";
			echo "<INPUT type='hidden' name='job_id'   value='" . $job_id                 . "'> \n";
			echo "<INPUT type='hidden' name='param'    value='aaa'> \n";
			
			$b_high = $b_high ? '' : 'disabled';
			$b_up   = $b_up   ? '' : 'disabled';
			$b_down = $b_down ? '' : 'disabled';
			$b_low  = $b_low  ? '' : 'disabled';

			echo "<INPUT type='button' style='height: 20px; width: 20px; font-size: 10px;'           value='-' onclick=\"FORM_ACTION('job_remove.php',       '');     submit(); \">\n";
			echo "<INPUT type='button' style='height: 20px; width: 20px; font-size: 10px;' {$b_high} value='!' onClick=\"FORM_ACTION('job_change_order.php', 'high'); submit(); \">";
			echo "<INPUT type='button' style='height: 20px; width: 20px; font-size: 10px;' {$b_up}   value='^' onClick=\"FORM_ACTION('job_change_order.php', 'up');   submit(); \">";
			echo "<INPUT type='button' style='height: 20px; width: 20px; font-size: 10px;' {$b_down} value='v' onClick=\"FORM_ACTION('job_change_order.php', 'down'); submit(); \">";
			echo "<INPUT type='button' style='height: 20px; width: 20px; font-size: 10px;' {$b_low}  value='_' onClick=\"FORM_ACTION('job_change_order.php', 'low');  submit(); \">";
			echo "</form>\n";
			echo "</nobr>";
			echo "</td>";

			echo "<td width= 80 align='right' > <i> <font size='-1' color='black'><nobr> $duration /        </nobr></font></i> </td>\n";
			echo "<td width= 80 align='right' > <i> <font size='-1' color='grey'><nobr> $last_duration      </nobr></font></i> </td>\n";
			echo "<td width=150 align='center'> <i> <font size='-1' color='grey'><nobr> $time_added_started </nobr></font></i> </td>\n";
			echo "<td align='left'  > <i> <font size='-1' color='black'><nobr> $product_bcomment   </nobr></font></i> </td>\n";

			//echo "<td width=75> <font size='-2'> $product_status </font> $product_status_txt</td>\n";
			//echo "<td align='left'  > <i> <font size='-3' color='grey'><nobr> $cmd   </nobr></font></i> </td>\n";
			//echo "<td align='left'  > <i> <font size='-3' color='lightgreen'><nobr> $lll   </nobr></font></i> </td>\n";
			
			echo "</tr>\n";
		}

		//if (1 < count($jobs) && 1 == worker_count())
		if (worker_count() < count($jobs))
		{
			$total_estimation -= $current_duration;
			$curr_time         = time();
			$end_time          = date('Y-m-d H:i:s', $curr_time + $total_estimation);
			
			$total_estimation = $total_estimation ? format_time($total_estimation) : '---';
			
			echo "<tr class='queue'>\n";
			//echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td> <font size='-1'>  </font> </td>\n";
			
			if (1 < worker_count())
				echo "<td align='right' > <b> <font size='-1' color='black'> <nobr> ESTIMATION (for 1 build thread):       </nobr> </font></b> </td>\n";
			else
				echo "<td align='right' > <b> <font size='-1' color='black'> <nobr> ESTIMATION:       </nobr> </font></b> </td>\n";
				
			echo "<td align='right' > <b> <font size='-1' color='black'> <nobr> $total_estimation </nobr> </font></b> </td>\n";
			echo "<td align='center'> <b> <font size='-1' color='black'> <nobr> $end_time         </nobr> </font></b> </td>\n";
			echo "<td align='left'  > <i> <font size='-1' color='grey'>    </font></i> </td>\n";
			
			echo "</tr>\n";
		}
	
		echo "</table>\n";
		echo "<br/>\n";
		
		
		
		
		echo    "<table border='0' cellspacing='0'>\n";
	echo    "<tr class='header'>\n";
	echo    "     <td align='center'>  </td>\n";
	echo    "     <td align='center'> <b> Product </b> </td>\n";
	echo    "     <td align='center'> <b> Status  </b> </td>\n";
	echo    "     <td align='center'> <b> Autotester  </b> </td>\n";
	echo    "     <td align='center'> <b> Night   </b> </td>\n";
	echo    "     <td align='center'> <b> prio       </b> </td>\n";
	echo    "     <td align='center'> <b> Span    </b> </td>\n";
	echo    "     <td align='center'> <b> Last    </b> </td>\n";
	//echo    "     <td align='center'> <b> Description </b> </td>\n";
	echo    "     <td align='left'> <b> User comment  </b> </td>\n";
	echo "</tr>\n";

	// All available projects

//FIXME: reuse get_script_names()
	$ar_product = glob("../scripts/*.xml"); 
	$ar_names     = Array();

	foreach ($ar_product as $product_xml) if (strlen($product_xml))
	{
		$product_id = basename($product_xml, '.xml');
		
		if (get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
			$ar_names[$product_id] = $product_name;
	}
	
	asort($ar_names);
//FIXME: END
			
	$line_nr = 0;
	$line_nr_hidden = 0;
	foreach ($ar_names as $product_id => $product_name)
	{
		if (!get_product_info($product_id, $product_xml, $product_name,  $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script, $product_info))
			continue;

		$hidden = (substr_compare($product_name, '_', 0, 1) == 0);

		if (!$hidden || $all)
		{
			$product_time         = get_param('product', $product_id);
			$product_dir          = get_product_dir($product_time);
			$time                 = strtotime($product_time);
			$days                 = $time ? (time() - $time)/(60*60*24) : -1;
			$time_ok              = $days < 15 || !$product_enabled || !$product_night;
			$product_status       = GetValue($product_dir, 'status');
			$product_user_comment = GetValue($product_dir, 'comment');
			$product_duration     = get_param('product_span', $product_id);
			$product_user_comment = CorrectUserComment($product_user_comment);
			$product_duration     = $product_duration     ? format_time($product_duration)       : "";
			
			$tr_class = 'results';
			$td_style = 'd' . $line_nr++ % 2; 
			
			if ($hidden && 0 == $line_nr_hidden ++) {
				$tr_class .= $all ? " tr_up_separator" : " tr_up_separator_forced";
			}

			echo "\n\n<tr class='{$tr_class}'>";

			// start build button
			if ($product_enabled && strlen($product_script) > 5)
			{
				$btn_class = isset($launched_products[$product_id]) ? 'job_added' : 'job_notadded';
				
				echo "<form name='job_add' action='../_Main/job_add.php'  onsubmit='document.user_comment.submit()'>\n";
				echo "<td class='$td_style'><nobr><center>";
				echo "<INPUT type='hidden' name='all'        value='" . $all                    . "'> \n";
				echo "<INPUT type='hidden' name='bcomment'   value='" . rawurldecode($bcomment) . "'> \n";
				echo "<INPUT type='hidden' name='product_id' value='" . $product_id             . "'> \n";
				echo "<INPUT class='$btn_class' type='button' value='+' onclick='submit()'>\n";
				echo "</center></nobr></td>";
				echo "</form>\n";
			}	
			else
			{
				echo "<td class='$td_style'><nobr><font size='-2' color='gray'><center>N/A</center></font></nobr></td>\n";
			}

			
			// project name & log:
			$pre_link    = "<a class='res_status' href=\"../_Main/show_log.php?id=$product_id&all=$all&bcomment=$bcomment\"> ";
			
			$color = NULL;
			switch ($product_status)
			{
				case 'building':  $color = "yellow";      break;
				case 'done':      $color = "lightgreen";  break;
				case 'halted':    $color = "magenta"; $product_status=strtoupper($product_status); break;
				case 'died':      $color = "red";     $product_status=strtoupper($product_status); break;
			}

			$tmp = explode('] ', $product_name);
			$branch = 1 < count($tmp) ? array_shift($tmp).'] ' : '';
			$name   = implode('] ', $tmp);
			$product_text = "<font size=-1 color='gray'>{$branch}</font> {$name}";
			
			echo "<td class='{$td_style}'> <nobr><a class='prod_name' href='../_Main/edit.php?id={$product_id}&all={$all}&bcomment={$bcomment}'>{$product_text}</a></nobr></td>\n";
			
			if (is_null($color))
			{
				echo "<td class='$td_style' align=center> <nobr><font size='-2' color='gray'> n/a </font> </nobr></td>";
			}
			elseif ($product_status != 'building')
			{
				$error = GetFlag($product_dir,  'error');
				if ($error)
				{
					$product_status = 'ERROR';
					$color  = 'red';
				}
				else
				{
					$warning = GetFlag($product_dir,  'warning');
					if ($warning)
					{
						$product_status = ($product_status == 'done') ? "WARNING" : "$product_status; warning";
						$color  = 'orange';
					}
					elseif ($product_status == 'done')
					{
						$product_status = "OK";
					}
				}
				
				echo "<td bgcolor='$color' align=center> <nobr> $pre_link <font size='-1' color='black'> $product_status </font> </a></nobr></td>";
			}
			else
			{
				echo "<td bgcolor='$color' align=center> <nobr> $pre_link <font size='-1' color='black'> $product_status </font> </a></nobr></td>";
			}
			
			// end of [log link]
			
			$bAT = get_autotester_data_by_build_started($product_time, $AT_id, $AT_product, $AT_branch, $AT_total_errors, $AT_tests_failed, $AT_tests_count);
			if (!empty($bAT))
			{
				$link = "../_AT/autotester_list.php?product={$AT_product}&branch=";
				echo (0 < $AT_total_errors) ? "<td bgcolor='red'        align=center> <nobr><a class='AT_status' href='$link'><b>{$AT_total_errors}</b> [{$AT_tests_failed}/{$AT_tests_count}] </a></nobr></td>"
											: "<td bgcolor='lightgreen' align=center> <nobr><a class='AT_status' href='$link'>   {$AT_total_errors} [{$AT_tests_failed}/{$AT_tests_count}] </a></nobr></td>";
			}
			else
			{
				$exists_prev = false;
				
				if ($product_status == 'building' || $product_status == 'ERROR')
				{
					$previous_builds = get_last_product_builds($product_id, 5);
					
					$time_started_prev = '';
					$AT_id_prev = $AT_product_prev = $AT_branch_prev = $AT_errors_prev = $AT_tests_failed_prev = $AT_tests_count_prev = 0;
					foreach ($previous_builds as $previous_build)
					{
						$time_started_prev = $previous_build['time_started'];
						$exists_prev = get_autotester_data_by_build_started($time_started_prev, $AT_id_prev, $AT_product_prev, $AT_branch_prev, $AT_errors_prev, $AT_tests_failed_prev, $AT_tests_count_prev);
						
						if ($exists_prev)
							break;
					}
				}
				
				if ($exists_prev)
				{
					//echo "<td bgcolor='yellow'> <center> ? </center> </td>\n";

					$link_prev = "../_AT/autotester_list.php?product={$AT_product_prev}&branch=";
					$color = ($product_status == 'building') ? 'yellow' : 'lightgray';//(0 < $AT_total_errors) ? 'red' : 'lightgreen';
					echo "<td class='AT_status_bld' bgcolor='$color' align=center> <nobr><a class='AT_status_bld' href='$link_prev'>   {$AT_errors_prev} [{$AT_tests_failed_prev}/{$AT_tests_count_prev}] </a></nobr></td>";
				}
				else
				{
					echo "<td class='$td_style'> <center> </center> </td>\n";
				}
			}				

			$product_night = $product_info['night_build'];
			$product_order = $product_info['priority'];
			//echo "$product_night ";
			echo "<td class='$td_style' align='center'>  <nobr> <font size=-5>";
			if (0 < $product_night)
			{
				for ($wday = 0; $wday < 7; ++ $wday)
				{
					if ($product_night & pow(2, $wday))
						echo "<INPUT type='checkbox' disabled checked value=true>\n";
					else
						echo "<INPUT type='checkbox' disabled value=false>\n";
						
					if ($wday == 4)
						echo " | ";
				}
			}
			echo "</font></nobr></td>\n";

			echo "<td align='right'>";
			if (0 < $product_night)
				echo "<font color='gray' size=-2>$product_order</font>";
			echo "</td>\n";

			$product_duration_link = "<a class='duration' href = '../_History/index.php?filter_product_id={$product_id}'> $product_duration </a>";
			$product_time_link_pre = "<a class='time'     href = '../_History/index.php?filter_product_id={$product_id}'>";
			
			echo "<td class='$td_style' align='right'>   <nobr><font size='-1'> $product_duration_link                          </font> </nobr></td>\n";

			if ($time)
			{
				if ($time_ok)
					echo "<td class='$td_style' align='center'> <nobr>$product_time_link_pre <font size='-1' style='color:gray'> $product_time </font> </a> </nobr></td>\n";
				elseif ($days < 30)
					echo "<td bgcolor='orange' align='center'> <nobr>$product_time_link_pre <font size='-1' style='color:black'> $product_time </font> </a> </nobr></td>\n";
				else
					echo "<td bgcolor='red'    align='center'> <nobr>$product_time_link_pre <font size='-1' style='color:black'> $product_time </font> </a> </nobr></td>\n";
			}
			elseif ($product_enabled)
				echo "<td class='$td_style'    align='center'> <nobr>$product_time_link_pre <font size='-1' style='color:gray'> $product_time </font> </a> </nobr></td>\n";
			else
				echo "<td class='$td_style'>                     </td>\n";

			//echo "<td class='$td_style'>                 <nobr><font size='-1' style='color:gray'> $product_comment </font> </nobr></td>\n";
			echo "<td class='$td_style' align='left'>    <nobr><font size='-1' style='color:gray'> $product_user_comment               </font> </nobr></td>\n";
			echo "<tr/>\n";
		}
	}
//<!---------------------------------------------------------------------------->
//<!-- BUILDS -->

	echo "</table>\n";
	echo "<hr />\n";

	echo "<table>\n";
	
	echo "<td width=100>\n";
	echo "<font size='-1'>";
	echo "<form name=change_all>\n";
	echo "<INPUT type='hidden' name='all'      value='" . ($all ? 0 : 1)            . "'> \n";
	echo "<INPUT type='hidden' name='bcomment' value='" . rawurldecode($bcomment)   . "'> \n";
	echo "<LABEL for=L_all> <INPUT type='checkbox' value=" . ($all ? "'1' checked" : "'0'") . " onclick='submit()' id=L_all>show all </LABEL>";
	//echo "<INPUT type='button' value='show all'  onclick='submit()'>\n";
	echo "</form>\n";
	echo "</font>";
	echo "</td>\n";

	echo "<td>\n";
	echo "<form name='job_add_night' action='../_Main/job_add_night.php'  onsubmit='document.user_comment.submit()'>\n";
	echo "<INPUT type='hidden' name='all'        value='" . $all                    . "'> \n";
	echo "<INPUT type='hidden' name='bcomment'   value='" . rawurldecode($bcomment) . "'> \n";
	echo "<INPUT type='button' value='Shedule night builds'  onclick='submit()'>\n";
	echo "</form>\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "<form name='job_remove_scheduled' action='../_Main/job_remove_scheduled.php'  onsubmit='document.user_comment.submit()'>\n";
	echo "<INPUT type='hidden' name='all'        value='" . $all                    . "'> \n";
	echo "<INPUT type='hidden' name='bcomment'   value='" . rawurldecode($bcomment) . "'> \n";
	echo "<INPUT type='button' value='Unschedule pending jobs'  onclick='submit()'>\n";
	echo "</form>\n";
	echo "</td>\n";

	echo "<td>\n";
	echo "<form name='create_project' action='../_Main/edit.php'  onsubmit='document.user_comment.submit()'>\n";
	echo "<INPUT type='button' value='Create new product'  onclick='submit()'>\n";
	echo "</form>\n";
	echo "</td>\n";
	
	echo "</table>\n";
	//echo "<hr />\n";
	}
	
	protected function GenerateHeadData() 
	{
		echo "<style type='text/css'>";

		echo "td { padding-left:5px; padding-right:5px; }";
		echo "td.d0 { background-color: white; color: black;   }";
		echo "td.d1 { background-color: #E0FFFF; color: black; }";
		
		
		echo "tr.header  td { border-style:solid;  border-color:gray; border-width: 1px; background-color:lightgray; }";
		echo "tr.results td { border-style:dotted; border-color:lightgray; border-width: 1px; }";
		echo "tr.tr_up_separator_forced  td { border-top-style:solid;  border-top-color:red; border-top-width: 2px; }";
		echo "tr.tr_up_separator         td { border-top-style:solid;  border-top-color:black; border-top-width: 1px; }";
		//echo "td.AT_status_bld { border-style:solid;  border-color:yellow; border-width: 1px; background-color:yellow; }";

		echo "input[class='job_added']    { height:20px; width:30px; font-size: 60%; margin:-5px; background-color: yellow; }";
		echo "input[class='job_notadded'] { height:20px; width:30px; font-size: 60%; margin:-5px; background-color: lightgray; }";
		
		echo "a.prod_name:link, a.prod_name:visited  { text-decoration: none; color:black; }";
		echo "a.res_status:link,a.res_status:visited { text-decoration: none; color:black; }";
		echo "a.AT_status:link, a.AT_status:visited  { text-decoration: none; color:black; }";
		echo "a.AT_status_bld:link, a.AT_status_bld:visited  { text-decoration: none; color:darkgray; }";
		echo "a.duration:link,  a.duration:visited   { text-decoration: none; color:black; }";
		echo "a.time:link,      a.time:visited       { text-decoration: none; color:black; }";
		
		
		echo "a.prod_name:hover  { text-decoration: underline; color:blue; }";
		echo "a.res_status:hover { text-decoration: underline; color:blue; }";
		echo "a.AT_status:hover  { text-decoration: underline; color:blue; }";
		echo "a.AT_status_bld:hover  { text-decoration: underline; color:blue; }";
		echo "a.duration:hover   { text-decoration: underline; color:blue; }";
		echo "a.time:hover       { text-decoration: underline; color:blue; }";

		echo "tr.queue:hover td   { background-color: lightblue; }";
		echo "tr.queue:hover font { color: black !important; }";

		echo "tr.results:hover td   { background-color: lightblue; }";
		echo "tr.results:hover font { color: black !important; }";
		
		echo "</style>";
	}
	
	protected function GenerateJsData()   
	{ 
		echo "<script>";
			echo "function sync_bcomment()";
			echo "{";
				echo "var bcomment = document.user_comment.bcomment;";
				echo "var forms = document.forms;";
				echo "for (var i = 0; i < forms.length; i ++)";
				echo "{";
						echo "var form = forms[i];";
						echo "if (form.name == 'job_add' || form.name == 'job_remove' || form.name == 'header_menu')";
						echo "form.bcomment.value = bcomment.value;";
				echo "}";
				echo "document.change_all.bcomment.value = bcomment.value;";
		echo "}";
		
		echo "
				function FORM_ACTION(action, param)
				{
					var forms = document.forms;
					for (var i = 0; i < forms.length; i ++)
					{
						var form = forms[i];
						if (form.name == 'job_remove')
						{
							form.action = action;
							form.param.value = param;
						}
					}
				}
			";

		echo "</script>";
	}
}

$gen_page = new CGenerateMainPage();
$gen_page->Generate();

?>


