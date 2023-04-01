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
        $platform = 'haiou';
        $res = $this->platformService->setPlatform($platform)->getApiCryptCardInfo();
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

    

}
