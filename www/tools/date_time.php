<?php

function format_time($seconds)
{
	$h = (int) ($seconds / 3600); 
	$m = (int) (($seconds / 60) - ($h * 60)); 
	$s = (int) ($seconds % 60 ); 
	
	$d = (int) ($h / 24);
	$h = (int) ($h % 24);
	
	$text = '';
	if ($d)
	{
		$text .= sprintf('%1d  d ', $d);
		$text .= sprintf('%02d h ', $h);
		$text .= sprintf('%02d m ', $m);
		$text .= sprintf('%02d s ', $s);
	}
	elseif ($h)
	{
		$text .= sprintf('%d h ',   $h);
		$text .= sprintf('%02d m ', $m);
		$text .= sprintf('%02d s ', $s);
	}
	elseif ($m)
	{
		$text .= sprintf('%d m ',   $m);
		$text .= sprintf('%02d s ', $s);
	}
	else
	{
		$text .= sprintf('%d s ',  $s);
	}
	
	return $text;
}

?>