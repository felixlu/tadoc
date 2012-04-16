@ECHO OFF


REM 执行的基本程序
set BasePath=%~dp0

set phpfile=%BasePath%\misc\script\main.php

REM 定义php安装的目录
set phpPath=%BasePath%\bin
set configpath=%basepath%\misc\php.ini
set extdir=%phpPath%\ext

REM 设置系统变量
set PATH=%PATH%;%BasePath%\bin;%BasePath%\lib
REM 运行程序
"%phpPath%\php.exe" -c "%configpath%" -d extension_dir="%extdir%" -q "%phpfile%" %*
pause