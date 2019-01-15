<?php

function get_product_info($product_id, &$product_xml, &$product_name, &$product_mutex, &$product_comment, &$product_enabled, &$product_night, &$product_mailto, &$product_script, &$product_info=Array())
{
	$product_xml     = "../scripts/$product_id.xml";
	$product_srv     = "../scripts/$product_id.srv";
	$product_name    = "";
	$product_mutex   = "";
	$product_comment = "";
	$product_night   = "";
	$product_mailto  = "";
	$product_script  = "";
	
	if (file_exists("$product_xml"))
	{
		if (filesize("$product_xml"))
		{
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file("$product_xml");

			if (!$xml)
			{
				foreach(libxml_get_errors() as $error)
					echo "ERROR: XML: '", $error->message, "'<br/>\n";
					
				return false;
			}
		}
		
		$srv = false;
		if (is_file($product_srv) && filesize($product_srv))
		{
			libxml_use_internal_errors(true);
			$srv = simplexml_load_file("$product_srv");

			if (!$srv)
			{
				foreach(libxml_get_errors() as $error)
					echo "ERROR: SRV XML: '", $error->message, "'<br/>\n";
			}
		}
		//else
		//	$srv = $xml;//FIXME: TEMP !!!
		
		$product_name    = isset($xml->name)        ? trim($xml->name)        : '';
		$product_mutex   = isset($xml->mutex)       ? trim($xml->mutex)       : '';
		$product_comment = isset($xml->comment)     ? trim($xml->comment)     : '';
		$product_mailto  = isset($xml->mail_list)   ? trim($xml->mail_list)   : '';
		$product_script  = isset($xml->script)      ? trim($xml->script)      : '';

		$product_night   = isset($srv->night_build) ? trim($srv->night_build) : '';
		$product_enabled = isset($srv->enabled)     ? trim($srv->enabled)     : '';
		
		//$upgrade = 1;
		
		//if ($upgrade && empty($product_enabled))
		//	$product_enabled = isset($xml->enabled) ? trim($xml->enabled)     : ''; //FIXME: TEMP!!!

		$product_info['mutex']       = $product_mutex;
		$product_info['night_build'] = $product_night;
		$product_info['enabled']     = $product_enabled;
		$product_info['priority']    = isset($srv->priority) ? $srv->priority : '';

		//if ($upgrade && empty($product_info['priority']))
		//	$product_info['priority'] = isset($xml->order) ? trim($xml->order)     : ''; //FIXME: TEMP!!!
	}
	//else
	//{
	//	echo "ERROR: File does not exist [$product_xml]<br/>\n";
	//	return false;
	//}


	if (empty($product_name))
		$product_name = "< $product_id >";
		
	$product_mailto = str_replace("'", '',  $product_mailto);
	$product_mailto = str_replace(';', ',', $product_mailto);

	//$product_night   = ('true' == $product_night);
	//TEMP
	//if ('true' == $product_night)
	//	$product_night = 127;
	//elseif ('false' == $product_night)
	//	$product_night = 0;
	//TEMP END
		
	$product_enabled = ('true' == $product_enabled);
	return true;
}

function get_autotester_data_by_build_started($build_started, &$id, &$product, &$branch, &$total_errors, &$tests_failed, &$tests_count)
{
	$total_errors = $tests_failed = $tests_count = NULL;
	
	$db_path  = autotester_db_path();
	
	if (!is_file($db_path))
		return FALSE;

	try
	{
		$db = new PDO('sqlite:' . $db_path);
		
		if ($db)
		{
			//2013-01-02 20:47:05 ($build_started)
			//to
			//2013-01-07_13-58-58 (stored in db)
			
			$build_started = str_replace(' ', '_', $build_started);
			$build_started = str_replace(':', '-', $build_started);
			//$build_started = trim($build_started, '_');
			//$build_started = trim($build_started, ' ');
			//echo "$build_started<br/>";

			$sql  = "SELECT id, product, branch, total_errors, tests_failed, tests_count, build_started FROM tests WHERE build_started='{$build_started}' LIMIT 1";
			$res  = $db->query($sql);
			$data = $res->fetch();
			
			if ($data)
			{
				$id           = $data[0];
				$product      = $data[1];
				$branch       = $data[2];
				$total_errors = $data[3];
				$tests_failed = $data[4];
				$tests_count  = $data[5];
				return TRUE;
			}
		}
	}
	catch( Exception $exception ) { }

	return FALSE;
}

?>