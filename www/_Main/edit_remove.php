<?php
	$id     = isset($_GET['id'])      ? $_GET['id']      : -1;
	$all    = isset($_GET['all'])     ? $_GET['all']     : 0;
	$number = isset($_POST['number']) ? $_POST['number'] : -1;
	$check  = isset($_POST['check'])  ? $_POST['check']  : -2;
	$remove = (0 <= $id) && ($number == $check);
	
	if ($remove) 
		echo  header('Refresh: 2; url=../_Main/index.php?all=$all');
	else
		echo  header("Refresh: 5; url=../_Main/edit.php?id=$id");  
?>

<html>

<head>
<title> MxK Builder edit </title>
</head>
<body>
	
<?php
	if ($remove) 
	{
		$xml_path = "../scripts/$id.xml";
		$srv_path = "../scripts/$id.srv";
		
		if (unlink($xml_path))
			echo "ID=$id.xml removed<br />";
		else
			echo "ERROR: ID=$id.xml remove failed<br />";

		if (is_file($srv_path))
		{
			if (unlink($srv_path))
				echo "ID=$id.srv removed<br />";
			else
				echo "ERROR: ID=$id.srv remove failed<br />";
		}
	}
	else
	{
		echo "<h1>ERROR in comfirmation code</h1>";
	}
?>

</body>
</html>
