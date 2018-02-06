<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15 0015
 * Time: 15:16
 */
header('Content-Type: text/html; charset=utf-8'); //网页编码




/*curl操作
 *url---会话请求URL地址
 *method---请求方式，有POST和GET两种，默认get方式
 *res---返回数据类型，有json和array两种，默认返回json格式
 *data---POST请求时的参数，数组格式
 */
function curlRequest( $url, $method='get', $data=array()){

    //初始化一个会话操作
    $ch = curl_init();

    //设置会话操作的通用参数
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_URL , $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //POST方式时参数设置
    if( $method == 'post' ) curl_setopt($ch, CURLOPT_POST, 1);

    if( !empty($data) ) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    //执行会话
    $data = curl_exec($ch);


    //关闭会话，释放资源
    curl_close($ch);

    if( @curl_errno($ch) ) {

        return curl_error($ch);//异常处理
    }



    //返回指定格式数据
    return $data;
}


function post_code($url,$data=false){
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt( $ch, CURLOPT_SAFE_UPLOAD, FALSE);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    if($data==true){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    }
    $res=curl_exec($ch);

    $err_code = curl_errno($ch);
    curl_close($ch);
    if($err_code!=0){
        echo $err_code;exit;
    }
    $data = json_decode($res,true);
    return $data;
}


$url = "http://118.122.132.153:90/waylib_api/returnLend.php";
$data = [
//    'searchStr' => '西',
//    'searchType' => 1,
//    'pageSize' =>10,
//    'page' =>1,
    'book_code' =>'51070000206541',
//    'dz_code' =>'01',
//    'book_code' =>'51070000178798',
    'timesstamp' =>'2018-02-02 12:17:21',
    'sign' =>'MDFhOTZlNDdiODM3YmEwMGY5ZDhlOWFmZTk1MzU3MzA5NmM2YWQ0OA=='
];
echo curlRequest( $url ,'post', $data);


//$newPass = '1NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
//$newPass = base64_encode(sha1($newPass));
//
//$url = "http://118.119.9.140:9521/libAPI_Yunlib/common/searchLendBook?dz_code=01&timesstamp=2018-01-31%2018:24:58&sign=ZDZhYzRkMzIyM2FlYzkwZDY4ZTdjNDMzODYxYTBhOWNkMjQwOTlhZQ==";
//echo curlRequest( $url );