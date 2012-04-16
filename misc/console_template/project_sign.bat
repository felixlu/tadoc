@ECHO off

REM create chm
SETLOCAL ENABLEDELAYEDEXPANSION
@set BasePath=%~dp0

REM Docbook文件的路径
@set DocBookFilePath={DocBookFilePath}
REM Docbook所在的目录路径
@set DocBookPath={DocBookPath}
REM 目标目录
@set TargetPath={TargetPath}
@set TargetFile=%TargetPath%\{ProejctSign}.chm
@set TmpPath=%BasePath%\tmp\{ProejctSign}\chm\
@set XslPath=%BasePath%\misc\xsl-zh\chm\chm.xsl


set PATH=%PATH%;%BasePath%\bin\;%BasePath%\libs\;
%BasePath%\bin\xsltproc -o %TmpPath% --xinclude %XslPath% %DocBookFilePath%
mkdir %TmpPath%\images
mkdir %TmpPath%\css
if exist %DocBookPath%\images xcopy %DocBookPath%\images %TmpPath%\images  /Y
if exist %DocBookPath%\css xcopy %DocBookPath%\css %TmpPath%\css  /Y
xcopy %BasePath%\misc\images %TmpPath%\images  /Y
xcopy %BasePath%\misc\css\html\* %TmpPath%\css   /Y
regsvr32 /s %BasePath%\bin\itcc.dll
cd %TmpPath%
%BasePath%\bin\hhc.exe htmlhelp.hhp
cd %BasePath%

regsvr32 /s/u %BasePath%\bin\itcc.dll
mkdir %TargetPath%
cp %TmpPath%\docbook.chm %TargetFile%
ENDLOCAL

REM create pdf
SETLOCAL ENABLEDELAYEDEXPANSION
@set BasePath=%~dp0

@set DocBookFilePath={DocBookFilePath}
@set DocBookPath={DocBookPath}
@set TargetPath={TargetPath}
@set TargetFile=%TargetPath%\{ProejctSign}.pdf
@set TmpPath=%BasePath%\tmp\{ProejctSign}\pdf\
@set XslPath=%BasePath%\misc\xsl-zh\pdf\pdf.xsl

set PATH=%PATH%;%BasePath%\bin\;%BasePath%\libs\;

mkdir %TmpPath%
mkdir %TmpPath%\images
mkdir %TmpPath%\css
mkdir %TargetPath%

%BasePath%\bin\xsltproc --xinclude -o %TmpPath%\docbook.fo %XslPath% %DocBookFilePath%

set LOCALCLASSPATH=%BasePath%\libs\fop.jar;%BasePath%\libs\avalon-framework-4.2.0.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\batik-all-1.7.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\commons-io-1.3.1.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\commons-logging-1.0.4.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\serializer-2.7.0.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\xalan-2.7.0.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\xercesImpl-2.7.1.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\xml-apis-1.3.04.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\xml-apis-ext-1.3.04.jar
set LOCALCLASSPATH=%LOCALCLASSPATH%;%BasePath%\libs\xmlgraphics-commons-1.4.jar


if exist %DocBookPath%\images xcopy %DocBookPath%\images %TmpPath%\images  /Y /E
if exist %DocBookPath%\css xcopy %DocBookPath%\css %TmpPath%\css  /Y /E
xcopy %BasePath%\misc\images %TmpPath%\images  /Y /E
xcopy %BasePath%\misc\css\html %TmpPath%\css   /Y /E

java -cp %LOCALCLASSPATH% org.apache.fop.cli.Main -c %BasePath%\misc\fop.xconf  %TmpPath%\docbook.fo  -pdf %TmpPath%\docbook.pdf


cp %TmpPath%\docbook.pdf %TargetFile%
ENDLOCAL
explorer /e,{TargetPath}
