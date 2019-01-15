@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


pushd "%BLD%\portable\git\cmd"
PATH=%cd%;%PATH%
popd

echo Will clone repo to '%BLD_REPO%'
if not "%1"=="silent" pause


if not exist "%BLD_REPO%" (

	md "%BLD_REPO%"
	git clone git@vilnius:MxKBuilder.git "%BLD_REPO%"

) else (

	echo WARNING: Skipping, folder not empty ["%BLD_REPO%"]
	pause

)


echo.
echo [DONE]
if not "%1"=="silent" pause
