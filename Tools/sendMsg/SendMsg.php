<?php
namespace App\Tools\sendMsg;
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/6/22
 * Time: 下午2:54
 */
class SendMsg
{

    public function __construct($argv)
    {
        $this->sendSms($argv);
    }


    /*
	 * $argv=array( 'mobile'=>'13800138000', 'content'=>'sendsmscontent' );
	 */
    public function sendSms($argv)
    {
        $argv['content'] .= self::$sign;
        //file_put_contents('/tmp/send_msg'.date('Y-m-d').'.log',$argv['mobile'].":".$argv['content']."\n",FILE_APPEND);//added by whg
        static $registerFlag = false;

        if( $registerFlag === false ) {

            $registerArgv = $this->getRegisterArgv( $argv );
            $registerFlag = $this->register($registerArgv);
        }

        if( $registerFlag === false ) return false;

        $sendSmsArgv = $this->getSendsmsArgv( $argv );

        $params = $this->getPostStr($sendSmsArgv);

        $length = strlen($params);

        $fp = fsockopen("sdk2.entinfo.cn",80,$errno,$errstr,10);

        if( !$fp ) return false;

        $header = "POST /webservice.asmx/mdSmsSend_u HTTP/1.1\r\n";
        $header .= "Host:sdk2.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        $header .= $params."\r\n";

        fputs($fp,$header);
        $inheader = 1;
        while (!feof($fp)) {
            $line = fgets($fp,1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                // echo $line;
            }
        }

        preg_match('/<string xmlns=\"http:\/\/tempuri.org\/\">(.*)<\/string>/',$line,$str);
        $result=explode("-",$str[1]);

        if(count($result)>1)
            return false;
        else
            return true;
    }

    public function register($argv) {

        $params = $this->getPostStr($argv);

        $length = strlen($params);

        $fp = fsockopen("sdk2.entinfo.cn",80,$errno,$errstr,10);

        if( ! $fp ) return false;

        $header = "POST /webservice.asmx/Register HTTP/1.1\r\n";
        $header .= "Host:sdk2.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        $header .= $params."\r\n";

        fputs($fp,$header);
        $inheader = 1;
        while (!feof($fp)) {
            $line = fgets($fp,1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                // echo $line;
            }
        }
        $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
        $line=str_replace("</string>","",$line);
        $result=explode(" ",$line);

        if  ( $result[0]=="0")
            return true;   // one ok
        if ($result[0]=="-1")
            return true;   // resset ok
    }

    public function sendEmail($data)
    {
        return false;
    }

    private function getPostStr($argv) {

        $flag = 0;
        $params = '';
        foreach ($argv as $key=>$value) {

            if ($flag!=0) {
                $params .= "&";
                $flag = 1;
            }

            $params .= $key."=";
            $params .= urlencode($value);
            $flag = 1;
        }

        //$params = iconv( "UTF-8", "gb2312//IGNORE" ,$params);

        return $params;
    }

    private function getRegisterArgv( $argv ) {

        $sn  = $this->getSmsSn();
        $pwd = $this->getSmsPwd();
        $contact = $this->getSmsContact();

        $registerArgv = array(

            'sn'=>$sn,
            'pwd'=>$pwd,
        );

        return array_merge( $registerArgv, $contact );
    }

    private function getSendsmsArgv( $argv ) {

        $sn  = $this->getSmsSn();
        $pwd = $this->getSmsPwd();

        $sendSmsArgv = array(

            'sn'=>$sn,
            'pwd'=>strtoupper(md5($sn.$pwd)),
            'mobile'=>$argv['mobile'],
            'content'=>urlencode( $argv['content'] ),
            'ext'=>'',
            'rrid'=>'',
            'stime'=>''
        );

        return $sendSmsArgv;
    }

    private function getSmsSn()
    {
        return self::$sn;
    }

    private function getSmsPwd()
    {
        return self::$pwd;
    }

    private function getSmsContact()
    {
        return self::$contact;
    }
}