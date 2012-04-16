<?php
/**
 * �����û����ɽű�
 */
ini_set('max_execution_time',0);
define('AP',dirname(dirname(dirname(__FILE__))));
define('DS',DIRECTORY_SEPARATOR);
ini_set('memory_limit','10M');

include(AP.DS.'misc'.DS.'script'.DS.'global.php');


TaConsole::getInstance(array('single_run'=>false));

TaConsole::put("��ӭʹ��TaDoc��DocBookת���� chm �� pdf �ļ�");
//��Ŀ�ı�־
$project_sign = TaConsole::get("��������Ŀ�ı�ʶ��ʹ�� ��ĸ�����ݼ��»���(_)����: toaction��");
while(1){
	$ok= true;
	if(!is_name($project_sign)){
		TaConsole::put("û���ҵ�����Ҫ�����Ŀ��־");
		$ok=false;
	}
	if(is_file(AP.DS.$project_sign.'.bat')){
		TaConsole::put("�Ѿ�������ͬһ��Ŀ����������");
		TaConsole::put("���ȷ��Ҫ�ؽ�������ɾ��");
		$ok=false;
	}
	if($ok===true){
		break;
	}
	$project_sign = TaConsole::get("��������Ŀ�ı�ʶ��ʹ����ĸ�����ݼ��»���(_)����: toaction");
}
//��ȡ�ֲ�����ڵ�ַ
$docbook_file = TaConsole::get("�������ֲ��DocBook�ļ���ַ���磺".AP.DS.'doc'.DS.'docbook'.DS.'ta_doc.xml'."��");
while(!is_file($docbook_file)){
	$_a = TaConsole::get("û���ҵ����ļ���",array('Y'=>'��������','N'=>'����Ҫ��','N'));
	if(strtoupper($_a) == 'N'){
		break;
	}
	$docbook_file = TaConsole::get("�������ֲ��DocBook�ļ���ַ:");
}
//�ֲ��Ŀ¼
$docbook_path=dirname($docbook_file);
//��ȡĿ�����ɵ�·��
$target_dir="$docbook_path".DS.'target';
while(1){
	$_dir = TaConsole::get("�����������ļ���Ŀ¼·��",null,$target_dir);
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
	TaConsole::put("�Ѿ��ɹ�������������:{$bat_file}");
}else{
	TaConsole::put("�����ļ�ʧ��,����Ŀ¼�Ƿ���д��Ȩ��");
}
?>
