<?php
require_once("../conf/conf_fnc.php");
require_once("../tools/file_tools.php");
require_once("../tools/builder_script_fnc.php");
require_once("../tools/builder_sys_fnc.php");
require_once("../db/builder_db_jobs_fnc.php");

function add_night_builds($bcomment)
{
	if (loglevel('daemon')) _log("Adding night builds");
	
	$curr_date     = GetSysDate();
	$curr_time     = GetSysTime();
	$curr_weekday  = date('N'); //ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) 	1 (for Monday) through 7 (for Sunday)
	$ar_product    = glob('../scripts/*.xml');
	$scheduled_ids = Array();

	foreach ($ar_product as $product_xml) if (strlen($product_xml))
	{
		$product_id = basename($product_xml, '.xml');

		if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script, $product_info))
			continue;

		if (!($product_night & pow(2, $curr_weekday-1)))
			continue;
		
		$priority = empty($product_info['priority']) ? 0 : intval($product_info['priority']);
		if (!isset($scheduled_ids[$priority]))
			$scheduled_ids[$priority] = Array();
		$scheduled_ids[$priority][$product_id] = $product_id;
	}

	krsort($scheduled_ids);
	foreach ($scheduled_ids as $order_ids)
	{
		ksort($order_ids);
		foreach ($order_ids as $product_id)
		{
			$curr_datetime = GetSysDateTime2($curr_date, $curr_time);

			if (add_job($product_id, $curr_datetime, $bcomment))
			{
				if (loglevel('daemon')) _log("Added night-job: $product_id");
			}
			else
			{
				if (loglevel('daemon')) _log("WARNING: Failed to add night-job: $product_id");
			}

			if (loglevel('daemon')) _log("Adding night job: $product_id");
		}
	}
}

?>