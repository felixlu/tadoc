@ECHO OFF


REM ִ�еĻ�������
set BasePath=%~dp0

set phpfile=%BasePath%\misc\script\main.php

REM ����php��װ��Ŀ¼
set phpPath=%BasePath%\bin
set configpath=%basepath%\misc\php.ini
set extdir=%phpPath%\ext

REM ����ϵͳ����
set PATH=%PATH%;%BasePath%\bin;%BasePath%\lib
REM ���г���
"%phpPath%\php.exe" -c "%configpath%" -d extension_dir="%extdir%" -q "%phpfile%" %*
pause