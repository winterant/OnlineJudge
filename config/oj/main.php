<?php

// 该文件为系统默认配置，后台设置会将新配置保存在数据库中。
return [
	"siteName"	                => "Online Judge",  //网站名称
	"beian"                     => "",      //备案信息
    "APP_LOCALE"                => "en",    //网站前台默认语言

	"web_page_display_wide" 	=> true,    //宽屏模式
	"show_home_notice_marquee"  => true,    //首页顶部滚动显示一条公告

	"allow_register"	        => true,    //允许访客注册账号
	"login_reg_captcha"			=> true,    //登陆和注册时使用验证码
	"display_complete_userinfo" => true,    //对于未登录访客，个人信息页面 是否显示用户完整信息
	"display_complete_standings"=> true,    //对于未登录访客，排行榜页面 是否显示用户完整信息

	"guest_see_problem"	        => true,    //允许访客浏览题目内容
	"show_disscussions"			=> true,    //在题目页面显示讨论版
	"post_discussion"			=> false,   //允许普通用户在讨论版发表讨论

	"rank_show_school"	        => false,   //榜单显示学校
	"rank_show_class"			=> false,   //榜单显示班级
	"rank_show_nick"	        => true,    //榜单显示昵称（姓名）

	"penalty_acm"	            => 1200,    //竞赛acm模式错误一次的罚时，1200秒=20分钟
	"submit_interval"	        => 20,      //同一用户两次提交最小间隔，20秒
];
