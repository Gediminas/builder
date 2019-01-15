<?php

//*1 SetTTL(seconds)
//* -------------
//*2 seconds - 
//* -------------
//*3 Set timeout for child process called by run_process() (batch script also is called so)

$seconds = $cmd_params[0];

_log_to($command_log, "old value = [{$sys_params['TTL']}] seconds");

$sys_params['TTL'] = $seconds;

_log_to($command_log, "new value = [{$sys_params['TTL']}] seconds");
_log_to($command_log, "[DONE]");

?>