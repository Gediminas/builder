<?php

require_once("db.php");
require_once("../tools/lock.php");
require_once("../tools/log.php");

class builder_db extends db
{
	protected $m_key = NULL;
	
	protected function get_create_table_query() { assert(false); return ""; }
	
	protected function create_db()
	{
		try
		{
			if (!$this->m_db)
				throw new Exception("DB not opened for creation");
				
			$query = $this->get_create_table_query();

			_log("DB CREATING");
			$this->m_db->beginTransaction();
			$this->m_db->exec($query);
			$this->m_db->commit();
			_log("DB CREATED");
		}					
		catch (Exception $exception)
		{
			$this->m_db->rollBack();
			_log_die('DB CREATION FAILED: ' . $exception->getMessage());
		}
	}

	public function open($path)
	{
		try
		{
			if (!Lock($path, $this->m_key))
				throw new Exception("Could not lock");

			db::open($path);

			assert($this->m_key);
			
			if (0 == filesize("$path"))
				$this->create_db();
		}
		catch (Exception $exception)
		{
			log_error('DATABASE OPEN FAILED: ' . $exception->getMessage());
		}
	}

	public function close()
	{
		db::close($this->m_db);
		UnLock($this->m_path, $this->m_key);
	}

	protected function log($text)
	{
		_log("$text [{$this->m_path}]");
	}
	
	protected function log_error($text)
	{
		_log_error("$text [{$this->m_path}]");
	}
}

?>