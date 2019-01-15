<?php
	$all =  $_GET['all'];
	header('Refresh: 45; url=../_Main/index.php?all='.$all);
?>

<html>
<head>
<title> MxK Builder Restart</title>
</head>
<body>

<?php
	require("../tools/builder_sys_fnc.php"); 
	
	echo "<center><h1>Restarting PC</h1></center>";
	$rez = run("shutdown -r -t 1"); 
?>

</body>
</html>