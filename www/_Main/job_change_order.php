<?php
$all      = isset($_GET['all'])      ? $_GET['all']      : 0;
$bcomment = isset($_GET['bcomment']) ? $_GET['bcomment'] : false;
$job_id   = isset($_GET['job_id'])   ? $_GET['job_id']   : false;
$param    = isset($_GET['param'])    ? $_GET['param']   : false;
echo header("Refresh: 0; url=../_Main/index.php?all=$all&bcomment=$bcomment&all=$all");

require_once("../db/builder_db_jobs_fnc.php");

echo "bcomment=$bcomment<br/>";
echo "param=$param<br/>";

change_order($job_id, $param);

?>