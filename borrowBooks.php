<?php
//借书|续借接口
include './conn/Function.php';

$dz_code = @$_POST['dz_code'];
$book_code = @$_POST['book_code'];
$code = @$_POST['code'];
$timesstamp = @$_POST['timesstamp'];
$postSign = @$_POST['sign'];

//加密验证
$str = $code.$book_code.$dz_code.$timesstamp.'NWQwOWEwMzJmZjZiYTdlMTUzMzFhZGNlYjgzNmQxMWEyZmU2NDlhNw==';
$sign = base64_encode(sha1($str));

$requestStr = "请求参数:".json_encode($_POST);
$class->Error_Log($requestStr,$str,$sign);

//if(!empty($book_code) || !empty($dz_code) || !empty($code) ){
//    echo $class->errorJson('400','无效的参数');
//    exit;
//}

if($sign !== $postSign){
    $class->Data_Log($class->Error_json_log('400','加密验证失败'));
    echo $class->errorJson('400','加密验证失败');
    exit;
}

//构造sql
//找到读者类型最大借阅数
$sql = "select
tbcGreadercardType.MaxLend,
tbGreader.ID,
tbcGrcardbook.BackTime,
tbcGrcardbook.ReTime,
tbcGrcardbook.ReNum
from tbGreader
INNER JOIN tbcGreadercardType ON tbcGreadercardType.ID = tbGreader.LibCardType
INNER JOIN tbcGrcardbook ON tbcGrcardbook.CTID = tbGreader.LibCardType
WHERE tbGreader.BarCode = '".$dz_code."'";

@$arr=$class->Return_Res($sql);//处理sql
@$LibCardType =@$arr[0]['MaxLend'];//最大借阅数
@$ReaderID =@$arr[0]['ID'];//读者ID
@$BackTime =@$arr[0]['BackTime'];//借书时长(天)
@$ReTime =@$arr[0]['ReTime'];//续借时长(天)
@$ReNum =@$arr[0]['ReNum'];//续借次数


//判断续借还是借书
if($code == 0){//借书
        //找到读者当前在借数量
            $sql = "select *
        from tbALTLend
        INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
        WHERE tbGreader.BarCode =  '".$dz_code."' and tbALTLend.IsReturn = 0";
            @$arr=$class->Return_Res($sql);//处理sql
            @$borrow_count = count($arr);//当前在借数量

            if($borrow_count >= $LibCardType){
                $class->Data_Log($class->Error_json_log('400','当前借书数量已达到上限'));
                echo $class->errorJson('400','当前借书数量已达到上限');
                exit;
            }

        //判断该书是否已被借
    $sql = "select top(1) ID
        from tbALTLend
        WHERE BarCode = '".$book_code."' and IsReturn = 0";
//    var_dump($sql);
    @$arr=$class->Return_Res($sql);//处理sql

//    var_dump($arr);

    //判断结果
    if($arr != null){
        $class->Data_Log($class->Error_json_log('400','该书已被借'));
        echo $class->errorJson('400','该书已被借');
        exit;
    }
//    var_dump($arr);exit;

        //找到流通库最后的id
            $sql = "select top(1) *
        from tbALTLend
        ORDER BY ID DESC";
            @$arr=$class->Return_Res($sql);//处理sql
            @$countId = $arr[0]['ID']+1;//新增的ID

        //插入借书流通数据
            @$LendTM = date('Y-m-d H:i:s');
            @$DueTm = date('Y-m-d H:i:s',time()+3600*24*$BackTime);
            @$PressDate = date('Y-m-d H:i:s',time()+3600*24*$BackTime);
            $sql = "INSERT INTO
        tbALTLend
        VALUES (
        '".$countId."',
        '".$ReaderID."',
         '".$book_code."',
         '".$LendTM."',
         '".$DueTm."',
         '".$PressDate."',
         0,
        0,
         1,
         1,
         'Waylib',
         null,
         null,
         null,
         0,
         2,
         '',
        '".$countId."',
         '',
         '',
         '',
         '',
        ''
         )";
            @$arr=$class->Return_Res($sql);//处理sql
            //判断结果
            if($arr !== null){
                $class->Data_Log($class->JSON('0000','借书成功',$arr));
                echo $class->JSON('0000','借书成功',$arr);
            }else{
                $class->Data_Log($class->Error_json_log('400','借书失败'));
                echo $class->errorJson('400','借书失败');
            }
}elseif($code == 1){//续借
    //找到读者当前在借记录
    $sql = "select
        CONVERT(varchar(19),tbALTLend.LendTM,120)as LendTM,
        CONVERT(varchar(19),tbALTLend.DueTm,120)as DueTm,
        CONVERT(varchar(19),tbALTLend.PressDate,120)as PressDate,
        tbALTLend.RenewTime,
        tbALTLend.ID
        from tbALTLend
        INNER JOIN tbGreader ON tbGreader.ID = tbALTLend.ReaderID
        WHERE tbALTLend.IsReturn = 0 and tbALTLend.BarCode = '".$book_code."'";
    @$arr=$class->Return_Res($sql);//处理sql
//    var_dump($arr);
    @$RenewTime = @$arr[0]['RenewTime'];//已续借次数
    @$DueTm = @$arr[0]['DueTm'];//应归还时间
    @$PressDate = @$arr[0]['PressDate'];
    @$tbALTLendID = @$arr[0]['ID'];
    if($RenewTime >= $ReNum){
        $class->Data_Log($class->Error_json_log('400','续借次数已达到上限'));
        echo $class->errorJson('400','续借次数已达到上限');
        exit;
    }else{
        //判断是否超期
        @$nowTime = date('Y-m-d H:i:s');

        if($nowTime > $DueTm){
            $class->Data_Log($class->Error_json_log('400','超期无法续借'));
            echo $class->errorJson('400','超期无法续借');
            exit;
        }else{
            @$newDueTm = date('Y-m-d H:i:s',strtotime($DueTm)+3600*24*$ReTime);//新应归还时间
            @$newPressDate = $newDueTm;
            @$newRenewTime = $RenewTime+1;
            @$sql = "UPDATE tbALTLend
        SET DueTm = '".$newDueTm."',PressDate =  '".$newPressDate."',RenewTime = ".$newRenewTime."
        WHERE ID = '".$tbALTLendID."'";
            @$arr=$class->Return_Res($sql);//处理sql
            //判断结果
            if($arr !== null){
                $class->Data_Log($class->JSONall('0000','续借成功',$arr));
                echo $class->JSON('0000','续借成功',$arr);
            }else{
                $class->Data_Log($class->Error_json_log('400','续借失败'));
                echo $class->errorJson('400','续借失败');
            }
        }
    }
}else{
    $class->Data_Log($class->Error_json_log('400','无效的code参数'));
    echo $class->errorJson('400','无效的code参数');
}







?>