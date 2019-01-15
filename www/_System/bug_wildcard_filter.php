<?php

//Does not work when:
//  $name     = "xxxyyy"
//  $wildcard = "xxx*yyy"

function check($name, $file_wildcard_filter)
{
	$preg_filter    = str_replace("*", "([^<]+)", $file_wildcard_filter);
	$preg_filter    = "/^$preg_filter$/i";

	echo "$name _________ $file_wildcard_filter __________ $preg_filter _______ ";
	
	if (preg_match($preg_filter, $name, $matches))
	{
		echo "OK<br>\n";
	}
	else
	{
		echo "FAILED<br>\n";
	}
}

echo "<br/>\nShould be OK: <br/>\n";
check("MxDXF20.dll", "*.dll");
check("MxDXF20.dll", "MxDXF20.dll");
check("MxDXF20.dll", "MxDXF*.dll");
check("MxDXF20.dll", "*20.dll");

echo "<br/>\nShould be OK (FIXME): <br/>\n";
check("MxDXF20.dll", "MxDXF*20.dll");
check("MxDXF20.dll", "*MxDXF20.dll");
check("MxDXF20.dll", "MxDXF20.dll*");

echo "<br/>\nShould FAIL: <br/>\n";
check("MxDXF20.pdb", "*.dll");







?>