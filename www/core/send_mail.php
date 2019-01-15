<?php
require_once ("../conf/conf_fnc.php");
require_once("../tools/builder_script_fnc.php");
require_once("../tools/check_errors.php");
require_once("../tools/date_time.php");
require_once("../tools/file_tools.php");
require_once("../db/builder_db_params_fnc.php");


function send_mail_on_start()
{
	//echo "SendMail: send_mail_on_start() - does nothing\n";
}

function send_mail_on_die($worker_id, $job)
{
	echo "SendMail: send_mail_on_die()\n";
	
	//$product_id   = get_param("worker$worker_id", 'product_id');
	//$time_started = $job['time_added'];
	//$product_dir  = get_product_dir($time_started);

	$product_id   = $job['product'];
	$user_comment = $job['comment'];
	$time_started = get_param("worker$worker_id", 'product_dir_time');
	$product_dir  = get_product_dir($time_started);

	$duration     = strtotime(GetSysDateTime()) - strtotime(timestamp_remove_debug_flag($time_started));
	$link_distr     = '-';
	$link_at        = '-';
	
	get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script);

	$log = '';
	send_mail($product_mailto, $product_id, $product_name, $time_started, $product_dir, $user_comment, $duration, $link_distr, $link_at, '-', NULL, $log);
	echo $log;
}

function send_mail_on_finish($sys_params, $last_command_log)
{
	_log_to($last_command_log, "SendMail");
	
	$product_id     = $sys_params['product_id'];
	$product_name   = $sys_params['product_name'];
	$product_mailto = $sys_params['product_mailto'];
	$time_started   = $sys_params['time_started'];
	$product_dir    = $sys_params['product_dir'];
	$user_comment   = $sys_params['user_comment'];
	$duration       = $sys_params['duration'];
	$build          = $sys_params['build_nr'];
	$link_distr     = '-';
	$link_at        = '-';
	
	if (isset($sys_params['distr_path']))
	{
		$link_distr = trim($sys_params['distr_path']);
		$link_distr = "<a href='$link_distr'> $link_distr </a>";
	}
		
	_log_to($last_command_log, "mailto={$product_mailto}");
	
	$AT_status = "";
	{
		$exists = get_autotester_data_by_build_started($time_started, $AT_id, $AT_product, $AT_branch, $AT_errors, $AT_tests_failed, $AT_tests_count);

		if ($exists)
		{
			_log_to($last_command_log, "AT_product={$AT_product}");
			_log_to($last_command_log, "AT_errors={$AT_errors}");
			
			$AT_status = ($AT_errors == 0 ? 'OK - ' : 'FAILED - ') . "{$AT_errors} errors [{$AT_tests_failed}/{$AT_tests_count}] tests failed";;

			$ip        = get_ip();
			$link_at   = "http://{$ip}/_AT/autotester_list.php?product={$AT_product}";
			$link_at   = "<a href='$link_at' > $link_at  </a>";
		}
	}

	_log_to($last_command_log, "AT_status={$AT_status}");

	$log = "\n";
	$sent = send_mail($product_mailto, $product_id, $product_name, $time_started, $product_dir, $user_comment, $duration, $link_distr, $link_at, $build, $AT_status, $log);
	_log_to($last_command_log, $log);

	_log_to($last_command_log, ($sent ? "[DONE]\n" : "[FAILED]"));
	
	return $sent;
}

function send_mail($product_mailto, $product_id, $product_name, $time_started, $product_dir, $user_comment, $duration, $link_distr, $link_at, $build, $AT_status, &$log)
{
	if (empty($product_mailto))
	{
		$log .= "SendMail: mailto empty, exiting...\n";
		return true;
	}
	
	$error_status   = GetFlag($product_dir, 'error')   ? 'Error' : (GetFlag("$product_dir", 'warning') ? 'Warning' : 'OK');
	$build_status   = GetValue($product_dir, 'status');
	$build_status   = strtoupper($build_status);
	$duration       = format_time($duration);
	
	if ('DIED' == $build_status)                                $status = 'DIED';
	elseif ('HALTED' == $build_status && 'OK' == $error_status) $status = 'Halted';
	else                                                        $status = $error_status;
	
	switch ($status)
	{
	case 'DIED':
	case 'Error':   $status_frmt = "<td bgcolor='#FF4444'> <b> $status </b> </td>"; break;
	case 'Warning': $status_frmt = "<td bgcolor='#FF8000'> <b> $status </b> </td>"; break;
	case 'OK':      $status_frmt = "<td bgcolor='#00F000'> <b> $status </b> </td>"; break;
	case 'Halted':  $status_frmt = "<td bgcolor='magenta'> <b> $status </b> </td>"; break;
	default:        $status_frmt = "<td bgcolor='yellow'>  <b> $status </b> </td>"; break;
	}

	$ip             = get_ip();
	$host           = host();
	$link_log       = "http://$ip/_Main/show_log.php?time=$time_started";
	$link_log       = "<a href='$link_log'> $link_log </a>";
	$summary        = ProductErrorSummary($product_id, $time_started);
	$summary        = str_replace("\n", "<br />", $summary);
	$summary        = str_replace('---------', "<hr />\n", $summary);
	
	$pre         = (empty($AT_status) || $AT_status[0] == 'O') ? $status : 'AT-Failed';
	$subject     = setting('simple_mail_subject') ? "$pre: $product_name" : "$pre: $product_name [$ip / $host]";
	
	$log .= "SendMail: status AT={$AT_status}\n";
	$log .= "SendMail: status Build={$status}\n";
	$log .= "SendMail: status Final={$pre}\n";

	$body    = "
				<html>
				<head> <title>Not-estimated bugs</title> </head>
				<body>
				<table border=1>
				<tr> <td> Product  </td> <td> <b> $product_name </b> ($product_id) </td> </tr>
				<tr> <td> Build    </td> <td> <b> $build                      </b> </td> </tr>
				<tr> <td> Status   </td>          $status_frmt                           </tr>
				<tr> <td> Comment  </td> <td> <b> $user_comment               </b> </td> </tr>
				<tr> <td> DISTR.   </td> <td> <b> $link_distr                 </b> </td> </tr>
				<tr> <td> IP/Host  </td> <td> $ip / $host                          </td> </tr>
				<tr> <td> Log      </td> <td> $link_log                            </td> </tr>
				";
				
	if (!empty($AT_status))
	{
		$color = $AT_status[0] == 'O' ? '#00F000' : '#FF4444';
		$body.="<tr> <td> AT       </td> <td> $link_at                             </td> </tr>
			    <tr> <td> AT result </td> <td bgcolor='$color'> <b> $AT_status </b> </td> </tr>
			   ";
	}
			   
	$body   .= "<tr> <td> Duration </td> <td> $duration                            </td> </tr>
				</table>
				<hr /> <br />
				<font size=-1>
				$summary
				</font>
				</body>
				</html>
				";
				//http://localhost/_AT/autotester_log.php?rundate=2011-04-08%2003:13:08
	
	$headers =	'From: mxkbuilder@Matrix-Software.lt'         . "\r\n" .
				'Reply-To: g.luzys@matrix-software.lt'        . "\r\n" .
				'X-Mailer: PHP/' . phpversion()               . "\r\n" .
				'MIME-Version: 1.0'                           . "\r\n" .
				'Content-Type: text/html; charset = "UTF-8"'  . "\r\n";
		
	$sent = mail($product_mailto, $subject, $body, $headers);
	
	if ($sent)
	{
		$log .= "SendMail: OK (mail was successfully accepted for delivery)\n";
	}
	else
	{
		$log .= "SendMail: FAILED (mail was NOT accepted for delivery)\n";
		$log .= "WARNING: SendMail() failed";
	}
	
	return $sent;
}
?>