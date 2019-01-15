<?php
	$all       = $_GET['all'];
	$create_id = isset($_POST['create_id']) ? $_POST['create_id'] : NULL;
	
	if (is_null($create_id))
	{
		$save_id = isset($_GET['save_id']) ? $_GET['save_id'] : NULL;
		echo  header("Refresh: 2; url=../_Main/edit.php?id=$save_id&all=".$all);  
	}
	else
	{
		echo  header("Refresh: 2; url=../_Main/edit.php?id=$create_id&all=".$all);  
	}
?>
	
<html><head>
<title> MxK Builder edit </title>
</head><body>
 
<?php
	if (is_null($create_id))
	{
		$name        = isset($_POST['name'])        ? $_POST['name']        : "";
		$mutex       = isset($_POST['mutex'])       ? $_POST['mutex']       : "";
		$comment     = isset($_POST['comment'])     ? $_POST['comment']     : "";
		$mail_list   = isset($_POST['mail_list'])   ? $_POST['mail_list']   : "";
		$enabled     = isset($_POST['enabled'])     ? 'true' : 'false';
		$priority    = isset($_POST['priority'])    ? $_POST['priority']    : "";
		$script      = isset($_POST['script'])      ? $_POST['script']      : "";
		
		$night_build = 0;
		for ($wday = 1; $wday < 8; ++ $wday)
			$night_build += isset($_POST['night_build_' . $wday]) ? pow(2, $wday-1) : 0;
		
		//echo "night_build: $night_build <br/>";

		if ($priority == '0')
			$priority = '';

		
		$xml_path = "../scripts/$save_id";
		if (strlen($save_id) && file_exists("$xml_path.xml"))
		{
			$xml = fopen("$xml_path.xml", 'w');
			fwrite($xml, "<?xml version=\"1.0\"?>\n");
			fwrite($xml, "<build_script>\n\n");
			write_xml_block($xml, 'name',        $name);
			write_xml_block($xml, 'mutex',       $mutex);
			write_xml_block($xml, 'comment',     $comment);
			write_xml_block($xml, 'mail_list',   $mail_list);
			//write_xml_block($xml, 'night_build', $night_build);
			//write_xml_block($xml, 'order',       $priority);
			//write_xml_block($xml, 'enabled',     $enabled);
			write_xml_block($xml, 'script',      $script);
			fwrite($xml, "</build_script>\n");
			fclose($xml);
			$xml = NULL;

			$srv = fopen("$xml_path.srv", 'w');
			fwrite($srv, "<?xml version=\"1.0\"?>\n");
			fwrite($srv, "<server_options>\n\n");
			write_xml_block($srv, 'night_build', $night_build);
			write_xml_block($srv, 'priority',    $priority);
			write_xml_block($srv, 'enabled',     $enabled);
			fwrite($srv, "</server_options>\n");
			fclose($srv);
			$srv = NULL;

			echo "<H1>Saved: <i>" . $save_id . "</i></H1>\n\n";
		}
		else
		{
			die("ERROR: Not saved");
		}
	}
	else
	{
		if (strlen($create_id) && !file_exists("../scripts/$create_id.xml"))
			fclose(fopen("../scripts/$create_id.xml", "a"));
		
		echo "<H1>Created: <i>" . $create_id . "</i></H1>\n\n";
	}
?>

</body></html>


<?php 

function write_xml_block($file_handle, $tag, $value)
{
	fwrite($file_handle, "<$tag>\n"); 
	fwrite($file_handle, "$value\n"); 
	fwrite($file_handle, "</$tag>\n\n"); 
}

?>