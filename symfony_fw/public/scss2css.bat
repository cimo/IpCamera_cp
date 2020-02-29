@echo off

echo Scss2css - Windows

set source="%cd%"

sass --watch %source%\scss:%source%\css --style compressed

pause