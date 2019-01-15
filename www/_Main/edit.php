<html><head>
<title> MxK Builder edit </title>
</head>

<body>
<?php 

require_once("../conf/conf_fnc.php");
require_once("../tools/builder_script_fnc.php");

$all         = isset($_GET['all']) ? $_GET['all'] : 0;
$provided_id = isset($_GET['id'])  ? $_GET['id']  : NULL;
$bcomment    = isset($_GET['bcomment'])  ? $_GET['bcomment']  : NULL;
$existing_id = NULL;
$xml         = NULL;

if (!is_null($provided_id) && strlen($provided_id) && file_exists("../scripts/$provided_id.xml"))
{
	if (get_product_info($provided_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script, $product_info))
	{
		$existing_id = $provided_id;
	}
}
	

echo "<a href='../_Main/index.php?all=$all'> [back]</a><hr/>\n\n";

//--------------- forma ---

if (is_null($existing_id))
{
	echo "<form action='../_Main/edit_save.php?all=$all' method='post'>\n";
	echo "<table border='0'>\n";
	echo "  <tr> <td> <b>New ID</b></td>";
	echo "       <td><INPUT NAME='create_id' value='$provided_id' size=10></td>\n";
	echo "       <td> <input type=\"submit\" value=\"Create\"/></td></tr>";
	echo "</table>\n";
	echo "\n</form> ";
	return;
}


//$product_name    = isset($xml->name)        ? trim($xml->name)        : "";
//$product_mutex   = isset($xml->mutex)       ? trim($xml->mutex)       : "";
//$product_comment = isset($xml->comment)     ? trim($xml->comment)     : "";
//$product_mailto  = isset($xml->mail_list)   ? trim($xml->mail_list)   : "";
//$product_script  = isset($xml->script)      ? trim($xml->script)      : "";

//$product_night   = isset($srv->night_build) ? trim($srv->night_build) : "0";
$product_priority   = $product_info['priority'];
//$product_enabled = isset($srv->enabled)     ? trim($srv->enabled)     : "";

//TEMP
if ($product_night == 'true')
	$product_night = 127;
elseif ($product_night == 'false')
	$product_night = 0;
//echo "( $product_night )";
//TEMP END

echo    "<form action='../_Main/edit_save.php?save_id=$existing_id&all=$all' method='post'>\n";

echo "<table border='0'>\n";
echo    "<tr>\n";
echo    "<td><b>ID</b></td>\n";
echo    "<td>$existing_id</td>\n";
echo    "</tr>\n";

echo    "<tr> <td><b>Name:</b></td>";
echo    "     <td><INPUT NAME='name' value='$product_name' size=100></td></tr>\n";

echo    "<tr> <td><b>Comment:</b></td>";
echo    "     <td><INPUT NAME='comment' value='$product_comment' size=185> </td></tr>\n";

echo    "<tr> <td><b>Mail list:</b></td>";
echo    "     <td><INPUT NAME='mail_list' value='$product_mailto' size=185> </td></tr>\n";

echo "</table>\n";


echo "<table border='0'>\n";
echo    "<tr> <td width=120><b>Enabled:</b>";

if ($product_enabled == 'true') 
	echo    " <input type='checkbox' name='enabled' checked value='true'>\n";
else 
	echo    " <input type='checkbox' name='enabled' value='false'>\n";

echo    "</td><td><b>Night build (weekdays):</b>";

echo    " <input type='checkbox' name='night_build_1' value=" . (($product_night &  1) ? "'true' checked" : "'0'") . ">\n";
echo    " <input type='checkbox' name='night_build_2' value=" . (($product_night &  2) ? "'true' checked" : "'0'") . ">\n";
echo    " <input type='checkbox' name='night_build_3' value=" . (($product_night &  4) ? "'true' checked" : "'0'") . ">\n";
echo    " <input type='checkbox' name='night_build_4' value=" . (($product_night &  8) ? "'true' checked" : "'0'") . ">\n";
echo    " <input type='checkbox' name='night_build_5' value=" . (($product_night & 16) ? "'true' checked" : "'0'") . ">\n";
echo    " | \n";
echo    " <input type='checkbox' name='night_build_6' value=" . (($product_night & 32) ? "'true' checked" : "'0'") . ">\n";
echo    " <input type='checkbox' name='night_build_7' value=" . (($product_night & 64) ? "'true' checked" : "'0'") . ">\n";

echo    "</td>";

echo    "<td width=60 align='right'><b>Priority:</b></td>\n";
echo    "<td><INPUT NAME='priority' value='$product_priority' size=10></td>\n";

echo    "<td width=100 align='right'><b>Mutex:</b></td>\n";
echo    "<td><INPUT NAME='mutex' value='$product_mutex' size=20></td>\n";
echo    "</tr>";

echo "</table>\n";

//script editbox
echo    "<textarea rows='30' cols='150' style='width:100%' name='script'>$product_script\n\n</textarea><br/>\n";


echo "<INPUT type='hidden' name='all'   value='" . $all . "'> \n";
echo "<INPUT type='hidden' name='product_id' value='" . $existing_id             . "'> \n";
echo "<INPUT type='hidden' name='bcomment'   value='" . rawurldecode($bcomment) . "'> \n";

echo      "<input type=\"submit\" value=\"Save\"/>";
echo      "<INPUT type='button' value='Commands' onClick=window.location.href='show_commands.php'>";
echo      "<INPUT type='button' value='Run' onClick=window.location.href='job_add.php?product_id=$provided_id'>\n";
echo      "</form> ";

$number = rand(10,99);

echo    "<form action='../_Main/edit_remove.php?id=$existing_id&all=$all' method='post'>\n";
echo 	"enter digit (<b>" . $number . "</b>) to comfirm remove: ";
echo 	"<INPUT NAME='check' value='' size=1>";
echo	"<INPUT type='hidden' NAME='number' value='" . $number . "' HIDDEN size=1>";
echo	"<input type='submit' value='Remove'/>";
echo      "\n</form> ";

?>
</body></html>