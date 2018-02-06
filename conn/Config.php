<?php
// 指定允许其他域名访问
header( "Access-Control-Allow-Origin:*" );
// 响应类型
header( "Access-Control-Allow-Methods:POST,GET" );
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

error_reporting(0);

// SqlServer 数据库配置文件

   define('SQLSVR_HOST','localhost');
   define('SQLSVR_USER','way'); // 用户名
   define('SQLSVR_PASS','way'); //密码
   define('SQLSVR_DATABASE','WD2000V30');// 数据库
?>

