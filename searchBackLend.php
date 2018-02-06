<?php
//查询历史已还流通信息
include './conn/Function.php';

$dz_code = @$_POST['dz_code'];
//$book_code = @$_POST['book_code'];
$timesstamp = @$_POST['timesstamp'];
$postSign = @$_POST['sign'];


//加密验证
//$str = $book_code.$dz_code.$timesstamp.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
$str = $dz_code.$timesstamp.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
$sign = base64_encode(sha1($str));

$requestStr = "请求参数:".json_encode($_POST);
$class->Error_Log($requestStr,$str,$sign);

//if(!$dz_code && !$book_code){
if(!$dz_code){
    $class->Data_Log($class->Error_json_log('400','无效的参数'));
    echo $class->errorJson('400','无效的参数');
    exit;
}

if($sign !== $postSign){
    $class->Data_Log($class->Error_json_log('400','加密验证失败'));
    echo $class->errorJson('400','加密验证失败');
    exit;
}

////只传书籍条码
//if(!$dz_code){
////构造sql
//$sql = "select
//CONVERT(varchar(19),tbALTLend.LendTM,120)as CHECKTIME,
//CONVERT(varchar(19),tbALTLend.DueTm,120)as RETURNTIME,
//tbALTLend.BarCode as BARCODE,
//tbGreader.BarCode as DZCODE
//from tbALTLend
//INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
//WHERE tbALTLend.BarCode='".$book_code."'";
//}
//只传读者条码
//if(!$book_code){
//构造sql
    $sql = "select
CONVERT(varchar(19),tbALTLend.LendTM,120)as CHECKTIME,
CONVERT(varchar(19),tbALTLend.DueTm,120)as RETURNTIME,
tbALTLend.BarCode as bookcode,
tbGreader.BarCode as DZCODE
from tbALTLend
INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
WHERE tbGreader.BarCode LIKE '%".$dz_code."%' and tbALTLend.IsReturn = 1";
//}
//都传
//if($book_code && $dz_code){
//构造sql
//    $sql = "select
//CONVERT(varchar(19),tbALTLend.LendTM,120)as CHECKTIME,
//CONVERT(varchar(19),tbALTLend.DueTm,120)as RETURNTIME,
//tbALTLend.BarCode as BARCODE,
//tbGreader.BarCode as DZCODE
//from tbALTLend
//INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
//WHERE tbGreader.BarCode LIKE '%".$dz_code."%' And tbALTLend.BarCode = '".$book_code."'";
//}
//var_dump($sql);

@$arr=$class->Return_Res($sql);//处理sql


//判断结果
if($arr){
    $class->Data_Log($class->JSONall('0000','验证通过',$arr));
    echo $class->JSONall('0000','验证通过',$arr);
}else{
    $class->Data_Log($class->Error_json_log('400','未找到流通记录'));
    echo $class->errorJson('400','未找到流通记录');
}


?>