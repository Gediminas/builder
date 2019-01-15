<?php

require_once("../conf/conf_fnc.php");
require_once("../tools/log.php");
require_once("../tools/lock.php");
require_once("../db/builder_db_jobs_fnc.php");
require_once("run_product.php");

$worker_id  = isset($_GET['id']) ? $_GET['id'] : $argv[1];

//set_time_limit(4*60*60);//4h
set_time_limit(0);
set_php_error_handler(get_worker_log($worker_id));

if ($worker_id <= 0 || worker_count() < $worker_id)
	_log_die("WORKER [$worker_id] is invalid, valid range [1.." . worker_count() . "]");
	
$job                 = get_first_reserved_job();
$reserved_worker_id  = -$job['worker'];
$product_id          =  $job['product'];
$user_comment        =  $job['comment'];
	
if ($reserved_worker_id != $worker_id)
	_log_die("Worker [$worker_id] is not waited (daemon waits for worker [$reserved_worker_id])");
	
if (!accept_job_by_worker($worker_id, $job_id))
	_log_die("WORKER [$worker_id] has nothing to work.");
	
if (LockState::Unlocked != GetLockState("worker$worker_id"))
	_log_die("WORKER [$worker_id] Cannot lock. Another worker is running or just died?");
	
if (!Lock("worker$worker_id", $the_key))
	_log_die("WORKER [$worker_id] Lock failed. Another worker just started?");

_log("WORKER [$worker_id] LOCKED");

run_product($worker_id, $product_id, $job_id, $user_comment);

UnLock("worker$worker_id", $the_key);
_log("WORKER [$worker_id] UNLOCKED");

?>