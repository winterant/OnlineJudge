<?php

// 该文件为系统默认配置，后台设置会将新配置保存在数据库中。
return [
	"siteName"	                => "Ludong University Online Judge",  //网站名称
	"beian"                     => "",      //备案信息
    "owner"                     => "About Author",
    "owner_link"                => "http://demo.techaction.cn:8014/rg/details/11/369/889",
	"web_page_display_wide" 	=> true,    //宽屏模式
	"allow_register"	        => true,    //允许访客注册账号
	"show_home_notice_marquee"  => true,    //首页顶部滚动显示一条公告
	"guest_see_problem"	        => true,    //允许访客浏览题目内容
	"rank_show_school"	        => false,   //榜单显示学校
	"rank_show_nick"	        => true,    //榜单显示昵称（姓名）
	"penalty_acm"	            => 1200,    //竞赛acm模式错误一次的罚时，1200秒=20分钟
	"submit_interval"	        => 20,      //同一用户两次提交最小间隔，20秒
    "APP_LOCALE"                => "en"     //网站前台默认语言
];
