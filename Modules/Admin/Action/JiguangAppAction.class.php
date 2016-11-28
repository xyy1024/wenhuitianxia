<?php
header('Content-Type:text/html;charset=utf-8');
/**
 * 极光推送
 */
class JiguangAppAction extends AppAction
{
    protected $type = '';
    /**
    * 模拟post进行url请求
    * @param string $url
    * @param string $param
    */
   /* private $_appkeys = '2187c1b0a9f9873752effe59';  //KEY
    private $_masterSecret = '3fe6f5e88281c23f60d02074';  //KEY 密码*/
    /**
     * AuroraPush运行主程序
     * @param   $u_id 接收id
     * @param   $q_id 推送记录id
     * @param   $tit 推送的标题
     * @return [type] [description]
     */
    public function AuroraPush($u_id='',$q_id='',$tit='')
    {
         /**   
        * 发送
        * @param int $sendno 发送编号。由开发者自己维护，标识一次发送请求
        * @param int $receiver_type 接收者类型。1、指定的 IMEI。此时必须指定 appKeys。2、指定的 tag。3、指定的 alias。4、 对指定 appkey 的所有用户推送消息。* @param string $receiver_value 发送范围值，与 receiver_type相对应。 1、IMEI只支持一个 2、tag 支持多个，使用 "," 间隔。 3、alias 支持多个，使用 "," 间隔。 4、不需要填
        * @param int $msg_type 发送消息的类型：1、通知 2、自定义消息
        * @param string $msg_content 发送消息的内容。 与 msg_type 相对应的值
        * @param string $platform 目标用户终端手机的平台类型，如： android, ios 多个请使用逗号分隔
        */
        $platform = 'android,ios'; // 接受此信息的系统
        $atime=date('y-m-d H:i:s');
        //$jpush=new Jiguang();
        $pp=array( 
            'n_id'=>"$q_id",    //此条推送消息的id   
            'n_content'=>"$tit", //推送的标题  
            'uid'=>"$u_id",//用户id
        );
        $msg_content=json_encode($pp);
        $num=2;//4 代表send的第三个参数可为空，否则不为空(全部推送)  2 指定某人推送
        $this->send(16,$num,$u_id,1,$msg_content,$platform);//进行推送
        $this->send(16,$num,$u_id,2,$msg_content,$platform);//进行推送
        /*$this->setSuccess(true);
        $this->setMsg('推送成功');
        $this->show($this->type);*/
        //echo '完成';
    }
    //极光推送demo
    public function send($sendno = 15,$receiver_type = 1, $receiver_value = "", $msg_type = 1, $msg_content = "", $platform = 'android,ios')
    {
        //echo $receiver_type;
        $url = 'http://api.jpush.cn:8800/sendmsg/v2/sendmsg';
        $param = '';
        $param .= '&sendno='.$sendno;
        $appkeys = $this->_appkeys;
        $param .= '&app_key='.$appkeys;
        $param .= '&receiver_type='.$receiver_type;
        $param .= '&receiver_value='.$receiver_value;
        $masterSecret = $this->_masterSecret;
        $verification_code = md5($sendno.$receiver_type.$receiver_value.$masterSecret);
        $param .= '&verification_code='.$verification_code;
        $param .= '&msg_type='.$msg_type;
        $param .= '&msg_content='.$msg_content;
        $param .= '&platform='.$platform;
        $res = $this->request_post($url, $param);
        $res_arr = json_decode($res, true);
      //print_r($res_arr);
    }
    public function request_post($url="",$param="") {
        if (empty($url) || empty($param)) {
        return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }

}
?>