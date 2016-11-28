<?php
return array(
	'VAR_FILTERS'=>'htmlspecialchars',
	'TMPL_PARSE_STRING' =>array(
		'__IMG__'=>__ROOT__.'/Public/images/',
		'__CSS__'=>__ROOT__.'/Public/css/',
		'__JS__'=>__ROOT__.'/Public/js/',
	),
	/* Cookie设置 */
	'COOKIE_EXPIRE'         => 0,    // Coodie有效期
	'COOKIE_DOMAIN'         => '',      // Cookie有效域名
	'COOKIE_PATH'           => '/',     // Cookie路径
	'COOKIE_PREFIX'         => 'wyQiantai_',      // Cookie前缀 避免冲突

	'SESSION_PREFIX'        => 'wyQiantai_', // session 前缀
	'VERIFY_LIFE_TIME' => 5,	//短信验证码有效期
	// 'ERROR_PAGE'=>'/Public/error.html'
	// 'TMPL_EXCEPTION_FILE'=>'./Modules/Home/Tpl/Public/error.html'
	 
);
?>