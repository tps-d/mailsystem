<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Repositories\WorkspacesRepository;


use App\Facades\MailSystem;
use App\Services\PlatformService;

class PlatformController extends Controller
{
    public $platformService;

    /** @var WorkspacesRepository */
    protected $workspaces;

    public function __construct(WorkspacesRepository $workspaces,PlatformService $platformService){
        $this->workspaces = $workspaces;
        $this->platformService = $platformService;

    }

    public function test(Request $request,$platform){

    }


    public function captcha_fetch(Request $request,$platform){

        $email = $request->get('name');
        if(!$email){
            throw new Exception('Invalid params');
        }

        $res = $this->platformService->setPlatform($platform)->getApiCaptcha($email);
        if(isset($res['code']) && $res['code'] == 200){
            return \Response([
                'value' => $res['data']["captcha"]
            ]);
        }else{
            return \Response($res);
        }
    }

    public function card_fetch(Request $request,$platform){
        $email = $request->get('name');
        if(!$email){
            throw new Exception('Invalid params');
        }

        $postage_id = 0;
        $count = 1;
        $type_day = "";
        $type_time = "";
        $expire_time = "";
        $remark = "";

        $res = $this->platformService->setPlatform($platform)->createApiCryptCard($postage_id,$count,$type_day,$type_time,$expire_time,$remark);
        if(isset($res['code']) && $res['code'] == 200){
            return \Response([
                'value' => $res['data']["code"][0]
            ]);
        }else{
            return \Response($res);
        }
    }


    public function card_list(Request $request){

        $platform = auth()->user()->currentWorkspace->name;
        //$platform = 'haiou';
        $res = $this->platformService->setPlatform($platform)->getApiPlanList();
        if(!isset($res['code']) || $res['code'] != 200){
            echo "无法获取";
            exit;
        }

        $html = '<div class="list-group">';
        foreach($res['data']['postage_list'] as $item){
            $vip = $item['type'] == 1 ? 'SVIP ' : 'VIP '; 
            $vip = $vip . $item['day'].'天 ';
            //$html.="<a href='javascript:;' class='list-group-item list-group-item-action' data-pid='".$item['id']."' data-day='".$item['day']."'>".$vip.strip_tags($item['desc'],'<del><i>')."</a>";
            $html .= "<div class='list-group-item list-group-item-action'><div class='d-flex w-100 justify-content-between'>
                <div class='d-flex align-items-center'>
              <input type='radio' id='postage_".$item['id']."' name='pid' value='".$item['id']."' data-day='".$item['day']."' />
              <label for='postage_".$item['id']."' style='margin-bottom:0; margin-left: 10px;'>".$vip.strip_tags($item['desc'],'<del><i>')."</label>
              </div>
              <div>有效 <input type='text' size='1' name='exp' value='1' /> 天</div>
            </div></div>";
        }
        $html .="</div>";

        echo $html;
    }



    public function code_check(Request $request,$platform){

        $code = $request->get('code');

        date_default_timezone_set('Asia/Shanghai');

        $timenow=time();

        if($code && strlen($code) == 10){
            /*
                "id": 1,
        "day": 30, #天数
        "code": "", #优惠码
        "type": 0, #0:折扣，1: 金额
        "value": 900, #折扣和金额已扩大100倍
        "remark": "", #额外说明
        "expire_time": 1676813993, #到期时间   
        */
            //$platform = 'haiou';
            $res = $this->platformService->setPlatform($platform)->getApiDiscountCodeInfo($code);
            if(isset($res['code']) && $res['code'] == 200){
                $has_expired = isset($res['data']['expire_time']) && $res['data']['expire_time'] < $timenow;

                if(empty($res['data']) || $has_expired){
                    return \Response([
                        'error' => '该优惠码已过期'
                    ]);
                }else{
                    $code = $res['data']['code'];
                    $day = $res['data']['day'];
                    $expire_time = $res['data']['expire_time'];
                    $type = $res['data']['type'];
                    $value = $res['data']['value'] / 100;

                    $message = "恭喜您获得 ${day}天套餐优惠码";
                    if($type){
                        $message .= ", 可以享受 -${value} 元价格减免优惠";
                    }else{
                        $message .= ", 可以享受 ${value} %价格折扣优惠";
                    }

                    
                    return \Response([
                        'code' => $code,
                        'day' => $day,
                        'expire' => date('Y-m-d H:m:s',$expire_time),
                        'type' => $type,
                        'value' => $value,
                        'message' => $message
                    ]);
                }
                
            }
        }elseif($code && strlen($code) == 12){

/*
    "data": {
        "id": 1,
        "postage_id": 12, #套餐id
        "day": 7, #此卡密对应的天数
        "cdkey": "keykey", #卡密值
        "expire_time": 1676813993, #卡密到期时间
        "used": 0, #卡密使用的时间
        "created": 1676813993, #卡密创建时间
    }
*/
            $res = $this->platformService->setPlatform($platform)->getApiCryptCardInfo($code);

            if(isset($res['code']) && $res['code'] == 200){

                $has_expired = isset($res['data']['expire_time']) && $res['data']['expire_time'] < $timenow;

                if(empty($res['data']) || $has_expired){
                    return \Response([
                        'error' => '该兑换码已过期或已被使用'
                    ]);
                }elseif(isset($res['data']['used']) && $res['data']['used'] == 1){
                    return \Response([
                        'error' => '该兑换码已被使用'
                    ]);
                }else{
                    $cdkey = $res['data']['cdkey'];
                    $day = $res['data']['day'];
                    $expire_time = $res['data']['expire_time'];
                    $message = "恭喜您获得 ${day} 天免费体验兑换码";
                    return \Response([
                        'code' => $cdkey,
                        'day' => $day,
                        'expire' => date('Y-m-d H:m:s',$expire_time),
                        'message' => $message
                    ]);
                }
            }
        }

        return \Response([
            'error' => '您输入的优惠码或兑换码不存在'
        ]);
    }
    

}
