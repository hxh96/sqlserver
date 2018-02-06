<?php
//还书接口
include './conn/Function.php';

$book_code = @$_POST['book_code'];
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
        CONVERT(varchar(19),tbALTLend.LendTM,120)as LendTM,
        CONVERT(varchar(19),tbALTLend.DueTm,120)as DueTm,
        CONVERT(varchar(19),tbALTLend.PressDate,120)as PressDate,
        tbALTLend.RenewTime,
        tbALTLend.ID
        from tbALTLend
        WHERE tbALTLend.BarCode =  '".$book_code."' and tbALTLend.IsReturn = 0";
@$arr=$class->Return_Res($sql);//处理sql
@$tbALTLendID = @$arr[0]['ID'];
@$DueTm = @$arr[0]['DueTm'];

if(!empty($arr)){
    @$ReturnTime = date('Y-m-d H:i:s');
    //判断是否超过应归还时间
    if($ReturnTime > $DueTm){
        $class->Data_Log($class->Error_json_log('400','超过应归还时间,请到图书馆归还'));
        echo $class->errorJson('400','超过应归还时间,请到图书馆归还');
        exit;
    }else{
        $sql = "UPDATE tbALTLend
        SET ReturnTime = '".$ReturnTime."',IsReturn = 1
        WHERE ID = '".$tbALTLendID."'";
        @$arr=$class->Return_Res($sql);//处理sql
        //判断结果
        if($arr !== null){
            $class->Data_Log($class->JSON('0000','还书成功',$arr));
            echo $class->JSON('0000','还书成功',$arr);
        }else{
            $class->Data_Log($class->Error_json_log('400','还书失败'));
            echo $class->errorJson('400','还书失败');
        }
    }


}else{
    $class->Data_Log($class->Error_json_log('400','该条码不存在借阅记录'));
    echo $class->errorJson('400','该条码不存在借阅记录');
}


?>