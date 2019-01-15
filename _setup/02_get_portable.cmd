@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


xcopy "\\ftp\ftproot\MxKBuilder\builder_portable_pack\*" "%BLD%\portable\" /E /C /I /H /R /Y

echo.
echo [DONE]
if not "%1"=="silent" pause
