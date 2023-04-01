<?php

declare(strict_types=1);

namespace App\Services;
use Exception;

use Illuminate\Support\Facades\Cache;
use App\Facades\Helper;

class PlatformService
{

    private $_platforms;
    private $_this_name;
    private $_this_platform;

    public function __construct(){
        $this->_platforms = config('platform.hosts');
    }

    public function setPlatform($value)
    {
        if(!isset($this->_platforms[$value])){
            throw new Exception('Platform value should not be provided in data.');
        }

        $this->_this_name = $value;
        $this->_this_platform = $this->_platforms[$value];
        return $this;
    }


#一、 验证码接口
#type: 0:注册.1:找回密码
#username: 邮箱或手机号(手机号(11位)会自动发送短信，邮箱需自行处理)
//curl https://api.fssxsd.com/api/web/common/captcha -X POST -H 'key: kuc3mnoobpzxuhrb' -d 'username=test@test.com&type=0' 
/*
#response
{
    "code": 200,
    "msg": "",
    "data": {
        "captcha": "1234"
    }
}*/
    public function getApiCaptcha($username,$type = 0){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $api_url = $this->_this_platform['api_url'];
        return Helper::http_post_fetch_json($api_url.'/api/web/common/captcha',['username'=>$username,'type'=>$type],['key'=>'kuc3mnoobpzxuhrb']);
    }

/*
#二、反馈
#content 反馈内容 (长度255)
#username 用户名 (如有多个联系信息可自行组装,例如reporter_name,reporter_email,reporter_contact)
#image_url 图片地址 (非必填)
curl https://api.fssxsd.com/api/web/feedback/info -X POST -d 'username=test@test.com&contect=test&image_url='
#response
{
    "code": 200,
    "msg": "",
    "data": {}
}
*/
    public function createApiFeedback($username,$content,$image_url=""){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $api_url = $this->_this_platform['api_url'];
        return Helper::http_post_fetch_json($api_url.'/api/web/feedback/info',[
            'username'=>$username,
            'content'=>$content,
            'image_url'=>$image_url
        ]);
    }

/*

海鸥: https://haiou02.kfdd.cc
如梭: https://rusuo02.kfdd.cc
橡树: https://xiangshu02.kfdd.cc
#生成卡密接口,需要用户通过api模拟后台用户登录，然后获取套餐信息，最后生成卡密
# 1、登录
# username: 用户名
# password: 密码

curl https://haiou02.kfdd.cc/api/web/login -X POST -d 'username=123&password=123' 
#response
{
    "code": 200,
    "data": {
        "id": 1,
        "token": "1111111", #有效期1天，以下所有请求都需携带token
        "username": "test"
    }
}
*/
    public function getApiToken(){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $cache_token = Cache::get('platform_token_'.$this->_this_name);
        if(!$cache_token){
            $api_url = $this->_this_platform['admin_url'];
            $res = Helper::http_post_fetch_json($api_url.'/api/web/login',[
                'username'=>$this->_this_platform['username'],
                'password'=>$this->_this_platform['password']
            ]);
            if(!$res || !isset($res['code']) || $res['code'] != 200){
                throw new Exception('get token failed from '.$this->_this_name);
            }

            $cache_token = $res['data']['token'];

            Cache::put('platform_token_'.$this->_this_name, $cache_token , now()->addMinutes(1430));
        }

        return $cache_token;
    }

/*    
#2、获取套餐信息
# 只需使用id和day字段

curl https://haiou02.kfdd.cc/api/web/crypt_card/info?postage=1 -X GET -H 'Authorization: ${token}'
*/
    public function getApiCryptCardInfo($page=1){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $token = $this->getApiToken();
        $api_url = $this->_this_platform['admin_url'];
        return Helper::http_get_fetch_json($api_url.'/api/web/crypt_card/info',[
            'postage' => $page
        ],[
            'Authorization' => $token
        ]);
    }

/*
#七、查看优惠码信息
#code: 优惠码
#token 注册或登录成功后返回的token值
#curl https://api.fssxsd.com/api/web/discount_code/info?code=${code} -X GET -H 'Authorization: ${token}'
#response

{
    "code": 200,
    "msg": "",
    "data": {
        "id": 1,
        "day": 30, #天数
        "code": "", #优惠码
        "type": 0, #0:折扣，1: 金额
        "value": 900, #折扣和金额已扩大100倍
        "remark": "", #额外说明
        "expire_time": 1676813993, #到期时间
        "created": 1676813993, #创建时间
    }
}
*/
    public function getApiDiscountCodeInfo($page=1){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $token = $this->getApiToken();
        $api_url = $this->_this_platform['admin_url'];
        return Helper::http_get_fetch_json($api_url.'/api/web/discount_code/info',[
            'postage' => $page
        ],[
            'Authorization' => $token
        ]);
    }

/*    
#3、生成卡密接口
# postage_id: 套餐id
# count: 生成个数
# type_day: 卡密类型. 0:无限制。1:1天，7:7天，30天
# type_time: 卡密限制次数. 
# expire_time: 10位时间戳
# remark: 说明

curl https://haiou02.kfdd.cc/api/web/crypt_card/info -X POST -H 'Authorization: ${token}' -d 'postage_id=5&count=3&type_day=1&type_time=1&expire_time=111111&remark=test' 
#reponse
{
    "code": 200,
    "msg": "",
    "data": {
        "code": ["123","456","789"]
    }
}
*/
    public function createApiCryptCard($postage_id,$count,$type_day,$type_time,$expire_time,$remark){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $token = $this->getApiToken();
        $api_url = $this->_this_platform['admin_url'];
        return Helper::http_post_fetch_json($api_url.'/api/web/crypt_card/info',[
            'postage_id'=>$postage_id,
            'count'=>$count,
            'type_day'=>$type_day,
            'type_time'=>$type_time,
            'expire_time'=>$expire_time,
            'remark'=>$remark
        ],[
            'Authorization' => $token
        ]);
    }

/*
#4、生成优惠码
# day: 天数,和套餐天数对应
# type: 类型 0:折扣. 1:金额
# value: 值,(需扩大100倍)
# use_type: 使用类型。 0:全部用户. 1:仅新用户. 2:仅老用户
# expire_time: 10位时间戳
# remark: 说明(选填)
#state: 0:正常. 1:禁用

curl https://haiou02.kfdd.cc/api/web/discount_code/info -X POST -H 'Authorization: ${token}' -d 'day=${day}&type=${type}&value=${value}&use_type=${use_type}&expire_time=${expire_time}&remarl=${remark}&state=${state}' 
#reponse
{
    "code": 200,
    "msg": "",
    "data": {
        "id": 2,
        "day": 30,
        "code": "xxxxxxx",
        "use_type": 0,
        "type": 1,
        "value": 2,
        "remark": "",
        "expire_time": 1111111111111,
        "state": 0,
        "created": 111111111111
    }
}直接念。别有自己的想法 直接按字念
*/

    public function createApiDiscountCard($day,$type,$value,$use_type,$expire_time,$remark,$state=0){
        if(!$this->_this_platform){
            throw new Exception('Platform value should not be provided in data.');
        }

        $value = $value * 1000;

        $token = $this->getApiToken();
        $api_url = $this->_this_platform['admin_url'];
        return Helper::http_post_fetch_json($api_url.'/api/web/discount_code/info',[
            'day'=> (int)$day,
            'type'=> (int)$type,
            'value'=> (int) $value,
            'use_type'=> (int) $use_type,
            'expire_time'=> (int)$expire_time,
            'remark'=>$remark,
            'state'=> (int)$state
        ],[
            'Authorization' => $token,
            'Content-Type' => 'application/json'
        ]);
    }
}