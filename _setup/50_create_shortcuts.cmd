@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


xcopy _shortcuts "%USERPROFILE%\Desktop\BUILDER" /I
xcopy _shortcuts "%USERPROFILE%\Application Data\Microsoft\Internet Explorer\Quick Launch\" /I


echo.
echo [DONE]
if not "%1"=="silent" pause
