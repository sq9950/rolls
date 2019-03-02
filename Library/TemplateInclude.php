<?php
/**
 * Desc: 模板加载类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/11/30 10:38
 */

namespace Library;

class TemplateInclude {

    private static $temp_base_dir = __VIEW__;

    /**
     * 加载单个模板文件
     * @param string $temp_file
     * @param string $temp_base_dir
     * @return string
     */
    static public function include_template($temp_file = '', $temp_base_dir = ''){
        $base_dir = empty($temp_base_dir) ? self::$temp_base_dir : $temp_base_dir;
        $temp_url = "{$base_dir}/{$temp_file}";
        if(is_file($temp_url)){
            $temp_content = file_get_contents($temp_url);
        }else{
            $temp_content = '';
        }
        return $temp_content;
    }

    /**
     * 批量加载模板文件
     * @param array $temp_files
     * @param string $temp_base_dir
     * @return string
     */
    static public function include_templates($temp_files = array(),  $temp_base_dir = ''){
        if(is_array($temp_files)){
            foreach($temp_files as $temp_file){
                $content_arr[] = self::include_template($temp_file, $temp_base_dir);
            }
            $temp_contents = implode('', $content_arr);
        }else{
            $temp_contents = '';
        }
        return $temp_contents;
    }
}
