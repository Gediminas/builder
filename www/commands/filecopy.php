<?php

//*1 FileCopy(src, dst)
//* -------------
//*2 src - Source file
//*2 dst - Destination file
//* -------------
//*3 Copy file

$src     = $cmd_params[0];
$dst     = $cmd_params[1];

$result = copy_file($command_log, $src, $dst);
_log_to($command_log, $result ? "[DONE]" : "[FAILED]");
//return $result;

?>