<?php
require_once("../common/header.php");

class CGenerateGitPage extends CGeneratePage
{
	protected function GenerateModule() 
	{
		$filter = isset($_GET['filter']) ? $_GET['filter']   : '';
		$repo   = isset($_GET['repo'])   ? $_GET['repo']     : 'git@vilnius:MxKozijn.git';

		$filter = rawurldecode($filter);

		set_time_limit(120);
		
		$root     = tmp_dir() . '\\mod_GIT\\';
		$dir      = str_replace(':', '_', $repo);
		$curr_dir = getcwd();

		if (!is_dir($root)&& !mkdir($root)) die ("ERROR: Cannot create [$root]");
		if (!chdir($root))	                die ("ERROR: Cannot go to [$root]");

		$repos = glob('git@*.git');
		
		///////////////////////////
		// INPUT
		echo "<form>\n";
		echo "<div>\n";

		echo "<select name='repo' size=1>\n";
		foreach ($repos as $tmp_repo)
		{
			$tmp_repo = str_replace('_', ':', $tmp_repo);
			echo "<option value='$tmp_repo' " . (($tmp_repo == $repo) ? "selected" : "") . "> $tmp_repo " . " </option>\n";
		}
		echo "</select>";

		echo "<input type='text' name='filter' size=50 value='" . $filter . "'> \n";
		echo "<input type='submit' value='show'>\n";
		echo "<font size=-2> ( Enter URL variable [repo] to clone e.g. [?repo=git@Server:SomeRepo.git] ) </font>\n";
		
		echo "</div>\n";
		echo "</form>\n";
		// END INPUT
		///////////////////////////

		if (!is_dir($dir) && !mkdir("$dir", 0777, true)) die ("ERROR: Cannot create [$dir]");

		if (!is_dir("$dir\\refs"))
		{
			echo "<font size=-2> Cloning [$repo] to [$dir] </font> </br>\n</br>\n";
			echo "<font size=-2> DO NOT KILL PROCESS...    </font> </br>\n</br>\n";
			
			$cmd_clone = "cmd /c git clone --bare \"$repo\" \"$dir\" 2>&1";
			passthru($cmd_clone);
		}
		else
		{
			chdir($dir);

			echo "<font size=-2> Fetching [$repo] </font> </br>\n";
			
			$cmd_pull = "cmd /c git fetch --force \"$repo\"2>&1";
			passthru($cmd_pull);
		}

		$cmd_log   = "git log -2000 --grep=\"$filter\" --no-merges --pretty=format:\"%ai~@~%an~@~%s~@~%h\" 2>&1";
		exec($cmd_log, $rows);
		chdir($curr_dir);

		if (!$rows)
		{
			echo "<h2>No commits found</h2>\n";
		}
		else
		{
			echo "<br/> <font size=-2> (max 2000 commits) </font> <br/><br/>\n";
			
			echo "<table border=0 cellspacing=0>\n";
			echo "<tr bgcolor=#BBFFFF>\n";
			echo "<td width=150> <center> <b> Date    </b> </center> </td>\n";
			echo "<td> <center> <b> Changes </b> </center> </td>\n";
			echo "<td> <center> <b> Author  </b> </center> </td>\n";
			echo "<td> <center> <b> Comment </b> </center> </td>\n";
			echo "</tr>\n";

			$link_dir = '';//dirname(__FILE__);
			$line_nr  = 0;

			foreach ($rows as $row)
			{
				$items   = explode('~@~', $row);
				$date    = $items[0];
				$author  = $items[1];
				$comment = $items[2];
				$hash    = $items[3];
				
				$date = date("Y-m-d H:i:s", strtotime($date));
				
				$prj_color='black';

				//$hash = '8c0a85e82c0c84b01b7048067d1a03175c6355d3';
				//$hash = str_ireplace("<!--$hash-->", "<b>Some build 1</b><br/><b>Some build 2</b><br/><b>Some build 3</b>", $hash);
				//$hash = 'bf010a49e6bdb77ec25fca6c04c0af547d814079';
				//$hash = str_ireplace("<!--$hash-->", "<b>Some other build 1</b><br/><b>Some other build 2</b><br/><b>Some other build 3<br/><b>Some other build 4</b>", $hash);
				

				$comment = str_ireplace('W30', " <font color='$prj_color'><b>W30</b></font>", $comment);
				$comment = str_ireplace('W31', " <font color='$prj_color'><b>W31</b></font>", $comment);
				$comment = str_ireplace('E16', " <font color='$prj_color'><b>E16</b></font>", $comment);
				$comment = str_ireplace('P19', " <font color='$prj_color'><b>P19</b></font>", $comment);
				$comment = str_ireplace('SP1', " <font color='$prj_color'><b>SP1</b></font>", $comment);
				$comment = str_ireplace('SP2', " <font color='$prj_color'><b>SP2</b></font>", $comment);
				$comment = str_ireplace('SP3', " <font color='$prj_color'><b>SP3</b></font>", $comment);
				$comment = str_ireplace(': ',  " <font color='$prj_color'><b>:  </b></font>", $comment);
				$comment = preg_replace('/#(\d+)/', '<a href="http://bugzilla.matrixlt.local/show_bug.cgi?id=$1"><b>$0</b></a>', $comment);

				$link    = 	"show_changes.php?hash=$hash";

				$style = $line_nr++ % 2; 
				echo "<tr class='d$style'>\n";
				echo "<td>                  $date         </td>\n";
				echo "<td align=right> <a href='$link'> $hash    </a> </td>\n";
				echo "<td>                  &nbsp;&nbsp;$author       </td>\n";
				echo "<td>                  &nbsp;&nbsp;$comment      </td>\n";
				echo "</tr>\n\n";
			}

			echo "</table>\n";
		}
	}
	protected function GenerateHeadData() 
	{
		echo "<style type='text/css'>";
		echo "tr.d0 td { background-color: white; color: black;   }";
		echo "tr.d1 td { background-color: #E0FFFF; color: black; }";
		echo "</style>";
	}
}

$gen_page = new CGenerateGitPage();
$gen_page->Generate();

?>

<?php

/*
The placeholders are:

      %H: commit hash
      %h: abbreviated commit hash
      %T: tree hash
      %t: abbreviated tree hash
      %P: parent hashes
      %p: abbreviated parent hashes
      %an: author name
      %aN: author name (respecting .mailmap, see git-shortlog(1) or git-blame(1))
      %ae: author email
      %aE: author email (respecting .mailmap, see git-shortlog(1) or git-blame(1))
      %ad: author date (format respects --date= option)
      %aD: author date, RFC2822 style
      %ar: author date, relative
      %at: author date, UNIX timestamp
      %ai: author date, ISO 8601 format
      %cn: committer name
      %cN: committer name (respecting .mailmap, see git-shortlog(1) or git-blame(1))
      %ce: committer email
      %cE: committer email (respecting .mailmap, see git-shortlog(1) or git-blame(1))
      %cd: committer date
      %cD: committer date, RFC2822 style
      %cr: committer date, relative
      %ct: committer date, UNIX timestamp
      %ci: committer date, ISO 8601 format
      %d: ref names, like the --decorate option of git-log(1)
      %e: encoding
      %s: subject
      %f: sanitized subject line, suitable for a filename
      %b: body
      %B: raw body (unwrapped subject and body)
      %N: commit notes
      %gD: reflog selector, e.g., refs/stash@{1}
      %gd: shortened reflog selector, e.g., stash@{1}
      %gs: reflog subject
      %Cred: switch color to red
      %Cgreen: switch color to green
      %Cblue: switch color to blue
      %Creset: reset color
      %C(…): color specification, as described in color.branch.* config option
      %m: left, right or boundary mark
      %n: newline
      %%: a raw %
      %x00: print a byte from a hex code
      %w([<w>[,<i1>[,<i2>]]]): switch line wrapping, like the -w option of git-shortlog(1).
*/
?>


