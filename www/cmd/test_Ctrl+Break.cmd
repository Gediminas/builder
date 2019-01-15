set PID=%1
set SIG=%2
kill -f --signal %SIG% %PID%
