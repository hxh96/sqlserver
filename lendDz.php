<?php
//根据图书条码返回流通记录接口
include './conn/Function.php';

$book_code = @$_POST['book_code'];
//$book_code = 51070000178798;
$timesstamp = @$_POST['timesstamp'];
$postSign = @$_POST['sign'];



//加密验证
$str = $book_code.$timesstamp.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
$sign = base64_encode(sha1($str));

$requestStr = "请求参数:".json_encode($_POST);
$class->Error_Log($requestStr,$str,$sign);

if(!$book_code){
    $class->Data_Log($class->Error_json_log('400','无效的参数'));
    echo $class->errorJson('400','无效的参数');
    exit;
}

if($sign !== $postSign){
    $class->Data_Log($class->Error_json_log('400','加密验证失败'));
    echo $class->errorJson('400','加密验证失败');
    exit;
}

//构造sql
$sql = "select
CONVERT(varchar(19),tbALTLend.LendTM,120)as CHECKTIME,
CONVERT(varchar(19),tbALTLend.DueTm,120)as RETURNTIME,
tbALTLend.BarCode as BARCODE,
tbGreader.BarCode as DZ_CODE,
tbGreader.Password as PASS_WORD,
tbGreader.Password as PASSWORD,
tbGreader.IDCardNo as ID_CARD,
tbGreader.Tel as DZ_PHONE,
tbGreader.Name as DZ_NAME,
tbGreader.Sex as GENDER
from tbALTLend
INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
WHERE tbALTLend.BarCode='".$book_code."' and tbALTLend.IsReturn = 0" ;



@$arr=$class->Return_Res($sql);//处理sql

foreach($arr as &$v){
    if($v['GENDER'] == 1){
        $v['GENDER'] = '男';
    }else{
        $v['GENDER'] = '女';
    }
    $v['PASS_WORD'] =base64_encode(sha1($v['PASS_WORD'].'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw=='));
    $v['PASSWORD'] =base64_encode(sha1($v['PASSWORD'].'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw=='));
}

//判断结果
if($arr){
    $class->Data_Log($class->JSON('0000','验证通过',$arr));
    echo $class->JSON('0000','验证通过',$arr);
}else{
    $class->Data_Log($class->Error_json_log('400','图书条形码错误'));
    echo $class->errorJson('400','图书条形码错误');
}


?>