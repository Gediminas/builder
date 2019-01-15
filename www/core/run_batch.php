<?php

require_once("../conf/conf_fnc.php");
//require_once("../tools/builder_sys_fnc.php");
require_once("../tools/run_process.php");

class CRunBatch
{
	public function CRunBatch($_sys_params)
	{
		$this->sys_params = $_sys_params;
		$this->title      = '';
		$this->buffer     = Array();
	}
	
	public function UpdateSysParams($_sys_params)
	{
		$this->sys_params = $_sys_params;
	}
	
	public function Add($cmd)
	{
		if (0 == count($this->buffer) && 1 == stripos('_' . $cmd, 'rem '))
		{
			$this->title = str_ireplace('rem ', '> ', $cmd, $count);
			assert(1 == $count);
			_log("BATCH TITLE: {$this->title}");
			return;
		}
		
		echo ">>>> $cmd";
		_log("BATCH ADD: $cmd");
		array_push($this->buffer, $cmd);
	}
	
	public function Flush(&$cmd_nr)
	{
		if (0 == count($this->buffer))
		{
			$this->title = '';
			return false;
		}
			
		if (empty($this->title))
		{
			$this->title = (1 < count($this->buffer)) ? "> BATCH" : "> {$this->buffer[0]}";
		}
		
		$worker_src_dir = $this->sys_params['source_dir'];
		$worker_src_dir = path_to_dos($worker_src_dir); 
				
		array_unshift($this->buffer, "pushd \"$worker_src_dir\"");
		array_push($this->buffer, "popd");
								
		$this->Run($cmd_nr);
		
		$this->buffer = Array();
		$this->title  = '';
		return true;
	}
	
	private function Run(&$cmd_nr)
	{
		$worker_id     = $this->sys_params['worker_id'];
		$time_started  = $this->sys_params['time_started'];
		$TTL           = $this->sys_params['TTL'];
		$batch_path    = get_worker_tmp_batch_path($worker_id);
		$command_log   = get_command_log($time_started, $cmd_nr++);
		$fh            = fopen("$batch_path", 'w');
		
		$content = '';
		foreach($this->buffer as $cmd)
		{
			$content = $content . $cmd . "\r\n";
		}
		
		_log("BATCH FLUSH: ");
		_log("");
		_log($content, false);
		
		if ($fh)
		{
			fwrite($fh, $content);
			fclose($fh);
		}
		else
		{
			_log_error("Could not create [$batch_path]");
			return;	
		}
		
		$run = "_cmd_batch"; //realpath("../../cmd/_cmd_batch");
		$cmd = "$run /c \"$batch_path\"";
		
		_log_to($command_log, $this->title);
		_log_to($command_log, '');

		if (!run_shell_process($cmd, $result, $command_log, $TTL))
			_log_error("ERROR: Process (batch) failed to start [$cmd]");
		
		_log(hr());
		
		if (EErrorStatus::ERROR == CheckErrors($command_log, $time_started))
		{
			set_param("worker$worker_id", 'halt', 1);
			_log_to($command_log, "");
			_log_to($command_log, "SCRIPT WILL BE HALTED DUE TO ERRORS...");
			
			 if ($sys_params['ignore_halt'])
				_log_to($command_log, "(Currently 'ignore_halt' flag is set, script will be halted after unset)");
		}
	}

	private $sys_params;
	private $buffer;
	private $title;
};

?>