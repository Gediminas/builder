<?php

require_once("builder_db_jobs.php");

const JOB_ORDER = " ORDER BY order_nr, worker, id";

function get_all_jobs()
{
	return builder_db_jobs::_query("SELECT * FROM jobs " . JOB_ORDER);
}

function get_job($job_id)
{
	return builder_db_jobs::_query1("SELECT * FROM jobs WHERE id = $job_id");
}

function get_job_by_worker($worker_id)
{
	assert(0 < $worker_id);
	return builder_db_jobs::_query1("SELECT * FROM jobs WHERE worker = $worker_id");
}

function get_busy_jobs()
{
	return builder_db_jobs::_query("SELECT * FROM jobs WHERE worker <> 0 OR order_nr < 0" . JOB_ORDER);
}

function get_free_jobs()
{
	return builder_db_jobs::_query("SELECT * FROM jobs WHERE worker = 0 AND -1 < order_nr" . JOB_ORDER);
}

function get_first_free_job()
{
	return builder_db_jobs::_query1("SELECT * FROM jobs WHERE worker = 0 AND -1 < order_nr" . JOB_ORDER);
}

function get_first_reserved_job()
{
	return builder_db_jobs::_query1("SELECT * FROM jobs WHERE worker < 0 " . JOB_ORDER);
}

function get_job_next_order_nr()
{
	$res1 = builder_db_jobs::_query1("SELECT max(order_nr) FROM jobs WHERE order_nr<>9999" . JOB_ORDER);
	$order_nr = $res1[0] + 1;
	
	if ($order_nr < 1)
		$order_nr = 1;
		
	return $order_nr;
}

function add_job($product_id, $time_added, $user_comment)
{
	$order_nr = get_job_next_order_nr();
		
	$result = builder_db_jobs::_exec("INSERT INTO jobs ('product',     'time_added', 'order_nr', 'comment') " .
									           "VALUES ('$product_id', '$time_added', $order_nr, '$user_comment')");
	//assert(1 == $result);
	return (1 == $result);
}

function reserve_job_for_worker($job_id, $worker_id)
{
	assert(0 < $worker_id);
	$count = builder_db_jobs::_exec("UPDATE jobs SET worker=-$worker_id, order_nr=-1 WHERE id = $job_id AND worker = 0");
	assert(1 == $count);
	reorder();
	return $count;
}

function remove_not_started_jobs()
{
	$count = builder_db_jobs::_exec("DELETE FROM jobs WHERE worker < 0");
	reorder();
	return $count;
}

function accept_job_by_worker($worker_id, &$job_id)
{
	$updated = builder_db_jobs::_exec("UPDATE jobs SET worker=$worker_id, order_nr=-1 WHERE worker = -$worker_id");

	if (1 == $updated)
	{
		$queue    = builder_db_jobs::_query1("SELECT id FROM jobs WHERE worker = $worker_id");
		$job_id = $queue['id'];
		reorder();
	}
	
	return (1 == $updated) && (0 < $job_id);//FIXME
}

function remove_job($job_id)
{
	$result = builder_db_jobs::_exec("DELETE FROM jobs WHERE id = $job_id");
	assert(0 == $result || 1 == $result);
	reorder();
}

function remove_job_by_worker($worker_id)
{
	assert(0 < $worker_id);
	$result = builder_db_jobs::_exec("DELETE FROM jobs WHERE worker = $worker_id");
	assert(0 == $result || 1 == $result);
	reorder();
}

function reorder()
{
//FIXME: file lock / mutex / session
reorder_start:
	$retry = 0;

	$jobs = get_all_jobs();
	$nr=0;
	foreach ($jobs as $job)
	{
		$job_id   = $job['id'];
		$worker   = $job['worker'];
		$order_nr = $job['order_nr'];

		//if ($worker != 0)
		if (0 < $worker)
		{
			ASSERT($order_nr == -1);
			continue;
		}
		elseif ($order_nr == 0 || $order_nr == 9999)
		{
			continue;
		}
		else
		{
			$nr++;
			$updated = builder_db_jobs::_exec("UPDATE jobs SET order_nr={$nr} WHERE id={$job_id}");
			if (!$updated && $retry++ < 3)
				goto reorder_start;
		}
	}
}

function change_order($job_id, $param)
{
	$job = get_job($job_id);
	
	if (0 != $job['worker'])
		return;
	
	$order_nr_old = $job['order_nr'];
	if (-1 == $order_nr_old)
		return;
	
	switch ($param)
	{
	case 'high':
		$order_nr = 0;
		break;
	case 'low':
		$order_nr = 9999;
		break;
	case 'up':
		switch ($order_nr_old)
		{
		case 0:
			return;
		case 9999:
			$order_nr = get_job_next_order_nr();
			break;
		default:
			$order_nr = $order_nr_old - 1;
			//if ($order_nr == 0)
			//	return;
			break;
		}
		break;
	case 'down':
		switch ($order_nr_old)
		{
		case 0:
			$order_nr = get_job_next_order_nr();
			break;
		case 9999:
			return;
		default:
			$order_nr = $order_nr_old + 1;
			if ($order_nr == get_job_next_order_nr())
				$order_nr = 9999;
				//return;
			break;
		}
		break;
	default:
		break;
	}
	
	echo "SWAPING:<br/>";
	echo " order_nr_old=$order_nr_old (job_id=$job_id)<br/>";
	echo " order_nr=$order_nr<br/><br/>";
	
	if ($order_nr != 0 && $order_nr != 9999)
	{
		$updated = builder_db_jobs::_exec("UPDATE jobs SET order_nr={$order_nr_old} WHERE order_nr={$order_nr}");
		echo "updated1=$updated<br/>";
		//if (!$updated)
		//	return;
	}
	
	$updated = builder_db_jobs::_exec("UPDATE jobs SET order_nr={$order_nr} WHERE id={$job_id}");
	echo "updated2=$updated<br/>";
	//ASSERT($updated);
	
	if ($order_nr == 0)
		reorder();
}

?>