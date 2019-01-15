@echo off

set GIT_CMD=%BLD%\portable\git\cmd
set GIT_BIN=%BLD%\portable\git\bin
set PATH

echo.
echo Will append user environment PATH with:
echo   '%GIT_BIN%'
echo   '%GIT_CMD%'
pause

setpath add "%GIT_BIN%"
setpath add "%GIT_CMD%"

echo.
echo [DONE]
pause