<?php

require_once("../db/builder_db.php");

class builder_db_history extends builder_db
{
	protected function get_create_table_query()
	{
		return "CREATE TABLE history
				(
					id            INTEGER   PRIMARY KEY AUTOINCREMENT,
					job_id        INTEGER,
					product_id    CHAR(20)  NOT NULL,
					time_started  TIMESTAMP NOT NULL,
					time_finished TIMESTAMP NOT NULL,
					time_AT       TIMESTAMP NOT NULL,
					duration      INTEGER,
					build_nr      INTEGER,
					error_status  TINYINT,
					user_comment  CHAR(250),
					distr_path    CHAR(250)
				)";
	}
	
	static function _query($query)
	{
		$db = new builder_db_history(db_history_path());
		return $db->query($query);
	}
	
	static function _query1($query)
	{
		$db = new builder_db_history(db_history_path());
		return $db->query1($query);
	}
	
	static function _exec($query)
	{
		$db = new builder_db_history(db_history_path());
		return $db->exec($query);
	}
	
}
     
?>