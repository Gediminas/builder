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

class CGenerateHostLogPage extends CGeneratePage
{
	protected function GenerateHeadData() 
	{
		echo "\n<link rel='stylesheet' type='text/css' href='host_log.css' />\n\n";
		echo "\n<link rel='stylesheet' type='text/css' href='index.css' />\n\n";
		//echo "<script>\n";
		//echo "	function on_load() {\n";
		//echo "		setTimeout(window.location='#bottom', 2000);\n";
		//echo "	}\n";
		//echo "</script>\n\n";
	}
	
	protected function GenerateModule() 
	{
		$access_log = access_log();		
		$entries = file($access_log);
		$logs = Array();

		echo "<a href=index.php>back</a><br />\n";
		
		$ip       = isset($_GET['ip']) ? $_GET['ip'] : false;
		$filtered = isset($_GET['filtered']) ? $_GET['filtered'] : 1;
		$ext      = isset($_GET['ext']) ? $_GET['ext'] : false;
		
		///////////////////////////////////////////////////////////////

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

			if (!$ext && false !== stripos($data->url, "_Info/"))
				continue;

			if ($filtered &&
				!stripos($data->url, ".php") &&
				!stripos($data->url, ".html"))
				continue;

			if ($data->ip == $ip)
			{
				$logs[] = $data;
			}
		}

		///////////////////////////////////////////////////////////////

		$prev_date = '';
		
		echo "<hr/>\n";
		echo "$ip<hr/>\n";
		echo "<table border=0 style='border-spacing: 10px 0px;'>\n";


		foreach ($logs as $data)
		{
			if (!empty($prev_date) && $prev_date != $data->date)
			{
				echo "<tr class='separate'>\n";
			}
			else
			{
				echo "<tr>\n";
			}
			$prev_date = $data->date;

			$url = $data->url;
			$url = str_replace("POST ", "", $url);
			$url = str_replace("GET ",  "", $url);
			$url = str_replace(" HTTP/1.1",  "", $url);
			$url = substr($url, 1);
			
			$url_parts = explode('?', $url);
			$url1 = $url_parts[0];
			$url2 = isset($url_parts[1]) ? "?{$url_parts[1]}" : "";
			 
			echo "<td width= 90> {$data->date} </td>\n";
			echo "<td width= 50> {$data->time} </td>\n";
			echo "<td class='url_params'> <a class='url_main' href = ../$url>{$url1}</a>{$url2}</td>\n";
			echo "</tr>\n\n";
		   

		}
		echo "</table>\n";
		echo "</font>";
		echo "<hr/>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
		echo "<br/>";
	}
}

$gen_page = new CGenerateHostLogPage();
$gen_page->Generate();
?>
