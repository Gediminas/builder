<?php
//echo header("Refresh: 1; url=../_AT_old/index.php");

require_once("../common/header.php");

class CGenerateAtPage extends CGeneratePage
{
	protected function GenerateModule() 
	{
		//echo "<h1><center><font color='red'>Under construction</font></center></h1>";
		//echo "<h2><center><font color='red'><a href='../_AT_old/index.php'>GO TO OLD AUTOTESTER</a><hr/></font></center></h2>";
		//------------------------------

		require_once("tools.php");

		//menu_links(false); // menu links. No At link.

		//SQLite
		$group =  isset($_GET['group']) ? $_GET['group'] : "";

		$AT_path = autotester_db_path();
		//echo "[{$AT_path}] ";
		$db = DB_open($AT_path);

		$products  = get_products($db, $group);
		//print_r($products);
		
		$prod_capt = "Products";

		if (!$group || empty($group)) {
			echo "<a href='?group=1'>View grouped</a>";
		} else {
			echo "<a href='?group=0'>View un-grouped</a>";
		}


		echo "\n<center>\n";
		echo "<table border=1 ALIGN=center cellpadding=5>\n";
		echo "<caption><H2>".$prod_capt."</H2></caption>\n";
		echo "<tr ALIGN='center'>\n";
		echo "<td width='250'> <b> Product      </b> </td>\n";
		echo "<td width='050'> <b> Build        </b> </td>\n";
		echo "<td width='150'> <b> Date         </b> </td>\n";
		echo "<td width='060'> <b> Errors       </b> </td>\n";
		echo "<td width='100'> <b> Failed tests </b> </td>\n";
		echo "<td> <b> Last build user comment </b> </td>\n";
		echo "</tr>\n\n";
			
		$first_checked = 0;

		foreach ($products as $product)
		{
			// print list of made tests:
			$tmp1    = explode('###', $product);
			$product = $tmp1[0];
			$branch  = $tmp1[1];

			$info = get_product_last_test_info($db, $product, $branch);

			if (empty($info->user_comment))
				$info->user_comment = "-";

			$user_comment = CorrectUserComment($info->user_comment);

			echo "<tr>\n";

			$product_txt = str_replace('version ', '', $product);
			
			if (!empty($branch) && $branch != '-')
				$product_txt .= " [{$branch}]";
			else
				$product_txt = "<b>{$product_txt}</b>";
			
			//echo "<td><a href='autotester_list.php?LogTestShow=" . LogTestShow() . "&product={$product}&branch={$branch}&group={$group}'>$product_txt</a></td>\n";
			echo "<td><a href='autotester_list.php?product={$product}&branch={$branch}'  target='_blank'>$product_txt</a></td>\n";
			echo "<td align='right'>$info->build</td>\n";
			
			//info->test_started. green if new; yellow if older than 25hours; red if older than 3 days
			$bgcolor = ($info->test_started < date("Y-m-d G:i:s",mktime(date("G"), date("i"), date("s"), date("m")  , date("d")-3, date("Y")))) // 3 day before
							? ('#FF2222')
							: (($info->test_started < date("Y-m-d G:i:s",mktime(date("G")-1, date("i"), date("s"), date("m")  , date("d")-1, date("Y")))) // 1 day before
								? '#FFDD00'
								: '#00FF00');
								
			echo "<td bgcolor='$bgcolor'>";
			echo "<a href='autotester_log.php?id={$info->id}'>".$info->test_started."</a>";
			echo" </td>\n";
					
			$bgcolor = ($info->total_errors == 0) ? '#00FF00' : '#FF2222';
			
			echo "<td bgcolor='$bgcolor'>$info->total_errors </td>\n";
			echo "<td bgcolor='$bgcolor' align='left'>$info->tests_failed / $info->tests_count</td>";
			
			echo "<td> $user_comment </td>\n";

			echo "</tr>\n\n";
		}

		echo "</table></center>\n</form><br/>\n";
		echo "Date:<br/>\n";
		echo "green  - new<br/>\n";
		echo "yellow - older than 25hours<br/>\n"; 
		echo "red    - older than 3 days<br/>\n";
		/**/	
	}
}

$gen_page = new CGenerateAtPage();
$gen_page->Generate();

?>

