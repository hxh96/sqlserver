<?php
//header('Content-Type: text/html; charset=gbk'); //网页编码
//header('Content-Type: text/html; charset=utf-8'); //网页编码
//返回书目信息接口
include './conn/Function.php';

$book_code = @$_POST['book_code'];
$timesstamp = @$_POST['timesstamp'];
//$timesstamp = date('Y-m-d H:i:s');
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
//$sql = "select
//tbAGNTTMarc.MarcID,
//convert(text,tbAGNTTMarc.content) as content,
//tbAGNRiches.BarCode
//from tbAGNRiches
//INNER JOIN tbAGNTTMarc ON tbAGNTTMarc.MarcID = tbAGNRiches.MarcID
//where tbAGNRiches.BarCode = '".$book_code."'";

$sql = "select
tbAGNTTMarc.MarcID,
convert(text,tbAGNTTMarc.content) as content,
tbAGNRiches.BarCode
from tbAGNRiches
INNER JOIN tbAGNTTMarc ON tbAGNTTMarc.MarcID = tbAGNRiches.MarcID
where tbAGNRiches.BarCode = '".$book_code."'";

//var_dump($sql);exit;
@$arr=$class->Return_Res($sql);//处理sql

//var_dump($arr);

@$dataArr = [];
foreach($arr as $k=>$v){
    @$data2['TITLE'] = $v['Title'];
    @$content = $v['content'];//获取marc数据
    @$toubiaoqu = substr($content,0,24);//头标区
    @$shujuqishi = substr($toubiaoqu,12,5);//数据起始
    @$chukaitoubiaoqu = substr($content,24);//除开头标区的内容
    @$marcDate = substr($content,(int)$shujuqishi);//marc数据区
    @$muciqu = substr($chukaitoubiaoqu,0,(int)$shujuqishi-1);//目次区
    @$marcDatecount = strlen($marcDate);//marc数据区长度
    @$muciqucount = strlen($muciqu);//目次区长度
    @$muciqugeshu = $muciqucount/12;//目次区除12
    //定义数组
    @$arr2 = [];
    for($i=0;$i<$muciqugeshu;$i++){//分隔追加目次数据
        @$data = substr($muciqu,12*$i,12);
        @array_push($arr2,$data);
    }

    //遍历目次区数组
    foreach($arr2 as $zz){
        @$ziduanmin = substr($zz,0,3);//字段名
        @$changdu = (int)substr($zz,3,4);//长度
        @$qishiweizhi = (int)substr($zz,7);//起始位置
        @$neirong = substr($marcDate,$qishiweizhi,$changdu);//起始位置

        @$book = explode(chr(31).'a',$neirong);
        @$book2 = explode(chr(31).'c',$neirong);
        @$book3 = explode(chr(31).'f',$neirong);
        @$book4 = explode(chr(31).'d',$neirong);

        switch ($ziduanmin)
        {
            case '200'://题名责任
                @$bookTitle = explode(chr(31),$book[1])[0];//书目标题
//                $data2['TITLE'] = $bookTitle;
                @$bookAuthor = explode(chr(30),$book3[1])[0];//作者
                @$bookAuthor = explode(chr(31),$bookAuthor)[0];//作者
                @$data2['AUTHOR'] = $bookAuthor;
             break;
            case '210'://出版发行
                @$chubandi = explode(chr(31),$book[1])[0];//出版地
                @$data2['PUBLISHERPLACE'] = $chubandi;
                @$chubanshe = explode(chr(31),$book2[1])[0];//出版社
                @$data2['PUBLISHER'] = $chubanshe;
                @$chubanriqi = explode(chr(30),$book4[1])[0];//出版日期
                @$chubanriqi = explode(chr(31),$chubanriqi)[0];//出版日期
                @$data2['PUBDATE'] = $chubanriqi;
                break;
            case '215'://载体形态
                @$yema = explode(chr(31),$book[1])[0];//页码
                @$data2['PAGES'] = $yema;
                break;
            case '101'://作品语种
                @$yuzhong = explode(chr(31),$book[1])[0];//语种
                @$yuzhong = explode(chr(30),$yuzhong)[0];//语种
                @$data2['LAGS'] = $yuzhong;
                break;
            case '010'://ISBN
                @$zz = explode(chr(30),$book[1])[0];
                @$isbn = explode(chr(31),$zz)[0];//isbn号
                @$data2['ISBN'] = $isbn;
                @$jiage = explode(chr(30),$book4[1])[0];//价格
                @$jiage = explode('CNY',$jiage)[1];//价格
                @$data2['PRICE'] = $jiage;
                break;
            case '690'://中图分类
                @$fenleihao = explode(chr(31),$book[1])[0];//分类号
                @$fenleihao = explode(chr(30),$fenleihao)[0];//分类号
                @$data2['CLASSIFICATION_NUM'] = $fenleihao;
                break;
            case '205'://版本项
                @$banci = explode(chr(31),$book[1])[0];//版次
                @$banci = explode(chr(30),$banci)[0];//版次
                @$data2['EDITION'] = $banci;
                break;
            default:
        }
    }
    @array_push($dataArr,$data2);
}



//判断结果
if($arr){
    $class->Data_Log($class->JSONall('0000','验证通过',$dataArr));
    echo $class->JSON('0000','验证通过',$dataArr);
}else{
    $class->Data_Log($class->Error_json_log('400','找不到该条码'));
    echo $class->errorJson('400','找不到该条码');
}


?>