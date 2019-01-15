<?php

//*1 DEBUG::SetTimeout(seconds)
//* -------------
//*2 seconds - Time to live for worker process
//* -------------
//*3 Overrides worker process timeout (See PHP set_time_limit())

$seconds = $cmd_params[0];

_log_to($command_log, "set_time_limit($seconds)");
set_time_limit($seconds);
_log_to($command_log, "[DONE]");

?>