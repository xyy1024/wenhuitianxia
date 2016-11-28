<?php
/**
 * 判断是否是合法的电话格式，支持手机，区号，400客服
 */ 
function isTel($tel,$type='')
{
    $regxArr = array(
        'mobile'  =>  '/^(\+?86-?)?(18|15|13)[0-9]{9}$/',
        'tel' =>  '/^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/',
        '400' =>  '/^400(-\d{3,4}){2}$/',
    );
    if($type && isset($regxArr[$type]))
    {
        return preg_match($regxArr[$type], $tel) ? true:false;
    }
    foreach($regxArr as $regx)
    {
        if(preg_match($regx, $tel ))
        {
            return true;
        }
    }
    return false;
}
/**
 * 产生任意不重复的7位数字订单号
 * @param $t_id 服务类型的id
 */
 function createOdCode($t_id){
    return $t_id.date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
 }
/**
 * 产生随机数字的验证码
 */
function getVerifyCode($num = 6) {
    $nums = '';
    for ($i = 0; $i < $num; $i++) {
        $nums .= mt_rand(0, 9);
    }
    return $nums;
}
?>