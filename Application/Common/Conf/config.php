<?php
return array(
		/* 模块相关配置 */
		'AUTOLOAD_NAMESPACE' => array('Addons' => ONETHINK_ADDON_PATH), //扩展模块列表
		'DEFAULT_MODULE'     => 'Home',
		'MODULE_DENY_LIST'   => array('Common','User','Admin','Install'),
		'MODULE_ALLOW_LIST'  => array('Home'),
		
	/* URL配置 */
	'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'            => 2, //URL模式
	'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
	'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符
		
    'BUYANK_USER' => array(
        'APP_ID' => 'wx2359f58110dc66e0',//wxa5ed2b2307175d85',
        'APP_SECRET' => '7e83638026e46aec964dd3f9af8a3f06',//014f36a6397fa0d4fe18e2279f9d2323',
        'GRANT_TYPE' => 'client_credential'),
    'DEFAULT_MODULE' => 'Home',
    /* API域名 */
    'WEIXIN_DOMAIN_URL' => 'https://api.weixin.qq.com/cgi-bin',
    /* API路径 */
    'WEIXIN_API' => array(
        'GETTOKEN' => '/token',                                //获取token
        'GETJSAPI' => '/ticket/getticket',                    //获取jsapi

    ),
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__JS__'     => __ROOT__ . '/Public/js',
        '__CSS__'     => __ROOT__ . '/Public/css',
        '__IMG__'     => __ROOT__ . '/Public/img',
    ),
);