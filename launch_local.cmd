@echo off
cd bin
start cmd /c run_httpd.cmd %~dp0bin/Apache/conf/httpd_local.conf
sleep 2
start http://127.0.0.1:1234
echo on

