<?php

//*1 Mail(to, subject, body)
//* -------------
//*2 to      - List of mail recipients
//*2 subject - Mail subject
//*2 body    - Mail body
//* -------------
//*3 Send mail

$to           = $cmd_params[0];
$subject      = $cmd_params[1];
$body         = $cmd_params[2];
$product_id   = $sys_params['product_id'];
$time_started = $sys_params['time_started'];

$to = trim($to);
if (0 == strlen($to) || !strpos($to, '@'))
{
	_log_to($command_log, "No valid recipients");
	return;
}

//$errstatus = checkErrorsInternal($db, $ID);
//echo ">>".$ID."<<";

//if ($errstatus > 0)
//{
	$summary = ProductErrorSummary($product_id, $time_started);
//}
//else
//{
//	$summary = "No warnings";
//}


$body = str_replace("@n@", "\n", $body);
$body = str_replace("@summary@", $summary, $body);

$headers = 'From: mxkbuilder@matrix-software.lt' . "\r\n" .
		'Reply-To: g.luzys@matrix-software.lt' . "\r\n";

mail($to, $subject, $body, $headers);

_log_to($command_log, "");
_log_to($command_log, "[$to]");
_log_to($command_log, "");
_log_to($command_log, "*****************************************\n\n");
_log_to($command_log, "");
_log_to($command_log, "[$subject]");
_log_to($command_log, "");
_log_to($command_log, "*****************************************\n\n");
_log_to($command_log, "");
_log_to($command_log, "$body");
_log_to($command_log, "");
_log_to($command_log, "*****************************************\n\n");

?>