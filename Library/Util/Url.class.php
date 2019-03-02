<?php

/**
 * Class Url
 * URL链接生成封装类，统一URL链接的生成方式
 * @author 张鹏玄 | <zhangpengxuan@yundun.com>
 */
class Url {


    static public function get_base_domain(){

        return getHost();

    }

    static public function build_args_str($args = array()){
        $args_str = '';
        if(is_array($args) && !empty($args)){
            foreach($args as $key => $val){
                $args_arr[] = "{$key}/{$val}";
            }
            $args_str = implode('/', $args_arr);
        }
        return $args_str ? '/'.$args_str : '';
    }

    static public function get_module_url($module = 'Index', $case_sensitive = false){
        $base_domain = self::get_base_domain();
        $module = $case_sensitive ? $module : strtolower($module);
        return "{$base_domain}/{$module}";
    }

    static public function get_action_url($module = 'Index', $action = 'Index', $case_sensitive = false){
        $base_domain = self::get_base_domain();
        $module = $case_sensitive ? $module : strtolower($module);
        $action = $case_sensitive ? $action : strtolower($action);
        return "{$base_domain}/{$module}/{$action}";
    }

    static public function get_function_url($module = 'Index', $action = 'Index', $function = 'Index', $args = array(), $case_sensitive = false){
        $base_domain = self::get_base_domain();
        $module = $case_sensitive ? $module : strtolower($module);
        $action = $case_sensitive ? $action : strtolower($action);
        $function = $case_sensitive ? $function : strtolower($function);
        $args_str = self::build_args_str($args);
        return "{$base_domain}/{$module}/{$action}/{$function}{$args_str}";
    }

    static function get_short_function_url($module = 'Index', $function = 'Index', $args = array(), $case_sensitive = false){
        $base_domain = self::get_base_domain();
        $module = $case_sensitive ? $module : strtolower($module);
        $function = $case_sensitive ? $function : strtolower($function);
        $args_str = self::build_args_str($args);
        return "{$base_domain}/{$module}/{$function}{$args_str}";
    }
}