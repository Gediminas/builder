<?php
//tools

require_once("../conf/conf_fnc.php");

function CheckLineForErrors($line)
{
	$line = "_".$line;

	//Ignore errors/warnings
	if (
		//ISDEV
		stripos($line, "warning -6525: The Custom Action _1B61CF7A_ED0A_4E0F_8A97_90291ED92F35 in the InstallExecuteSequence table is run from an installed file")
		)
	{
		return EErrorStatus::OK;
	}
	
	//if (
	//	//Error was passed as param
	//	stripos($line, "\"Error: ")     ||
	//	stripos($line, "\"Warning: ")   ||
	//	stripos($line, "\"[Error]: ")   ||
	//	stripos($line, "\"[Warning]: ")
	//	)
	//{
	//	return EErrorStatus::OK;
	//}

//	if (
//		//VC10 warnings as notices
//		//stripos($line, " not defined. Defaulting to ") ||
//		//stripos($line, "Unknown compiler version - please run the configure tests and report the results") ||
//		stripos($line, "warning C4535: ") ||
//		stripos($line, "warning MSB8012: ")
//		)
//	{
//		return EErrorStatus::NOTICE;
//	}

	$p = 0;
	
	if (
		//PHP
		(false !== ($p = stripos($line, "Fatal Error: ")) && $p < 25) ||
		(false !== ($p = stripos($line, "ERROR:"))        && $p < 25) ||
		(false !== ($p = stripos($line, "[ERROR]:"))      && $p < 25) ||
		(false !== ($p = stripos($line, "[FAILED]"))      && $p < 25) ||
		
		//GIT
		stripos($line, "fatal: ") ||
		stripos($line, "Unable to checkout ") ||
		stripos($line, "' exists, but is neither empty nor a git repository") ||

		//GIT merge
		stripos($line, "Automatic merge failed") ||
		stripos($line, "CONFLICT (content): Merge conflict in ") ||
		stripos($line, "CONFLICT (submodule): Merge conflict in ") ||

		//wx
		stripos($line, "The system cannot find the path specified.") ||
				 
		//Batch
		stripos($line, "is not recognized as an internal or external command") ||
		stripos($line, "issing file:") ||
		stripos($line, "operable program or batch file.") ||
		stripos($line, "Could not open input file: ") ||
		
		//CMake	
		stripos($line, "CMakeError.log") ||

		//VC6
		stripos($line, "cl.exe terminated at user request.") ||
		stripos($line, "Tool execution canceled by user.") ||
		stripos($line, "The project cannot be built.") ||
		stripos($line, "fatal error ") ||
		stripos($line, ") : error ") ||
		
		//VC10
		stripos($line, "Unable to read the project file ") ||
		stripos($line, "INTERNAL COMPILER ERROR") ||
		stripos($line, "Build FAILED.") ||
		stripos($line, "): error ") ||
		
		//cl.exe
		//stripos($line, ": error") ||
		(false !== ($p = stripos($line, ": error")) && $p < 20) ||
		stripos($line, ": fatal error") ||
		stripos($line, "Error executing ") ||
		stripos($line, "Failed to initialize")
		)
	{
		return EErrorStatus::ERROR;
	}
	
	if (
		//PHP
		(false !== ($p = stripos($line, "WARNING: "))   && $p < 5) ||
		(false !== ($p = stripos($line, "[WARNING]: ")) && $p < 5) ||
		(false !== ($p = stripos($line, "NOTICE: "))    && $p < 5) ||
		stripos($line, "Strict Standards") ||
		stripos($line, "(Unknown): ") ||

		//PHP custom
		strpos($line, "___> ") ||
		
		//GIT
		stripos($line, "No submodule mapping found in ") ||
		
        //VC10 warnings
		stripos($line, " not defined. Defaulting to ") ||
		//stripos($line, "Unknown compiler version - please run the configure tests and report the results") ||
		
		//?
		stripos($line, ": warning")
		)
	{
		return EErrorStatus::WARNING;
	}

	if (
		//Force script to halt/exit
		stripos($line, "[HALT]")     ||
		stripos($line, "[EXIT]")
		)
	{
		return EErrorStatus::QUIT;
	}

	//OK
	return EErrorStatus::OK;
}

function __CheckLogFileForErrors($fname)
{
	$error = EErrorStatus::OK;
	$lines = file($fname);
	
	foreach ($lines as $line_num => $line)
	{
		if (0 == $line_num && stripos('_'.$line, '~'))
			break;

		$result = CheckLineForErrors($line);
		
		if (EErrorStatus::QUIT == $result)
			return EErrorStatus::QUIT;
			
		$error |= $result;
		
		if (EErrorStatus::ERROR & $error)
			return EErrorStatus::ERROR;
	}

	if (EErrorStatus::WARNING & $error)
		return EErrorStatus::WARNING;

	if (EErrorStatus::NOTICE & $error)
		return EErrorStatus::NOTICE;

	ASSERT(EErrorStatus::OK == $error);
	return EErrorStatus::OK;
}

function CheckLogFileForErrors($log)
{
	$error = __CheckLogFileForErrors($log);

	if (EErrorStatus::QUIT == $error)
		return EErrorStatus::QUIT;
	
	for ($sub_log_nr = 0; !(EErrorStatus::ERROR & $error); $sub_log_nr ++)
	{
		$sub_log = get_sub_log($log, $sub_log_nr);
		
		if (!is_file($sub_log))
			break;

		assert(is_readable($sub_log));
		$result = __CheckLogFileForErrors($sub_log);
		
		if (EErrorStatus::QUIT == $result)
			return EErrorStatus::QUIT;

		$error |= $result;
	}

	if (EErrorStatus::ERROR & $error)
		return EErrorStatus::ERROR;

	if (EErrorStatus::WARNING & $error)
		return EErrorStatus::WARNING;

	if (EErrorStatus::NOTICE & $error)
		return EErrorStatus::NOTICE;

	ASSERT(EErrorStatus::OK == $error);
	return EErrorStatus::OK;
}
	
function ProductErrorSummary($product_id, $product_time)
{
	$reportE = "";
	$reportW = "";
	$E_count = 0;
	$W_count = 0;
	$N_count = 0;
	
	for ($log_count = 0; ; )
	{
	    $fname = get_command_log($product_time, $log_count++);
	    
	    if (!is_file("$fname"))
	    	break;
	    
		$lines = file($fname);

		foreach ($lines as $line_num => $line)
		{
			if (0 == $line_num && stripos('_'.$line, '~'))
				break;
				
			$title = trim($lines[0], "0123456789:-\\/\n "); //Remove date, time & EOL

			$err = CheckLineForErrors($line);

			switch($err)
			{
			case EErrorStatus::ERROR:   $E_count ++; $reportE = $reportE . "\n    [" . $title . "]:\n #$E_count: " . $line; break;
			case EErrorStatus::WARNING: $W_count ++; $reportW = $reportW . "\n    [" . $title . "]:\n #$W_count: " . $line; break;
			case EErrorStatus::NOTICE:  $N_count ++; break;
			}
		}
	}

	return "$E_count Error{s), $W_count Warnings{s), $N_count Notice{s)". "\n---------\n" . $reportE . "\n---------\n" . $reportW;
}

function CheckErrors($command_log, $time_started)
{
	$err_state   = CheckLogFileForErrors($command_log);
	$product_dir = get_product_dir($time_started);
	
	switch($err_state)
	{
		case EErrorStatus::WARNING: SetFlag("$product_dir", 'warning'); _log($command_log, "[WARNING]"); break;	
		case EErrorStatus::ERROR:	SetFlag("$product_dir", 'error');   _log($command_log, "[ERROR]");   break;
	}

	_log_to($command_log, "err_state=$err_state");
	return $err_state;
}

?>