<?php
/**
 * 帮助用户生成脚本
 */
ini_set('max_execution_time',0);
define('AP',dirname(dirname(dirname(__FILE__))));
define('DS',DIRECTORY_SEPARATOR);
ini_set('memory_limit','10M');

include(AP.DS.'misc'.DS.'script'.DS.'global.php');


TaConsole::getInstance(array('single_run'=>false));

TaConsole::put("欢迎使用TaDoc将DocBook转换成 chm 及 pdf 文件");
//项目的标志
$project_sign = TaConsole::get("请输入项目的标识，使用 字母，数据及下划线(_)（如: toaction）");
while(1){
	$ok= true;
	if(!is_name($project_sign)){
		TaConsole::put("没有找到符合要求的项目标志");
		$ok=false;
	}
	if(is_file(AP.DS.$project_sign.'.bat')){
		TaConsole::put("已经存在了同一项目的批处程序。");
		TaConsole::put("如果确认要重建。请先删除");
		$ok=false;
	}
	if($ok===true){
		break;
	}
	$project_sign = TaConsole::get("请输入项目的标识，使用字母，数据及下划线(_)，如: toaction");
}
//获取手册的所在地址
$docbook_file = TaConsole::get("请输入手册的DocBook文件地址（如：".AP.DS.'doc'.DS.'docbook'.DS.'ta_doc.xml'."）");
while(!is_file($docbook_file)){
	$_a = TaConsole::get("没有找到该文件。",array('Y'=>'重新输入','N'=>'不需要。','N'));
	if(strtoupper($_a) == 'N'){
		break;
	}
	$docbook_file = TaConsole::get("请输入手册的DocBook文件地址:");
}
//手册的目录
$docbook_path=dirname($docbook_file);
//获取目标生成的路径
$target_dir="$docbook_path".DS.'target';
while(1){
	$_dir = TaConsole::get("请输入生成文件的目录路径",null,$target_dir);
	if($_dir == $target_dir){
		break;
	}
	if(TaFile::mkdir($_dir)){
		$target_dir = $_dir;
		break;
	}
}

$code = readover(AP.DS."misc".DS."console_template\project_sign.bat");

$code = str_replace('{ProejctSign}',$project_sign,$code);
$code = str_replace('{DocBookFilePath}',$docbook_file,$code);
$code = str_replace('{DocBookPath}',$docbook_path,$code);
$code = str_replace('{TargetPath}',$target_dir,$code);
$bat_file = AP.DS.$project_sign.".bat";
writeover($bat_file,$code);
if(TaFile::exists_file($bat_file)){
	TaConsole::put("已经成功创建批处程序:{$bat_file}");
}else{
	TaConsole::put("创建文件失败,请检查目录是否有写入权限");
}
?>
