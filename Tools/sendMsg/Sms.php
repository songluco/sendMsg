<?php
namespace App\Tools\sendMsg;

/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/6/22
 * Time: 下午4:18
 */
class Sms
{
    /**
     * redis存储时间
     */
    const EXP_TIME = 180;

    /**
     * 替换符号
     */
    const FIND_STR = '%s';

    /**
     * 日志目录
     */
    const LOG_DIR = 'sms';

    /**
     * 验证码验证次数
     */
    const VERIFY_NUM = 30;

    /**
     * 发送手机验证码，不包含redis存储，
     * 适用发送特定信息，或多参数模板
     *
     * @param $mobile
     * @param $mark
     * @return bool
     */
    public static function sendSms($mobile)
    {
        $info = func_get_args();
        /**存储日志**/

        unset($info[0]);

        $argv['content'] = $info[1];
        $argv['mobile']  = $mobile;
        /**存储日志**/

        return self::handle($argv);
    }


    /**
     * 调用发送类
     * @param $argv
     * @return bool
     */
    public static function handle($argv)
    {
        /**非生产环境禁止验证码**/
        $status = false;
        if ($status || env('APP_ENV') === 'live'){
            $status = new SendMsg($argv);
            if(!$status){
                return false;
            }
        }
        return true;
    }

    /**
     * 模板替换
     * @param $mark
     * @param array $info
     * @return bool
     */
    public static function tmplateReplace($info = [])
    {
        $content = '';
        str_replace(self::FIND_STR,'',$content);
        $res = vsprintf($content,$info);
        return $res;
    }


}