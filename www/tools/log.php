<?php

//global
static $php_error_handler_log;

function GetLogDateTime()
{
	return date("Y-m-d H:i:s");
}

function is_cli()
{
	return ('cli' == php_sapi_name());
}


function _log_reset($log_file, $title = NULL)
{
	if (is_file("$log_file"))
		unlink("$log_file");
	
	if (!empty($title))
		_log_to($log_file, "[$title]", false);
}

function _log_to($log_file, $text, $add_time=true)
{
	if(empty($log_file))
		return;

	if (empty($text))
		$add_time=false;
		
	//$nl   = is_cli() ? "\n" : "<br/>\n";
	$text = "$text\n";
	
	if ($add_time)
	{
		$time = GetLogDateTime();
		$text = "$time: $text";
	}

	//echo $text;
	if ($fh = fopen("$log_file", 'a'))
	{
		fwrite($fh, $text); 
		fclose($fh);
	}
}

function _log($text, $add_time=true)
{
	$nl   = is_cli() ? "\n" : "<br/>\n";
	$text = "$text$nl";
	
	if ($add_time)
	{
		$time = GetLogDateTime();
		$text = "$time: $text";
	}

	echo "$text";
}

function _log_warning($text, $pre = "WARNING: ")
{
	$text = is_cli() ? "$pre$text"
			         : "<font style='BACKGROUND-COLOR:yellow' color=black>\n $pre$text \n</font>";

	_log($text);
}

function _log_error($text, $pre = "ERROR: ")
{
	$text = is_cli() ? "$pre$text"
			         : "<font style='BACKGROUND-COLOR:red' color=black>\n $pre$text \n</font>";

	_log($text);
}

function _log_die($text)
{
	_log_error($text, "FATAL ERROR: ");
	_log_error("[TERMINATED]", "");
	die();
}

function getDebugBacktrace($NL = "<BR/>\n") {
    $dbgTrace = debug_backtrace();
	$dbgTrace = str_replace("($NL)", "()", $dbgTrace);
	$dbgMsg   = "";
	
    foreach($dbgTrace as $dbgIndex => $dbgInfo) if (1 < $dbgIndex)
	{
		if (empty($dbgMsg))
			$dbgMsg  = "___> Stack: $NL";

		$args = $dbgInfo['args'];
		$args = @implode(", ", $args);
		
		$dbgMsg .= "___> ";
		$dbgMsg .= " {$dbgInfo['function']}($args)";
		
		if (isset($dbgInfo['file']))
			$dbgMsg .= " - {$dbgInfo['file']}";

		if (isset($dbgInfo['line']))
			$dbgMsg .= " (line {$dbgInfo['line']})";
			
		//$dbgMsg .= $NL;
		$dbgMsg .= $NL;
    }

    return $dbgMsg;
}

function _log_php_error_handler($errno, $errstr, $errfile, $errline)
{
	if (!error_reporting())
		return false;

	global $php_error_handler_log;
	
    switch ($errno)
	{
	case E_ERROR:
	case E_USER_ERROR:
		$color = 'red';
		$errors = "[PHP]: Fatal Error";
		break;
		
	case E_WARNING:
	case E_USER_WARNING:
		$color = 'yellow';
		$errors = "[PHP]: Warning";
		break;
		
	case E_NOTICE:
	case E_USER_NOTICE:
		$color = 'yellow';
		$errors = "[PHP]: Notice";
		break;
		
	default:
		$color = 'blue';
		$errors = "[PHP]: (Unknown)";
		break;
	}
	
	$isCLI = is_cli();
	$nl    = $isCLI ? "\n" : "<br/>\n";
    $msg   = "$errors: $errstr in $errfile on line $errline";
	$msg   = $msg . $nl;
	$msg   = $msg . getDebugBacktrace($nl);

	if (!$isCLI)
		$msg = "<font style='BACKGROUND-COLOR: $color' color=\"black\">\n $msg \n</font>";

	_log($msg);
	return true;
}

function _log_php_shutdown_handler()
{
	$error = error_get_last();
	
    if ($error['type'] === E_ERROR)
	{
		_log("", false);
		_log_error("{$error['message']} in {$error['file']} (line {$error['line']})", "FATAL ERROR: ");
		_log_error("[DIED]", "");
   }
	else
	{
		_log("[FINISHED]");
	}
} 
function set_php_error_handler($error_log)
{
	global $php_error_handler_log;
	$php_error_handler_log = $error_log;
	set_error_handler("_log_php_error_handler");
	register_shutdown_function('_log_php_shutdown_handler');

	_log("------------------------------------------------------------------------", false);
	_log("> set_php_error_handler = " . $error_log, false);
	_log("> php_sapi_name         = " . php_sapi_name(), false);
	_log("> date_default_timezone = " . date_default_timezone_get(), false);
	_log("------------------------------------------------------------------------", false);
}

function ob_file_callback($buffer)
{
  global $ob_file;
  fwrite($ob_file, $buffer);
  fflush($ob_file);
}

?>