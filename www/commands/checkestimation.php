<?php

//*1 CheckEstimation()
//* -------------
//*2 product_name - Bugzilla product name to check
//*2 milestone    - Bugzilla target-milestone,  ''  - all (default value)
//*2 week_day     - Send only on that week day, '0' - every day (default value)
//*2 supervisors  - Supervisors mail list,      ''  - default value
//* -------------
//*3 Check bugzilla estimation. Checks all product versions and milestones. If not estimated bug found mail is sent to the assignee

$product_name = $cmd_params[0];
$milestone    = isset($cmd_params[1]) ? $cmd_params[1] : '';
$week_day     = isset($cmd_params[2]) ? $cmd_params[2] : 0;
$supervisors  = isset($cmd_params[3]) ? $cmd_params[3] : '';

$supervisors = str_replace(';', ',', $supervisors);

require_once("../tools/log.php");
require_once("../_Bugzilla/bugs_no_estimation.php");

global $ob_file;
$ob_file = fopen($command_log,'a');
ob_start('ob_file_callback');

$today_week_day = date('N');

echo "Today is $today_week_day week day [1 - Monday ... 7 - Sunday]\n";
echo "Check sheduled to run on  $week_day [0 - everyday, 1 - Monday ... 7 - Sunday]\n";

if (0 == $week_day || $today_week_day == $week_day)
{
	echo "CHECKING\n\n";
	
	bugs_no_estimation_notify($product_name, $milestone, $supervisors, $week_day, true);
}
else
{
	echo "TODAY WAS SKIPPED...\n";
}

ob_end_flush();

_log_to($command_log, "DONE");

?>