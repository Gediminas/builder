@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


if not exist "%BLD_REPO%\www\conf\conf.php" (

	pushd %BLD_REPO%\www\conf
	copy conf.php_ conf.php
	popd

) else (

	echo WARNING: Skipping, file already exists ["%BLD_REPO%\www\conf\conf.php"]
	pause

)



echo.
echo [DONE]
if not "%1"=="silent" pause
