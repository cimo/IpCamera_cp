@echo off

echo Uglifyjs - Windows

set source=%cd%\js\system

cd %source%

set minifiedFiles=*.min.js

for /r %source% %%a in (%minifiedFiles%) do (
    del %%a
)

set originalFiles=*.js

set count=0
for /r %source% %%a in (%originalFiles%) do set /a count+=1
echo Count: %count%

for /r %source% %%a in (%originalFiles%) do (
    uglifyjs %%~dpnxa --compress --mangle --output %%~dpna.min%%~xa
)

cd ..

pause