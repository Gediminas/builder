<?php

require_once("../conf/conf_fnc.php");
require_once("../tools/run_process.php");


function check($fname, $files_list)
{
	return stripos($files_list, $fname) >= 1;
}
	
function dependList($command_log, $worker_id, $bin_dir, $preserves, $type, $silent=0) // 1-remove list; 2-keep list
{
	$preserves     = " ".$preserves; // comparing specific
	$bin_dir       = '"$bin_dir/*.exe" "$bin_dir/*.dll"';
	$dep_list_path = get_worker_tmp_dep_list_path($worker_id);
	$exe           = realpath("../cmd/dumpbin/dumpbin.exe");
	$run           = "_cmd_dep"; //realpath("../../cmd/_cmd_dep");

	$cmd = "$run /c $exe /DEPENDENTS \"$bin_dir\" > \"$dep_list_path\" 2>>\"$command_log\"";

	if (!run_process($cmd, $result, NULL, 10*60))
		return "ERROR";
	
	$dep_list = file("$dep_list_path");
	
	//parsing
	$files_dep = array();
	$stage = 0;
	$current = "";
	
	foreach ($dep_list as $line)
	{
        if (stripos($line, "ump of file ")>=1)
		{
			$leng = strlen($line);
			$start = strripos($line,'\\');
			$start2 = strripos($line,'/');
			if ($start2 > $start) $start = $start2;
			$fname = substr($line, $start+1, $leng-$start-3); // parsing file names
			$files_dep[$fname] = "";
			$current = $fname;
		}
		
		if (strlen($line) <4) $stage--;
		if (stripos($line, "\r\n") ==1) $stage = 0;
		
		if (stripos($line, "Image has the following dependencies")>=1)
		{		
			$stage = 2;
		}
		else if (($stage > 0) && (strlen($line) >4)) // parsing stage and not empty line
		{
			$dep_fname = substr($line, 4, strripos($line,'dll')-1);		// put dependent dlls` names to array
			@$files_dep[$current] = $dep_fname.";".$files_dep[$current];
		}
    }
	// end of data parsing
	
	// calculating file dependencies
	// iterating until there were unused files
	$changed = true;
	$remove_list = "";
		
	while ($changed)
	{
		$changed = false;
		
		$files_list = ";".implode($files_dep);
		$require_list = $files_list;

		foreach (array_keys($files_dep) as $file)
		{
			 // remove $file from $require_list.
			$require_list = str_ireplace($file,"", $require_list);
			if ((check($file, $files_list) != 1) && (check($file, $preserves) != 1))
			{
				//if ($silent != 1) echo "unused file:\t$file\n";
				$changed = true;
				$remove_list = $file.";".$remove_list;
				unset($files_dep[$file]);
			}
			else 
			{
				//if (@$debug) echo "keeping file:\t$file\n";
			}
		}
		
		//if (isset($debug) && ($changed) && (@$silent != 1)) echo "----\n";
		
	}
	
	// parsing missing files:
	$require = explode(';', chop($require_list));
	$missing = "";
	foreach ($require as $file)  
	{
		$file = chop($file);
		if ((!stripos(" ".$missing, $file) >= 1) && (strlen($file) > 4) && (!stripos(" "._system_dll(), $file) >= 1) )
		{	
			//if (@$debug || ($type ==3)) echo "missing file: \t$file\n";
			$missing = $missing.$file." ";
		}
	}
	// end of missing
	
	$keep_list = "";
	foreach (array_keys($files_dep) as $file)	
	{	
		$keep_list = $file.";".$keep_list;
	}
	
	//if (@$debug) echo "keeping: ".$keep_list."\n";
	
		
	if ($type == 1)	return $remove_list;
	if ($type == 2)	return chop($keep_list);
	if ($type == 3)	return chop($missing);
	return "ERROR";
}	


//-------------------
function checkDependList($command_log, $dir, $preserves)
{
	_log_to($command_log, "Cheking preserve list");
	
	$files = explode(' ',$preserves); // comparing specific
	$rez = "";
	foreach ($files as $file)
	{
		$fname = "$dir/$file";
		_log_to($command_log, " Checking file [$fname]");
		
		if (!file_exists($fname)) {
			_log_to($command_log, " FAIL");
			_log_to($command_log, "\n WARNING: [$fname] file not found");
			$rez = $rez.";".$file;
		}
	
	}
	
	_log("done");
	return $rez;
}	

?>