<?php

function cleancmake($cmake_path)
{
	if ( !empty($cmake_path) ) 
	{
		$fileproj 		  	= "$cmake_path/ALL_BUILD.vcxproj";
		$filefilter 		  = "$fileproj.filters";
		$fileinstallcmake	= "$cmake_path/cmake_install.cmake";
		$filesln 			    = glob("$cmake_path/*.sln");
		
		if( file_exists($fileproj) && !unlink($fileproj) ) 
		{
			echo "'$fileproj' was not succesfully deleted \n";
		}
		
		if( file_exists($filefilter) && !unlink($filefilter) ) 
		{
			echo "'$filefilter' was not succesfully deleted \n";
		}
		
		if( file_exists($fileinstallcmake) && !unlink($fileinstallcmake) ) 
		{
			echo "'$fileinstallcmake' was not succesfully deleted \n";
		}

		if( !count($filesln) ) 
    {
			echo "ERROR: *.sln file not found \n";
      return;
    }
		
		$file 		= fopen($filesln[0], 'c+');
		$rows 		= array();
		$pattern 	= "/ALL_BUILD/";
		$i          = 7;										//Remove lines from file
		$start		= false;
		
    try {
      while (!feof($file)) 
      {
        $line = fgets($file);
        
        if($start && $i > 0) {
          $i--;
        }
        
        if (!preg_match($pattern, $line) && (!($start) || $i == 0)) {
          $rows[] = $line;
          $start 	= true;
        }
      }
    } catch (Exception $e) {
        echo 'Exception: ',  $e->getMessage(), "\n";
    }

		
		file_put_contents($filesln[0], implode($rows));
		fclose($file);
	}
}
?>
