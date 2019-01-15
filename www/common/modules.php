<?php

function foreach_call($wildcard_path, &$params=NULL)
{
	$requires = glob("$wildcard_path");
	
	foreach ($requires as $require) if (is_file("$require"))
	{
		require_once("$require");

		$function = str_ireplace('.php', '', basename($require));
		assert(function_exists($function));
		
		if (is_null($params))
			$function();
		else
			$function($params);
	}
}

?>
