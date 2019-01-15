@echo off
pushd %~dp0


call 00_set_root.cmd %1 %2


echo *********************************************************
echo [SETUP STARTING]
echo *********************************************************
echo.
echo ------------------------------
echo Will use environment variables:
echo          BLD='%BLD%'
echo     BLD_REPO='%BLD_REPO%'
echo   BUILDERCFG='%BUILDERCFG%'
echo ------------------------------
echo.
echo.
pause


call 01_sync_time.cmd silent
call 02_get_portable.cmd silent
call 03_extract_portable.cmd silent
call 04_get_builder.cmd silent
call 05_prepare_db.cmd silent
call 06_prepare_conf.cmd silent
call 21_vc6_reg_dirs.cmd silent
call 50_create_shortcuts.cmd silent
call 70_run_TotalCmd.cmd
call 80_run_config.cmd silent


echo.
echo HTTPD will be launched now
echo Please modify builder configuration file NOW if needed
echo.
pause


call 90_run_httpd.cmd silent
call 95_run_firefox.cmd silent


popd
echo [SETUP FINISHED]
pause
