<?php
echo header('Refresh: 1; url=../_System/index.php');

require_once(dirname(__FILE__) . '/../tools/builder_sys_fnc.php');

$action = isset($_GET['action']) ? $_GET['action'] : $argv[1];
echo $action . "\n";

switch ($action) {
	case 1: StartDaemon();   break;
	case 2: StopDaemon();    break;
	case 3: RestartDaemon(); break;
}

sleep(3);
?>