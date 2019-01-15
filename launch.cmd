@echo off
cd bin
start cmd /c run_httpd.cmd %~dp0bin/Apache/conf/httpd.conf
sleep 2
start http://localhost
echo on
