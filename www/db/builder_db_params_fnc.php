<?php

require_once("builder_db_params.php");

function set_param($owner, $param, $value)
{
	$params = new builder_db_params(db_params_path());
	$result = $params->exec("UPDATE params SET value='$value' WHERE owner='$owner' AND param='$param'");
	
	if (0 == $result)
		$result = $params->exec("INSERT INTO params (owner, param, value) VALUES ('$owner', '$param', '$value')");
	
	//$params->close();

	assert(1 == $result);
}

function get_param($owner, $param)
{
	$result = builder_db_params::_query1("SELECT value FROM params WHERE owner='$owner' AND param='$param'");
	$value = $result['value'];
	return $value;
}

function remove_params($owner)
{
	assert(!empty($owner));
	$result = builder_db_params::_exec("DELETE FROM params WHERE owner = '$owner'");
}

?>