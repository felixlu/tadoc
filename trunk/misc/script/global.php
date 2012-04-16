<?php

!defined('TMP') && define('TMP', AP.'/tmp');
/**
 * 功能描述:检查字符串是否为字母,数字及"_"组成的字符串
 * @param string $string 检查的字符串
 */
function is_name($string) {
	if (is_string($string)) {
		return preg_match('/^[a-z][a-z0-9\_]*$/i', $string) > 0;
	} else {
		return false;
	}
}
/**
 * 错误信息显示
 *
 * @param string $msg 显示提示信息
 * @param bool $exit 是否停止程序.false是不停止.非false的值均为停止
 */
function error($msg='', $exit=false) {
	TaConsole::put('[error]'.$msg);
	if($exit!=false){
		exit;
	}
}
/**
 * 读取文件的全部内容
 *
 * @param string $filepath 文件路径
 * @param string $method 打开文件的方式,具体可查看fopen(),默认为'rb'
 * @return string 读取的文件内容
 */
function readover($filepath, $method='rb') {
	$filesize = filesize($filepath);
	$handle = fopen($filepath, $method);
	flock($handle, LOCK_SH);
	$filedb = fread($handle, $filesize);
	fclose($handle);
	return $filedb;
}

/**
 * 写入文件
 *
 * @param string $filepath 文件路径
 * @param string $data 写入的字符串
 * @param string $method 打开文件的方法,默认为'rb+'
 * @param bool $lock 是否加文件锁,非true的值,即为加共享锁;true即加独写锁
 */
function writeover($filepath, $data, $method='rb+', $lock=true) {
	touch($filepath);
	$handle = fopen($filepath, $method);
	$lock === true ? flock($handle, LOCK_EX) : flock($handle, LOCK_SH);
	fwrite($handle, $data);
	if ($method == "rb+")
		ftruncate($handle, strlen($data));
	fclose($handle);
}
class TaConsole
{
	/**
	 * php输入的资源
	 * @var
	 */
	var $stdin;
	/**
	 * php输出的资源
	 * @var
	 */
	var $stdout;
	/**
	 * php错误信息
	 * @var
	 */
	var $stderr;
	/**
	 * 接受到的数据
	 * @var
	 */
	var $args;
	/**
	 * 实例化的当前类的值
	 * @var
	 */
	static $_this;
	/**
	 *
	 * @param
	 * @return
	 **/
	function __construct($args) {
		$this->stdin = fopen('php://stdin', 'r');
		$this->stdout = fopen('php://stdout', 'w');
		$this->stderr = fopen('php://stderr', 'w');
		$this->args=$args;
		self::cklock();
		//开启独享锁,
		if($this->args['single_run']) {
			self::lock();
		}
	}
	/**
	 * 获取当前的实例
	 * @param array $args 命令行输入的参数
	 * @return console 的实例对像
	 **/
	public static function getInstance($args=array()) {
		if(!is_object(self::$_this)) {
			self::$_this=new self($args);
		}
		return self::$_this;
	}
	/**
	 * 检查执行锁
	 * @param string $name 执行锁名
	 * @return void
	 **/
	public static function cklock($name='') {
		global $timestamp;
		$lock_file=self::get_lock_file($name);
		if(file_exists($lock_file)) {
			//检查锁定文件的创建时间
			if($timestamp - filectime($lock_file)>1800){//如果已经创建超过30分钟,将会删除掉该
				self::unlock($name);
				error('auto remove lock file:'.$lock_file);
			}
			self::put("The program is running,please exit the original ");
			self::put("process re-implementation of the new procedures.");
			self::put("If there is no implementation of the program, ");
			self::put("you can delete the file.");
			self::put("file:");
			self::put($lock_file);
			exit;
		}
	}
	/**
	 * 获取执行锁的文件路径
	 * @param
	 * @return
	 **/
	public static function get_lock_file($name='')
	{
		if (!is_dir(TMP."/console")) {
			TaFile::mkdir(TMP."/console");
		}
		if(!is_name($name)) {
			$name='run';
		}
		return TMP."/console/lock_{$name}";
	}
	/**
	 * 得到程序保存的数据
	 * @param
	 */
	public static function get_data($name='') {
		$filedb=readover(self::get_data_file($name));
		$render=unserialize($filedb);
		return $render;
	}
	/**
	 * 设置程序中使用到的数据
	 * @param mix $data 需要保存的数据
	 * @param string $name 保存的名称
	 */
	public static function set_data($data,$name='') {
		$filedb=serialize($data);
		writeover(self::get_data_file($name),$filedb,'wb+');
	}
	/**
	 * 得到数据保存的文件路径
	 * @param string $name 保存数据的名称
	 * @return string 返回文件的全路径
	 */
	public static function get_data_file($name='') {
		if(!is_name($name)) {
			$name='run';
		}
		return TMP."/console/data_{$name}";
	}

	/**
	 * 进行程序单独执行锁
	 * @param string $name 程序锁的名称
	 * @return void
	 */
	public static function lock($name='') {
		writeover(self::get_lock_file($name));
	}
	/**
	 * 对程序锁进行解锁
	 * @param string $name 程序锁名称
	 * @return void
	 **/
	public static function unlock($name) {
		import('TaFile');
		TaFile::remove(self::get_lock_file($name));
	}
	/**
	 * 在命令行中输出内容
	 * @param string $content 输出的内容
	 * @param bool $newline 是否建立新行
	 * @return void
	 */
	public static function put($content, $newline = true) {
		$_this=self::getInstance();
		if ($newline) {
			fwrite($_this->stdout, $content . "\n");
		} else {
			fwrite($_this->stdout, $content);
		}
	}
	/**
	 * 输出错误信息
	 * @param string $string 错误的信息
	 * @return void
	 */
	public static function err($content) {
		$_this=self::getInstance();
		fwrite($_this->stderr, 'Error: '. $content);
	}
	/**
	 * 进行输入操作
	 * @param string $prompt输入进的提示语言
	 * @param array $options 选项提示
	 * @param string $default 默认值
	 * @return string 返回输入的内容
	 */
	public static function get($prompt, $options = null, $default = null) {
		$_this=self::getInstance();
		if (!is_array($options)) {
			$print_options = '';
		} else {
			$print_options = "\n";
			foreach( $options as $i=>$one) {
				$print_options .= "  $i) $one\n";
			}
		}

		if ($default == null) {
			$_this->put($prompt . " $print_options \n" . '> ', false);
		} else {
			$_this->put($prompt . " $print_options \n" . "[$default] > ", false);
		}
		$result = fgets($_this->stdin);

		if ($result === false) {
			exit;
		}
		$result = trim($result);

		if ($default != null && $result==null) {
			return $default;
		} else {
			return $result;
		}
	}
}


/**
 * 文件操作类
 * @author Huang Zhitian
 * @link http://atim.cn
 * @copyright Huang Zhitian
 * @version Svn $Id$
 * @package Files
 */
class TaFile {

    /**
     * 得到一个文件的后缀
     * @param string $filepath 文件的路径
	 * @return 文件的后缀
	 */
    public static function get_ext($filepath) {
        $ext = substr(strrchr(strtolower($filepath), '.'), 1);
        return $ext;
    }

    /**
     * 复制文件或目录
     * @param string $source 需要复制的源目录或者文件路径
     * @param string $dest 需要复制的目标目录或者文件路径
     * @param string $MoveType 复制的类型，file为复制文件，dir为复制目录
     * @return array array(是功与否,复制失败的文件路径,复制失败的目录路径)
     */
    public static function copy($source, $dest, $type='file') {
        $FailedFilePaths = array();
        $FailedDirPaths = array();
        $render = false;
        if ($type == 'dir') {//复制目录的处理方法
            if (self::exists_dir($source)) {
                $BasePath = self::get_format_path(realpath($source));
                if (!self::exists_dir($dest)) {
                    self::get_format_path($dest . "/");
                }
                if (self::mkdir($dest)) {
                    $FilesDetail = self::get_dir($source);
                    $count = count($FilesDetail['files']);
                    for ($i = 0; $i < $count; $i++) {
                        $DestFile = $dest . str_replace($BasePath, '', $FilesDetail['files'][$i]);
                        if (self::copy_file($FilesDetail['files'][$i], $DestFile) !== true) {
                            $FailedFilePaths[] = $FilesDetail['files'][$i];
                        }
                    }
                    $dcount = count($FilesDetail['dirs']);
                    for ($i = 0; $i < $dcount; $i++) {
                        $one = $FilesDetail['dirs'][$i];
                        if (!empty($one)) {
                            if (!self::exists_dir($one) && !self::mkdir($one)) {
                                $FailedDirPaths[] = $one;
                            }
                        }
                    }
                }
            } else {
                $FailedDirPaths[] = $source;
            }
        } else {//复制文件的方法
            if (self::exists_file($source)) {
                $BasePath = self::get_format_path($source, 1);
                if (strpos($dest, '.') === false) {
                    $DestFile = $dest . str_replace($BasePath, '', $source);
                } else {
                    $DestFile = $dest;
                }
                if (self::copy_file($source, $DestFile) !== true) {
                    $FailedFilePaths[] = $source;
                }
            } else {
                $FailedFilePaths[] = $source;
            }
        }
        if (empty($FailedFilePaths) && empty($FailedDirPaths)) {
            $render = true;
        }
        return array($render, $FailedFilePaths, $FailedDirPaths);
    }

    /**
     * 删除文件或者目录
     * @param string $source 需要删除的目录或者文件路径
     * @param string $type 删除的类型，file为删除文件，dir为删除目录
     * @return array array(是功与否,删除失败的文件路径,删除失败的目录路径)
     */
    public static function remove($source, $type='file') {
        $FailedFilePaths = array();
        $FailedDirPaths = array();
        $render = false;
        if ($type == 'dir') {
            if (self::exists_dir($source)) {
                $FilesDetail = self::get_dir($source, false);
                //删除所有文件
                $count = count($FilesDetail['files']);
                for ($i = 0; $i < $count; $i++) {
                    if (unlink($FilesDetail['files'][$i]) !== true) {
                        $FailedFilePaths[] = $FilesDetail['files'][$i];
                    }
                }
                $dcount = count($FilesDetail['dirs']);
                for ($i = $dcount - 1; $i >= 0; $i--) {
                    if (rmdir($FilesDetail['dirs'][$i]) !== true) {
                        $FailedDirPaths[] = $FilesDetail['files'][$i];
                    }
                }
                if (rmdir($source) !== true) {
                    $FailedDirPaths[] = $source;
                }
            }
        } else {
            if (is_array($source)) {
                foreach ($source as $one) {
                    if (self::remove_file($one) !== true) {
                        $FailedFilePaths[] = $one;
                    }
                }
            } else {
                if (self::remove_file($source) !== true) {
                    $FailedFilePaths[] = $source;
                }
            }
        }
        if (empty($FailedFilePaths) && empty($FailedDirPaths)) {
            $render = true;
        }
        return array($render, $FailedFilePaths, $FailedDirPaths);
    }

    /**
	 * 删除一个文件
	 * @param string $source 文件文件的路径
	 * @return bool 删除成功返回 true, 失败返回false
	 */
    public static function remove_file($source) {
        $render = true;
        if (self::exists_file($source)) {
            $render = false;
            if (unlink($source) === true) {
                $render = true;
            }
        }
        return $render;
    }

    /**
     * 复制一个文件
     * @param string $source 源文件路径
     * @param string $dest 目标文件路径
	 * @return bool 成功返回 true, 返回false
     */
    public static function copy_file($source, $dest) {
        $DestDir = self::get_format_path($dest, 1);
        if (self::mkdir($DestDir) === true) {
            if (copy($source, $dest) === true) {
                return true;
            } else {
                $data = readover($source);
                writeover($dest, $data);
                if (self::exists_file($dest)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 移动文件或者目录
     * @param string $source 需要移动的目录或者文件路径.如果移动目录，
     * @param string $dest 目标目录或目标文件
     * @param string $type 操作类型，dir,即为移动目录，file，即为移动文件
     * @return array array(是功与否,移动失败的文件路径,移动失败的目录路径)
     */
    public static function move($source, $dest, $type='file') {
        $Failedmove_filePaths = array();
        $FailedMoveDirPaths = array();
        $render = false;
        if ($type == 'dir') {//复制目录的处理方法
            if (self::exists_dir($source)) {
                $BasePath = self::get_format_path(realpath($source));
                if (!self::exists_dir($dest)) {
                    self::get_format_path($dest . "/");
                }
                if (self::mkdir($dest)) {
                    $FilesDetail = self::get_dir($source);
                    $SourceFile = $FilesDetail['files'];
                    $count = count($SourceFile);
                    for ($i = 0; $i < $count; $i++) {
                        $DestFile = $dest . str_replace($BasePath, '', $SourceFile[$i]);
                        if (self::move_file($SourceFile[$i], $DestFile) !== true) {
                            $Failedmove_filePaths[] = $SourceFile;
                        }
                    }
                    if (empty($Failedmove_filePaths)) {
                        $dcount = count($FilesDetail['dirs']);
                        for ($i = $dcount - 1; $i >= 0; $i--) {
                            $one = $FilesDetail['dirs'][$i];
                            if (!empty($one)) {
                                if (true !== ($r = rmdir($one))) {
                                    $FailedMoveDirPaths[] = $one;
                                }
                            }
                        }
                    }
                }
            } else {
                $FailedMoveDirPaths[] = $source;
            }
        } else {//复制文件的方法
            if (self::exists_file($source)) {
                $BasePath = self::get_format_path($source, 1);
                $SourceFile[] = $source;
                if (strpos($dest, '.') === false) {
                    $DestFile = $dest . str_replace($BasePath, '', $source);
                } else {
                    $DestFile = $dest;
                }
                if (self::move_file($source, $dest) !== true) {
                    $Failedmove_filePaths[] = $source;
                }
            } else {
                $Failedmove_filePaths[] = $source;
            }
        }
        if (empty($Failedmove_filePaths) && empty($FailedMoveDirPaths)) {
            $render = true;
        }
        return array($render, $Failedmove_filePaths, $FailedMoveDirPaths);
    }


    /**
     * 移动一个文件
     * @param string $source 源文件路径
     * @param string $dest 目标文件路径
	 * @return bool 成功返回 true, 返回false
     */
    public static function move_file($source, $dest) {
        $DestDir = self::get_format_path($dest, 1);
        if (self::mkdir($DestDir) === true) {
            if (copy($source, $dest) === true) {
                if (unlink($source) === true) {
                    return true;
                }
            } else {
                $data = readover($source);
                writeover($dest, $data);
                if (self::exists_file($dest)) {
                    if (unlink($source) === true) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
	 * 建立目录
	 * @param string $path 需要创建的目录地址
	 * @param octal $mode 值的权限,注意值为八进制格式,如: 0777, 0766等
	 * @return bool 成功返回 true, 返回false
     */
    public static function mkdir($path, $mode=0766) {
        if (self::exists_dir($path)) {
            if (!is_writeable($path)) {
                $old = umask(0);
                if (chmod($path, $mode) !== true) {
                    umask($old);
					error("Cannot be modified directory"
						." permissions ({$path})");
                    return false;
                }
                umask($old);
            }
        } else {
            $old = umask(0);
            if (mkdir($path, $mode, 1) !== true) {
                umask($old);
                error("Cannot be created directory ({$path})");
                return false;
            }
            umask($old);
        }
        return true;
    }
	/**
	 * 检查路径是否存在,不管是文件或者目录
	 * @param string $path 需要检查的路径
	 * @return bool/string 如果存在返回类型(目录的返回dir,文件的返回file),
	 *                     不存在返回false
	 */
	public static function exists($path){
		if( self::exists_dir($path)===true ){
			return 'dir';
		}elseif( exists_file($path)===true){
			return 'file';
		}
		return false;
	}
    /**
     * 检查是否存在目录
     * @param string $path 检查的目录路径
     * @return bool 存在返回true，不存在返回false
     */
    public static function exists_dir($path) {
        return is_dir($path);
    }

    /**
     * 检查文件是否存在
     * @param string $path 检查的文件路径
     * @return bool 存在返回true，不存在返回false
     */
    public static function exists_file($path) {
        return is_file($path);
    }


    /**
     * 读取目录下所以文件及目录的信息
     * @param string $path 需要读取信息的路径
     * @param array $without 不需要返回的目录名称
	 * @return array 返回文件及目录的路径(绝对路径)
     */
	public static function get_dir($path, $without=array('.svn','.git')) {
        if (!self::exists_dir($path)) {
            $path = dirname($path);
		}
		$path = self::get_format_path($path);
		if(substr($path,-1,1)=='/'){
			$path=substr($path,0,-1);
		}
        if ($without === false) {
            $without = array();
        }
		$dp = self::scandir($path);
        $files = array();
        $dirs = array();
        $count = count($dp);
        for ($i = 0; $i < $count; $i++) {
            $filename = $dp[$i];
            $_path = realpath("$path/$filename");
            if ($filename == '.' || $filename == '..' || in_array($filename, $without) || $_path === false) {
                continue;
            }
            if (is_dir($_path)) {
                $dirs[] = self::get_format_path($_path);
                $_termdir = self::get_dir($_path, $without);
                $dirs = array_merge($dirs, $_termdir['dirs']);
                $files = array_merge($files, $_termdir['files']);
            } else {
                $files[] = "$path/$filename";
            }
        }
        $result = array('files' => $files, 'dirs' => $dirs);
        return $result;
    }

    /**
     * 实现scandir同样功能的函数
     * @param string $dir 扫描的目录路径
     * @param integet $sorting_order	结果是否排序，如果为1即为倒序
     * @param context $context context参数的说明见手册中的 Streams API 一章
     * @return bool/array 返回目录下的所有文件及目录名称
     */
    public static function scandir($dir, $sorting_order=0, $context=false) {
        $files = false;
        if (!function_exists('scandir')) {
            if ($dh = opendir($dir, $context)) {
                while (false !== ($filename = readdir($dh))) {
                    $files[] = $filename;
                }
                closedir($dh);
                if ($sorting_order == 1) {
                    rsort($files);
                }
            }
        } else {
            if ($context === false) {
                $files = scandir($dir, $sorting_order);
            } else {
                $files = scandir($dir, $sorting_order, $context);
            }
        }
        return $files;
    }
}
?>