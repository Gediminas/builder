<?php

require_once("../conf/conf_fnc.php");

class LockState
{
    const Unlocked     = 0; //No lock file or empty unlocked file
    const Locked       = 1; //Locked file 
    const LockedBroken = 2; //Unlocked not empty (with text 'locked') file
	                        // indicating that some thread did not call UnLock() (possibly died)
}

function lock_log($lock_path, $text)
{
	if (loglevel('locks'))
		_log("[$lock_path] $text");
}

function get_lock_path($lock_name)
{
	$dir = dirname($lock_name);
	
	if (empty($dir) || '.' == $dir)
	{
		$dir = tmp_dir();
		assert(is_dir($dir));
		$lock_path = "$dir/$lock_name.lock";
	}
	else
	{
		assert(is_dir("$dir"));
		$lock_path = "$lock_name.lock";
	}
		
	return $lock_path;
}

function Lock($lock_name, &$the_key)
{
	$lock_path = get_lock_path($lock_name);
	
	lock_log($lock_path, "flock-locking");

	$the_key = fopen("$lock_path", "c");
	assert($the_key);

	if (flock($the_key, LOCK_EX))
	{
		$wiped = ftruncate($the_key, 0);
		assert($wiped);

		$bytes = fwrite($the_key, 'locked');
		assert(6 == $bytes);

		lock_log($lock_path, "flock-LOCKED");
		return true;
	}

	lock_log($lock_path, "flock-CANNOT-LOCK");
	return false;
}

function UnLock($lock_name, &$the_key)
{
	assert($the_key);
	if (is_null($the_key))
		return;

	$lock_path = get_lock_path($lock_name);
	lock_log($lock_path, "flock-unlocking");
		
	$wiped = ftruncate($the_key, 0);
	assert($wiped);
		
	flock($the_key, LOCK_UN);
	fclose($the_key);
	$the_key = NULL;
		
	lock_log($lock_path, "flock-UNLOCKED");
}

function WipeLock($lock_name)
{
	$lock_path = get_lock_path($lock_name);
	$deleted = @unlink("$lock_path"); //can be deleted already by some other thread
	lock_log($lock_path, "flock-delete-result-[$deleted]");
	return $deleted;
}

function GetLockState($lock_name)
{
	$lock_path = get_lock_path($lock_name);
	lock_log($lock_path, "flock-checking");

	if (!is_file("$lock_path"))
	{
		lock_log($lock_path, "flock-CHECKED-UNLOCKED-No-Lock-At-All");
		return LockState::Unlocked;
	}
	
	$test_lock = fopen("$lock_path", "r");
	
	if (flock($test_lock, LOCK_SH|LOCK_NB))
	{
		$state  = fread($test_lock, 6);
		$broken = ('' != $state);
		lock_log($lock_path, "flock-state-$state");
		
		if ($broken)
		{
			lock_log($lock_path, "flock-CHECKED-LOCKED-but-BROKEN-with-state-$state");
			flock($test_lock, LOCK_UN);
			fclose($test_lock);
			return LockState::LockedBroken;
		}

		lock_log($lock_path, "flock-CHECKED-UNLOCKED");
		flock($test_lock, LOCK_UN);
		fclose($test_lock);
		return LockState::Unlocked;
	}
	
	lock_log($lock_path, "flock-CHECKED-LOCKED");
	fclose($test_lock);
	return LockState::Locked;
}

function IsFileLockedForWriting($file_path)
{
	if (!is_file("$file_path"))
		return LockState::Unlocked;
	
	assert(!empty($file_path));
	$file = @fopen("$file_path", "c");
	
	if (!$file)
		return LockState::Locked;
		
	fclose($file);
	return LockState::Unlocked;
}

?>

<?php
/*
function TestLock()
{
	_log("STARTED");
	$dir = tmp_dir();
	$pth = "$dir/test.lock";
	if (@unlink("$pth"))
		_log("deleted");
	else
		_log_warning("delete failed");
	_log("----------------------");

	for(;;)
	{
		if (!GetLockState('test'))
		{
			_log("not-locked");
			if (!Lock('test', $the_key))
				_log_error("FAILED TO LOCK");

			if (!GetLockState('test'))
				_log_error("NOT LOCKED (in-lock)");
			
			_log("sleep");
			sleep(1);

			if (0 == rand()%5)
				die ("diedas");
				
			Unlock('test', $the_key);
		}
		else
			_log("not-locked");

		if (LockState::LockedBroken == GetLockState('test'))
		{
			_log_error("BROKEN LOCK (after-hard)");
			WipeLock('test');
		}

		sleep(1);
	}
}
*/
?>