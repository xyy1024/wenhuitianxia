<?php
return array(
	'VAR_FILTERS'=>'htmlspecialchars',
	// 'TOKEN_ON'=>true,
	// 'TOKEN_NAME'=>'__hash__',
	// 'TOKEN_TYPE'=>'md5',
	// 'TOKEN_RESET'=>true,
	'AUTH_CONFIG'=>array(
		'AUTH_ON' => true, //认证开关
		'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
		'AUTH_GROUP' => 'wa_authgroup', //用户组数据表名
		'AUTH_GROUP_ACCESS' => 'wa_authgroupaccess', //用户组明细表
		'AUTH_RULE' => 'wa_authrule', //权限规则表
		'AUTH_USER' => 'wa_admin'//用户信息表
	),

	'TMPL_PARSE_STRING' =>array(
		'__PUB__'=>__ROOT__.'/Public/Admin/',
		'__ROOTPUB__'=>__ROOT__.'/Public/',
		'__IMG__' => __ROOT__.'/Public/Admin/images/',
	),
	/* Cookie设置 */
	'COOKIE_EXPIRE'         => 0,    // Coodie有效期
	'COOKIE_DOMAIN'         => '',      // Cookie有效域名
	'COOKIE_PATH'           => '/',     // Cookie路径
	'COOKIE_PREFIX'         => 'wyHoutai_',      // Cookie前缀 避免冲突
	
	'SESSION_PREFIX'        => 'wyHoutai_', // session 前缀
	'DEFAULT_MODULE'	=> 'Public',
	//版本号 服务热线
	'SHENGXING' => array(
		'VERSION' => 'Version.15.7.1',
		'HOTTEL' => '010-62512434',
	),
	// 'TMPL_L_DELIM' => '<{', //修改左定界符
	// 'TMPL_R_DELIM' => '}>', //修改右定界符
	'CFG_CONf'		 =>'./Public/',
	'AUTH_CATEGORY' =>array(
		'1' => '用户管理',
		'2' => '商户管理',
		'3' => '关于我们',
		'4' => '资讯管理',
		'5' => '焦点图管理',
		'6' => '评论管理',
		'7' => '优惠券管理',
		'8' => '活动管理',
		'9' => '消息推送管理',
		'10' => '数据统计分析',
		'11' => '意见反馈管理',
		'12' => '日志管理',
		'13' => '系统管理',
		'14' => '权限设置',
	),
	'OPEN_POWER'=>'true',	//开启后台权限功能
	'MAP_API' => 'E3y4ERVZZ2ZZEwQ2eumDTbAN0hAI5ZbO',
);