<?php
//登陆验证接口
include './conn/Function.php';

$dz_code = @$_GET['dz_code'];
$pass = @$_GET['pass'];
//$timesstamp = @$_POST['timesstamp'];
//$postSign = @$_POST['sign'];

$requestStr = "请求参数:".json_encode($_GET);
$class->Error_Log($requestStr, '', '');

//构造sql
$sql = "select *
from tbGreader
where tbGreader.BarCode='".$dz_code."'";

@$arr=$class->Return_Res($sql);//处理sql
//验证读者证
if($arr){
    @$password = @$arr[0]['Password'];
    //加密验证
    @$newPass = $password.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
    @$newPass = base64_encode(sha1($newPass));
    if($newPass !== $pass){
        $class->Data_Log($class->Error_json_log('400','密码错误'));
        echo $class->errorJson('400','密码错误');
        exit;
    }else{
        //构造sql
        $sql = "select
            tbGreader.BarCode as DZ_CODE,
            tbGreader.Password as PASS_WORD,
            tbGreader.Tel as DZ_PHONE,
            CONVERT(varchar(19),tbGreader.EndDate,120)as LOSE_TIME,
            tbGreader.Address as DZ_ADDRESS,
            tbGreader.Sex as DZ_GENDER,
            tbGreader.UnitName as DZ_UNIT,
            tbGreader.Name as DZ_NAME,
            CONVERT(varchar(19),tbGreader.StartDate,120)as ADD_TIME,
            tbGreader.LevelR as DZ_LEVEL,
            tbGreader.IDCardNo as ID_CARD,
            tbGreader.Email as DZ_EMAIL,
            tbGreader.CurLend as HAS_LEND,
            tbGreader.Deposit as DZ_YAJIN,
            tbcGreaderIDtype.Memo as CRED_TYPE,
            tbGreader.BarCode as LEND_CODE
            from tbGreader
            INNER JOIN tbcGreaderIDtype ON tbcGreaderIDtype.ID = tbGreader.IDCardType
            where tbGreader.BarCode='".$dz_code."' and tbGreader.Password='".$password."'";

        @$arr=$class->Return_Res($sql);//处理sql
//判断结果
        if($arr){
//    var_dump($arr);exit;
            $class->Data_Log($class->JSON('0000','登录成功',$arr));
            echo $class->JSON('0000','登录成功',$arr);
        }else{
            $class->Data_Log($class->Error_json_log('400','登录失败'));
            echo $class->errorJson('400','登录失败');
        }
    }
}else{
    $class->Data_Log($class->Error_json_log('400','找不到该读者'));
    echo $class->errorJson('400','找不到该读者');
    exit;
}










////加密验证
//$str = $dz_code.$newPass.$timesstamp.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
//$sign = base64_encode(sha1($str));



//
//if(!$dz_code && !$pass){
//    echo $class->errorJson('400','无效的参数');
//    exit;
//}

//if($sign !== $postSign){
//    echo $class->errorJson('400','加密验证失败');
//    exit;
//}










?>