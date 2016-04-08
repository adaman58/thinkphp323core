<?php
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE' => 'Home',
    'MODULE_DENY_LIST' => array('Common', 'User', 'Admin', 'Install'),
    'MODULE_ALLOW_LIST' => array('Home'),


    'DB_TYPE'               =>  'mongo',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'test',          // 数据库名
    'DB_USER'               =>  'hdcyx',      // 用户名
    'DB_PWD'                =>  'hdcyx',          // 密码
    'DB_PORT'               =>  '27017',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2, //URL模式
    'VAR_URL_PARAMS' => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR' => '/', //PATHINFO URL分割符

    'MP' => array(
        'APP_ID' => 'wx2359f58110dc66e0',
        'APP_SECRET' => '7e83638026e46aec964dd3f9af8a3f06',
        'GRANT_TYPE' => 'client_credential'),

    /* API域名 */
    'WEIXIN_DOMAIN_URL' => 'https://api.weixin.qq.com/cgi-bin',
    /* API路径 */
    'WEIXIN_API' => array(
        'GETTOKEN' => '/token',                                //获取token
        'GETJSAPI' => '/ticket/getticket',                    //获取jsapi

    ),
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__JS__' => __ROOT__ . '/Public/js',
        '__CSS__' => __ROOT__ . '/Public/css',
        '__IMG__' => __ROOT__ . '/Public/img',
    ),
);