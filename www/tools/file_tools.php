<?php
// tools

require_once("../conf/conf_fnc.php");
require_once("log.php");

function needDelete($timestamp, $debug=0)
{
	$tmp  = explode('_', $timestamp);
	$buildDate = explode('-', $tmp[0]);
	$buildTime = explode('-', $tmp[1]);
	$todayDate = explode('-', date("Y-m-d"));
	$age = ($todayDate[0]-$buildDate[0]) * 365 + ($todayDate[1]-$buildDate[1]) * 31 + ($todayDate[2]-$buildDate[2]);
	$day = $buildDate[2];

//	if ($debug == 1) echo "[$age/$day/$buildTime[0]]";
	
	if (($age > 7) && ($buildTime[0] > 8))
	{
//		if ($debug == 2) echo "[week+day]";
		return 1; // not a night build (after 08:00 am)
	}
	if  ($age < 20) 
	{
//		if ($debug == 2) echo "[fresh night]";
		return 0;						  // older than 20 days
	}
	if (($age < 200) && ($day % 7 == 0)) 
	{
//		if ($debug == 2) echo "[weekly night]";	
		return 0;
	}
//	if ($debug == 2) echo "[old build]";	
	return 1;
}


function getFileDate($path)
{
	if (file_exists($path)) 
	{
		return date ("Y-m-d_H-i", filemtime($path));
	}
	else
	{
		echo "error: file [$path] not found\n";
	}
}


function getShortName( $path)
{
	$str = strrev($path);
	$str = explode('/',$str);
	return strrev($str[0]);
}

function file_list($Path, $ext="dll") 
{ // make file list. file names separated by ;
    if ($Path=="") return; 
    //$Path= rtrim($Path, '/').'/';
    $handle = opendir($Path);
	$rez = '';
    for (;false !== ($file = readdir($handle));)
	{
		$file_ext = explode ('.',$file);
		$file_ext = isset($file_ext[1]) ? $file_ext[1] : "";

		if(($file !='.') && ($file != '..') && ($file_ext == $ext))
		{
			$rez = strtolower($file).";".$rez;
		}
	}//end of for 
    closedir($handle);
	return $rez;
} 

function delete_dir_tree($path) 
{
	if (empty($path))
		return; 

	if (strlen($path) < 5) 
	{
		_log_error("Path supplied for deleting is too short [$path]. Dangerous. Skipping");
		return;
	}

	$path   = rtrim($path, '/');
	$handle = opendir($path);
	
	while ($file = readdir($handle)) if ('.' != $file && '..' != $file)
	{
		$fullpath= "$path/$file";
		
		if (is_dir($fullpath))
			delete_dir_tree($fullpath);
		elseif (!unlink($fullpath))
			_log_error("Failed to delete file [$fullpath]");
	}
	
	closedir($handle);

	if (!rmdir($path))
		_log_error("Failed to delete folder [$path]");
} 

function get_files( $SourcePath, $DestPath, $ext, $debug) 
{ 
    if ($SourcePath=="") return; 
    if ($DestPath=="") return; 
    $Path= rtrim($SourcePath, '/').'/';
    $handle = opendir($Path);

    for (;false !== ($file = readdir($handle));)
        if($file != "." and $file != ".." ) {
            $fullpath= $Path.$file;

            if( is_dir($fullpath) ) {
                get_files($fullpath, $DestPath, $ext, $debug); //recursive
            } else {
                $getExt = explode ('.',$fullpath);
                $file_ext = $getExt[count($getExt)-1];

                if ($file_ext == $ext) 
                { 
					$fname = substr($fullpath, strripos($fullpath, "/")+1, strlen($fullpath));
						echo $fname."\n";                  
						copy($fullpath,$DestPath."/".$fname);
                }//end of if
              }
    }
    closedir($handle);
} 
 
function check_wildcard($name, $wildcard)
{
	//FIXME: Does not work when:
	//        $name     = "xxxyyy"
	//        $wildcard = "xxx*yyy"
	
	if (strlen($wildcard) == 0)
	{
		return true;
	}

	$filter = str_replace("*", "([^<]+)", $wildcard);
	$filter = "/^$filter$/i";
	$match  = preg_match($filter, $name, $matches);
	return $match;
}

function copy_file($command_log, $src, $dst)
{
	if (!is_file($src))
	{
		_log_to($command_log, "ERROR: Source file does not exist [$src]");
		return false;
	}
	
	$dst_dir = dirname($dst);
	
	if (!is_dir("$dst_dir") && !mkdir("$dst_dir", 0777, true))
	{
		_log_to($command_log, "ERROR: Destination folder cannot be created [$dst]");
		return false;
	}
					
	_log_to($command_log, "Copy: [$src] --> [$dst]");

	if (!copy($src, $dst))
	{
		_log_to($command_log, "ERROR: Cannot copy file [$src] --> [$dst]");
		return false;
	}
	
	return true;
}

function copy_dir($command_log, $src, $dst, $wildcard = NULL, $recursive = 1)
{
	if (!is_dir($src))
	{
		_log_to($command_log, "ERROR: Source folder does not exist [$src]");
		return false;
	}
	
	$result   = true;
	$iterator = new DirectoryIterator($src);

	foreach ($iterator as $info) if (!$info->isDot())
	{
		$sub_name = $info->getBasename();
		$sub_dst  = "$dst/$sub_name";
		$sub_src  = $info->getPathname();
		
		if ($sub_name[0] == '.')
		{
			continue;
		}

		if ($info->IsDir())
		{
			if ($recursive)
				$result = copy_dir($command_log, $sub_src, $sub_dst, $wildcard) && $result;
		}
		elseif (check_wildcard($sub_name, $wildcard))
		{
			$result = copy_file($command_log, $sub_src, $sub_dst) && $result;
		}
	}
	
	return $result;
}

function removeUnlisted($list, $path, $ext="lng")
{
	$files = file_list($path, $ext);
	echo $files;
	echo "listing [$path*.$ext]  files\n";
	$files_list = explode(";", $files);
	foreach ($files_list as $file) if(strlen($file)>3)
	{
		if (stripos($list, $file)) 
		{
			echo "keep: $file\n";
		}
		else
		{
			echo "remove: $path.$file\n";
			unlink($path.$file);
		}
	}

}

function getRelativePath($from, $to)
{
	$from = strtolower($from);
	$to   = strtolower($to);
	$from = str_replace('\\', '/', $from);
	$to   = str_replace('\\', '/', $to);
	$from = explode('/', $from);
	$to   = explode('/', $to);
	
	foreach($from as $depth => $dir)
	{
		if (isset($to[$depth]))
		{
			if($dir === $to[$depth])
			{
				unset($to[$depth]);
				unset($from[$depth]);
			}
			else
			{
				break;
			}
		}
	}

	for($i = 0; $i < count($from); ++ $i)
	{
		array_unshift($to,'..');
	}
	
	$result = implode('/', $to);
	return $result;
}

//function init_php_process_options($error_log, $time_limit)
//{
//	set_php_error_handler($error_log);
//	set_time_limit($time_limit);
//
//	_log("$error_log", "");
//	_log("$error_log", "> php_sapi_name = "         . php_sapi_name());
//	_log("$error_log", "> php_error_handler_log = " . $error_log);
//	_log("$error_log", "> date_default_timezone = " . date_default_timezone_get());
//	_log("$error_log", "");
//}

function check_create_dir($dir)
{
	if (is_dir("$dir"))
	{
		_log("CreateDir [$dir]: Already exists");
		return false;
	}

	$created = @mkdir( "$dir", 0777, true); //FIXME: Some transaction needed
	if ($created)
		_log("CreateDir [$dir]: Created");
	else
		_log_warning("CreateDir [$dir]: Failed. Some other thread already created it?");
		
	return $created;
}

function verify_temp_folder()
{
	check_create_dir(tmp_dir());
}

function SetFlag($dir, $flag, $status = true)
{
	$file = "$dir/[$flag]";
	
	if ($status)
	{
		if (!is_file($file))
		{
			$h=fopen($file, 'w');
			fclose($h);
		}
	}
	else
	{
		if (is_file($file))
		{
			unlink($file);
		}
	}
}

function GetFlag($queue_tmp_dir, $flag)
{
	$file = "$queue_tmp_dir/[$flag]";
	return is_file($file);
}

function SetValue($queue_tmp_dir, $entry, $value)
{
	$file = "$queue_tmp_dir/\$$entry";
	
	$h=fopen($file, 'w');
	fwrite($h, $value);
	fclose($h);
}

function GetValue($queue_tmp_dir, $entry)
{
	$file = "$queue_tmp_dir/\$$entry";
	
	if (!is_file($file))
		return false;
	
	$h=fopen($file, 'r');
	$value = fread($h, 10240);
	fclose($h);
	return $value;
}

function GetLastLog($product_time)
{
	$last_command_log = "";
	
	for ($cmd_nr = 0; ; $cmd_nr++)
	{
		$command_log = get_command_log($product_time, $cmd_nr);
		
		if (!file_exists($command_log))
			break;

		$last_command_log = $command_log;
	}
	
	if (!empty($last_command_log))
	{
		for ($sub_log_nr = 0; ;$sub_log_nr ++)
		{
			$sub_fname = get_sub_log($last_command_log, $sub_log_nr);
			if (!is_file($sub_fname))
				break;

			if (!is_readable($sub_fname))
				continue;
				
			$last_command_log = $sub_fname;
		}
	}	
	
	return $last_command_log;
}

function GetMainAndSubLogLineCount($fname, &$sub_line_count)
{
	$sub_line_count = 0;
	if (empty($fname) || !is_file($fname)) {
		return 0;
	}
	
	try {	
		$lines = file($fname);
		$line_count = $sub_line_count = count($lines);
	
		for ($sub_log_nr = 0; ;$sub_log_nr ++)
		{
			$sub_fname = get_sub_log($fname, $sub_log_nr);
			if (!is_file($sub_fname))
				break;

			if (!is_readable($sub_fname))
				continue;
			
			$sub_lines = file($sub_fname);
			$sub_line_count = count($sub_lines);
			$line_count += $sub_line_count;
		}
	
		return $line_count;
	}
	catch(Exception $e) {
		echo "ERROR" . $e->getMessage();
		return 0;
	}
}

?>