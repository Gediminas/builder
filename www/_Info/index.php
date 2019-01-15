<?php
require_once("../common/header.php");

class CData
{
	public $date = "";
	public $time = "";
	public $ip   = "";
	public $host = "";
	public $url  = "";
	public $app  = "";
}

function resolve_host_static($ip)
{
	switch ($ip)
	{
	case '10.8.0.38':
		return 'gedas';
	default:
		return '-unknown-';
	}
}

function time_diff($s)
{
	$m = $h = $d = 0;
   
	if($s>59)
		$m = (int)($s/60);
   
	if($m>59){
		$h = (int)($m/60);
		$m = $m-($h*60);
	}
   
	if($h>23){
		$d = (int)($h/24);
		$h = $h-($d*24);
	}
   
	if ($d>0)
		$td = sprintf("%sd %02sh %02sm", $d, $h, $m);
	else if ($h>0)
		$td = sprintf("%sh %02sm", $h, $m);
	else if ($m>0)
		$td = sprintf("%sm", $m);
	else
		$td = "0s";

	return $td;
}

class CGenerateInfoPage extends CGeneratePage
{
	protected function GenerateHeadData() 
	{
		echo "<link rel='stylesheet' type='text/css' href='index.css' />\n";
	}
	
	protected function GenerateModule() 
	{
		$access_log = access_log();		
		$entries = file($access_log);
		$first = new CData;
		$last = new CData;
		$map = Array();

		///////////////////////////////////////////////////////////////
		$ext = isset($_GET['ext']) ? $_GET['ext'] : false;

		foreach ($entries as $entry) if (!empty($entry))
		{
			$parts = explode('[', $entry);
			if (1 == count($parts))
				continue;
		   
			$date_parts = explode(' ', $parts[1]);
		   
			$data = new CData;
			$data->date      = trim($date_parts[0], '] ');
			$data->time      = substr(trim($date_parts[1], '] '), 0, 5);
			$data->ip        = trim($parts[2], '] ');
			$data->full_host = trim($parts[3], '] ');
			$data->url       = trim($parts[4], '] ');
			$data->app       = trim($parts[5], '] ');

			if ($data->ip == $data->full_host)
			{
				$data->full_host = $data->host = resolve_host_static($data->ip);
			}
			else
			{		
				$host_parts = explode('.', $data->full_host);
				$data->host = trim($host_parts[0], '] ');
			}

			if (!$ext && false !== stripos($data->url, "_Info/"))
				continue;
				
			if (!stripos($data->url, ".php") &&
				!stripos($data->url, ".html"))
				continue;
			
			$map["{$data->host}-{$data->ip}"] = $data;
		   
			if (empty($first->date))
				$first = $data;
			   
			$last = $data;
		}

		ksort($map);

		///////////////////////////////////////////////////////////////

		$prev_host = "";
		$now_date  = date('Y-m-d');
		$now_time  = date('H:i');
		//$now = "$now_date $now_time";

		echo "<font face='Courier New'>";
		echo "<table border=0 style='border-spacing: 10px 0px;'>\n";
		echo sprintf("<tr><td> first </td><td> %s %s </td></tr>\n", $first->date, $first->time);
		echo sprintf("<tr><td> last  </td><td> %s %s </td></tr>\n", $last->date, $last->time);
		echo sprintf("<tr><td> now   </td><td> %s %s </td></tr>\n", $now_date, $now_time);
		echo "</table>\n";

		echo "<br \>\n";
		echo "<hr/>\n";

		echo "<table border=0 style='border-spacing: 10px 0px;'>\n";

		foreach ($map as $host_ip=>$data) if ($data->host != "localhost")
		{
			if (!empty($prev_host) && $prev_host != $data->host)
			{
				echo "<tr><td>&nbsp;</td></tr>\n";
			}
			$prev_host = $data->host;

			$time_diff = time_diff(strtotime("$now_date $now_time") - strtotime("$data->date $data->time"));
		   
			$url = $data->url;
			$url = str_replace("POST ", "", $url);
			$url = str_replace("GET ",  "", $url);
			$url = str_replace(" HTTP/1.1",  "", $url);
			$url = substr($url, 1);
			
			$url_parts = explode('?', $url);
			$url1 = $url_parts[0];
			$url2 = isset($url_parts[1]) ? "?{$url_parts[1]}" : "";
			 
			echo "<tr >\n";
			echo "<td nowrap='nowrap' width=250> {$data->full_host} </td>\n";
			echo "<td nowrap='nowrap' width= 90> <a href = host_log.php?ip={$data->ip}&filtered=1> {$data->ip} </a></td>\n";
			//echo "<td width= 90> {$data->ip} </td>\n";
			echo "<td nowrap='nowrap' width= 90> {$data->date} </td>\n";
			echo "<td nowrap='nowrap' width= 50> {$data->time} </td>\n";
			echo "<td nowrap='nowrap' width= 100 align=right><b> {$time_diff} </b></td>\n";
			echo "<td nowrap='nowrap' class='url_params'> <a class='url_main' href = ../$url>{$url1}</a>{$url2}</td>\n";
			//echo "<td><font size=-2> {$data->app} </font></td>\n";
			echo "</tr>\n\n";
		   

		}
		echo "</table>\n";
		echo "</font>";
		echo "<hr/>";
	}
}

$gen_page = new CGenerateInfoPage();
$gen_page->Generate();
?>
