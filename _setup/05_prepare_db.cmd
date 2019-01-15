@echo off



if not exist "%BLD%\_tmp\builder-autotester.s3db" (

	md "%BLD%\_tmp"
	copy "%BLD%\portable\DB\Empty_Kozijn.s3db" "%BLD%\_tmp\builder-autotester.s3db"

) else (

	echo WARNING: Skipping, file already exists ["%BLD%\_tmp\builder-autotester.s3db"]
	pause

)


echo.
echo [DONE]
if not "%1"=="silent" pause
