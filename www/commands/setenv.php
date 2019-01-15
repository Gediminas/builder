<?php

//*1 SetEnv(ide)
//* -------------
//*2 ide - build environment, must exist file - /local/php/ide_<ide>.php
//* -------------
//*3 Set building environment, used to get build list from build.cfg files

$ide = $cmd_params[0];
$sys_params['ide'] = $ide;

//if (!is_file("../../local/php/ide_$ide.php"))
//_log_to($command_log, "ERROR: ide=$ide");

?>