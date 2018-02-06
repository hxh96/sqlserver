<?php
require('Config.php');

class  List_Book {

    protected $Database;
    protected $UID;
    protected $PWD;
    protected $serverName;
          
// 构造函数    
function __construct()
{
    $this->Database =SQLSVR_DATABASE;
    $this->UID = SQLSVR_USER;
    $this->PWD = SQLSVR_PASS;
    $this->serverName = SQLSVR_HOST;

}



// 连接数据库   
function  ConnectDb(){

    $connectionInfo = array( "Database"=>$this->Database, "UID"=>$this->UID, "PWD"=>$this->PWD);

    $conn = sqlsrv_connect( $this->serverName, $connectionInfo );

    if($conn){
      return $conn;
    }else{
      echo '数据库连接失败';
      exit();
    }

    
}

    //日志
function Error_Log($requestStr,$sign,$signDown)
{
    $str = "\n\n\n\n请求地址:".$_SERVER['PHP_SELF']."\n";
    $str .= "请求时间:".date('Y-m-d H:i:s')."\n";
    $str .= $requestStr."\n";
    $str .= "加密前:".$sign."\n";
    $str .= "加密后:".$signDown."\n";
    error_log($str, 3, './log/'.date('Y-m-d').'.log');
}

    //日志
    function Data_Log($data)
    {
        $str = "返回结果:".$data;
        error_log($str, 3, './log/'.date('Y-m-d').'.log');
    }


 //  处理结果集
function Return_Res($sql=''){
   $Data= $this->ConnectDb();
   
   $arr=array();

   $sql = iconv("utf-8", "gbk//ignore", $sql);//为了解决中文乱码问题

   if($result = sqlsrv_query($Data, $sql)){

      while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC,SQLSRV_SCROLL_NEXT )){
          $arr[]= $row;
      }
        
      return $arr;
    }

}




/*   sql 乱码*/
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
  {
        static $recursive_counter = 0;

        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
               $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
              //file_put_contents("E:/mylog.log", "原始:".$value."\r\n", FILE_APPEND);
                $value = @iconv("gbk//ignore", "utf-8", $value);
             //file_put_contents("E:/mylog.log", "utf-8:".$value."\r\n", FILE_APPEND);
                $array[$key] = $function($value);
             // file_put_contents("E:/mylog.log", "urlencode:".$array[$key]."\r\n", FILE_APPEND);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
  }



/* 转json */
function JSON($code,$msg,$array)
 {
     $re['code'] = $code;
     $re['message'] = $msg;
     $re['data'] = @$array[0];
      $this->arrayRecursive($re, 'urlencode', true);
      $json = json_encode($re);
      return urldecode($json);
  }

    /* 转json */
    function JSONall($code,$msg,$array)
    {
        $re['code'] = $code;
        $re['message'] = $msg;
        $re['data'] = $array;
        $this->arrayRecursive($re, 'urlencode', true);
        $json = json_encode($re);
        return urldecode($json);
    }

/* json处理错误 */
function errorJson($code,$msg)
{
    $re['code'] = $code;
    $re['message'] = urlencode($msg);
    $re = json_encode($re);
    return urldecode($re);
}

    //错误日志
    function Error_json_log($code,$msg){
        $re['code'] = $code;
        $re['message'] = urlencode($msg);
        $re = json_encode($re);
        return urldecode($re);
    }


}

$class= new List_Book();




		     



     

       
          

            
            
            
   
           
             

      
   

       
      

?>