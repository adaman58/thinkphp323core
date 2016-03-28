<?php
return array(
    //'配置项'=>'配置值'
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL' => 2, //URL模式
    'VAR_URL_PARAMS' => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR' => '/', //PATHINFO URL分割符

    'SITE_DOMAIN' => 'wx.dreammove.cn',

    'MP' => array( // 微信公众号聚募网
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
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/img',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    ),
);