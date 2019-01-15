<?php

//*1 DirCopy(src, dst, wildcard)
//* -------------
//*2 src - Source folder
//*2 dst - Destination folder
//*2 wildcard - e.g. ".dll", can be NULL
//*2 recursive - 0 - not-recursive, 1 - recursive (default 1)
//* -------------
//*3 Copy folder using wildcard

$src       = $cmd_params[0];
$dst       = $cmd_params[1];
$wildcard  = $cmd_params[2];
$recursive = isset($cmd_params[3]) ? $cmd_params[3] : 1;

$result = copy_dir($command_log, $src, $dst, $wildcard, $recursive);
_log_to($command_log, $result ? "[DONE]" : "[FAILED]");
//return $result;

?>