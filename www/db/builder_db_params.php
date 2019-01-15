<?php

require_once("builder_db.php");

class builder_db_params extends builder_db
{
	protected function get_create_table_query()
	{
		return "CREATE TABLE params
				(
					owner CHAR(20),
					param CHAR(20),
					value CHAR(20)
				)";
	}
	
	static function _query($query)
	{
		$db = new builder_db_params(db_params_path());
		return $db->query($query);
	}
	
	static function _query1($query)
	{
		$db = new builder_db_params(db_params_path());
		return $db->query1($query);
	}
	
	static function _exec($query)
	{
		$db = new builder_db_params(db_params_path());
		return $db->exec($query);
	}
	
}

?>