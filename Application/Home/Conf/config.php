<?php
return array(
	//'配置项'=>'配置值'
    'TMPL_L_DELIM'          =>  '<{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}>',            // 模板引擎普通标签结束标记
    'URL_MODEL'             =>  2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Login', // 默认控制器名称
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '59.110.231.70', // 服务器地址
    'DB_PORT'               =>  '3306',        // 端口
    'DB_NAME'               =>  'auth',  // 数据库名
    'DB_PREFIX'             =>  'tp_',    //表前缀
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'wuyongqiang539', // 密码

    'PLATFORM_ID'           =>  1
);