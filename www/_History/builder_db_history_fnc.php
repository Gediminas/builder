<?php

require_once("builder_db_history.php");

function get_history($limit=NULL)
{
	$query  = "SELECT * FROM history ORDER BY id DESC";
	
	if (!is_null($limit))
		$query  .= " LIMIT $limit";
		
	return builder_db_history::_query($query);
}

function add_history($job_id, $product_id, $time_started, $time_finished, $time_AT, $duration, $build_nr, $error_status, $distr_path, $user_comment)
{
	$result = builder_db_history::_exec("INSERT INTO 
		history ( 'job_id', 'product_id',  'time_started',  'time_finished',  'time_AT',  'duration',  'build_nr',  'error_status',  'distr_path',  'user_comment')
		VALUES  ('$job_id', '$product_id', '$time_started', '$time_finished', '$time_AT', '$duration', '$build_nr', '$error_status', '$distr_path', '$user_comment')");
	assert(1 == $result);
	return (1 == $result);
}

function get_last_product_builds($product_id, $count)
{
	$query  = "SELECT * FROM history WHERE product_id='{$product_id}' ORDER BY id DESC LIMIT {$count}";
	return builder_db_history::_query($query);
}

function get_history_product_by_build_started($started_time)
{
	$query  = "SELECT * FROM history WHERE time_started='{$started_time}'";
	return builder_db_history::_query1($query);
}


?>