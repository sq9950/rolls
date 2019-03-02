<?php
/**
 * Desc: 功能描述
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/9/10 17:49
 */
namespace Library\Util;
class Dir {

    const OPERATOR = '/';
    static protected $ret = ['status' => 0, 'info' => '操作失败'];

    /**
     * @node_name 创建目录
     * @param      $path_name    目录路径
     * @param bool $recursive    是否递归创建
     * @param int  $mode
     * @return array
     */
    static public function CreateDir($path_name, $mode = 0755, $recursive = true) {
        if (empty($path_name)) {
            return ['status' => 0, 'info' => '目录路径不能为空'];
        }
        if (file_exists($path_name)) {
            return ['status' => 1, 'info' => '文件已存在'];
        }
        $res = mkdir($path_name, $mode, $recursive);
        if ($res) {
            return ['status' => 1, 'info' => '创建成功'];
        }

        return ['status' => 1, 'info' => '目录创建失败：' . $path_name];
    }

    /**
     * @node_name 创建文件
     * @param      $path_file    文件路径
     * @param bool $recursive    是否递归创建
     * @param int  $mode
     * @return array
     */
    static public function CreateFile($path_file, $mode = 0755, $recursive = true) {
        if (empty($path_file)) {
            return ['status' => 0, 'info' => '文件路径不能为空！'];
        }
        if (file_exists($path_file) && is_file($path_file)) {
            return ['status' => 0, 'info' => '文件已存在！' . $path_file];
        }
        if (file_exists($path_file) && !is_file($path_file)) {
            return ['status' => 0, 'info' => '同名目录已存在！' . $path_file];
        }
        $path_name = dirname($path_file);
        if (!file_exists($path_name)) {
            $res = self::CreateDir($path_name, $mode, $recursive);
            if (!$res['status']) {
                return $res;
            }
        }
        $res = touch($path_file);
        if ($res) {
            return ['status' => 1, 'info' => '创建成功'];
        }

        return ['status' => 1, 'info' => '文件创建失败：' . $path_file];
    }

    /**
     * @node_name 清空目录内的文件和目录
     * @param string $dir
     * @return array
     */
    static public function ClearDir($dir = '') {
        if (empty($dir)) {
            return ['status' => 0, 'info' => '目录名不能为空'];
        }
        if (!function_exists('scandir')) {
            return ['status' => 0, 'info' => '很遗憾，scandir函数已被禁用'];
        }
        var_dump($dir);
        self::RecursiveClearDir($dir);

    }

    /**
     * @node_name 删除目录内的文件和目录
     * @param string $dir
     * @return array
     */
    static public function DeleteDir($dir = '') {
        if (empty($dir)) {
            return ['status' => 0, 'info' => '目录名不能为空'];
        }
        if (!function_exists('scandir')) {
            return ['status' => 0, 'info' => '很遗憾，scandir函数已被禁用'];
        }
        self::RecursiveClearDir($dir);
        rmdir($dir);

    }

    /**
     * @node_name 递归删除目录和文件
     * @param string $dir
     * @return bool
     */
    static private function RecursiveClearDir($dir = '') {
        static $level = 0;
        if(!is_readable($dir) || !is_writable($dir) || !is_executable($dir)){
            var_dump("递归删除目录需要读、写、执行权限:{$dir}");
            return false;
        }
        $level++;
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $full_path = $dir . "/" . $file;
                if (!is_dir($full_path)) {
                    $res = unlink($full_path);
                    if(!$res){
                        var_dump("删除{$full_path}失败！");
                    }
                } else {
                    self::RecursiveClearDir($full_path);
                }
            }
        }
        $level--;
        closedir($dh);
        if($level) {
            rmdir($dir);
        }
    }

}