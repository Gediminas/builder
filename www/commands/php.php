<?php

//*1 PHP(cmd_params)
//* -------------
//*2 cmd_params - Space separated list, e.g. PHP(some.php 'first_param' 'second_param')
//* -------------
//*3 Execute external PHP script

require_once("../tools/run_process.php");

$TTL = $sys_params['TTL'];
$cmd = "_cmd_php /c _php-win-PHP -f";
//$cmd = "_php -f";

foreach($cmd_params as $param) if (0 < strlen($param))
{
	$cmd = $cmd . " \"$param\"";
}

run_process($cmd, $result, $command_log, $TTL);
?>