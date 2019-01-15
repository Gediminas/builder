<?php

class db
{
	protected $m_path = NULL;
	protected $m_db   = NULL;
	
	function __construct($path)
	{
		$this->open($path);
	}
	
	function __destruct()
	{
		$this->close();
	}
	
	public function open($path)
	{
		try
		{
			assert(!empty($path));
			$this->m_path = $path;
			$this->log_normal("DB open");

			$this->m_db = new PDO('sqlite:' . $path);
			
			if (!$this->m_db)
				throw new Exception("Could not open DB");
			
			$this->m_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //set all errors to excptions
			
			$this->m_db->setAttribute(PDO::ATTR_TIMEOUT, 5.0); //http://www.mail-archive.com/sqlite-users@sqlite.org/msg13901.html
			//PRAGMA omit_readlock=ON;                   //http://www.mail-archive.com/sqlite-users@sqlite.org/msg38540.html

		}
		catch (Exception $exception)
		{
			$this->log_error('DATABASE OPEN FAILED: ' . $exception->getMessage());
		}
	}

	public function close()
	{
		$this->m_db = NULL;
		//unset($this->m_db);
	}

	public function query($query)
	{
		try
		{
			$this->log_normal("Executing db query [$query]");
			
			if (!$this->m_db)
				throw new Exception("DB not opened");
				
			$result = $this->m_db->query($query);
			
			if (!$result)
				throw new Exception("DB query failed");
				
			return $result->fetchAll();
		}
		catch (Exception $exception)
		{
			$this->log_error($exception->getMessage() . " [$query]");
			return NULL;
		}
	}

	public function query1($query)
	{
		try
		{
			$this->log_normal("DB query-one [$query]");
			
			if (!$this->m_db)
				throw new Exception("DB not opened");
				
			$result = $this->m_db->query($query . ' LIMIT 1');
			
			if (!$result)
				throw new Exception("DB query-one failed");
				
			return $result->fetch();
		}
		catch (Exception $exception)
		{
			$this->log_error($exception->getMessage() . " [$query]");
			return NULL;
		}
	}

	public function exec($query)
	{
		try
		{
			$this->log_normal("DB exec [$query]");
			
			if (!$this->m_db)
				throw new Exception("DB not opened");
				
			$result = $this->m_db->exec($query);
			
			$this->log_normal("DB exec returned [$result] [$query]");
			
			return $result;
		}
		catch (Exception $exception)
		{
			$this->log_error($exception->getMessage() . " [$query]");
			return NULL;
		}
	}

	protected function log_normal($text) { }
	protected function log_error($text)  { }
}

?>