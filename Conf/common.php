<?php
//全局数据库配置文件
return [
    'DEFAULT_JSONP_HANDLER' => 'callback',

    //rbac认证配置
    'USER_AUTH_ON'          => true,
    'USER_AUTH_TYPE'        => 2,        // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY'         => 'adminV3AuthId',    // 用户认证SESSION标记
    'ADMIN_AUTH_KEY'        => 'administrator',
    'ADMIN_USER_ID'         => 22,      //超级管理员ID
    'USER_AUTH_MODEL'       => 'User',    // 默认验证数据表模型
    'AUTH_PWD_ENCODER'      => 'md5',    // 用户认证密码加密方式
    'USER_AUTH_GATEWAY'     => '/YundunAdminIndex/public/login',// 默认认证网关,非法访问时跳转页面
    'USER_LOGOUT_GATEWAY'   => '/YundunAdminIndex/public/logout',// 退出登录URL
    'NOT_AUTH_MODULE'       => 'Public,Index',    // 默认无需认证模块
    'REQUIRE_AUTH_MODULE'   => '',        // 默认需要认证模块
    'NOT_AUTH_ACTION'       => '',        // 默认无需认证操作
    'REQUIRE_AUTH_ACTION'   => '',        // 默认需要认证操作
    'GUEST_AUTH_ON'         => false,    // 是否开启游客授权访问
    'GUEST_AUTH_ID'         => 0,        // 游客的用户ID
    'DB_LIKE_FIELDS'        => 'title|remark',
    'RBAC_ROLE_TABLE'       => 'rbac_role',
    'RBAC_USER_TABLE'       => 'rbac_role_user',
    'RBAC_ACCESS_TABLE'     => 'rbac_access',
    'RBAC_NODE_TABLE'       => 'rbac_node',
    'VERIFY_CODE_KEY'       => 'adminV3Verify',    //后台验证码session保存键名
    'QINIU_CONFIG'          => [
        //七牛上传配置-测试环境配置
        'accessKey' => 'TbOklOe78xWDBuDp0DLXJMVDipBwOUjAXkYQThwB',
        'secretKey' => 'bmIO5pBEJg0HnIvjlOn6563sw_ST4XbEFR6bcws8',
        'bucket'    => 'jingwupublic',
        'domain'    => 'http://upload.qiniu.com',
    ],
];
