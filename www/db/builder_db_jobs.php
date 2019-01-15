<?php

require_once("builder_db.php");

class builder_db_jobs extends builder_db
{
	protected function get_create_table_query()
	{
		return "CREATE TABLE jobs
				(
					id         INTEGER   PRIMARY KEY AUTOINCREMENT,
					product    CHAR(20)  NOT NULL,
					time_added TIMESTAMP NOT NULL,
					order_nr   INTEGER   NOT NULL,
					worker     TINYINT   DEFAULT 0,
					comment    CHAR(200)
				)";
	}
	
	static function _query($query)
	{
		$db = new builder_db_jobs(db_jobs_path());
		return $db->query($query);
	}
	
	static function _query1($query)
	{
		$db = new builder_db_jobs(db_jobs_path());
		return $db->query1($query);
	}
	
	static function _exec($query)
	{
		$db = new builder_db_jobs(db_jobs_path());
		return $db->exec($query);
	}
	
}
     
?>