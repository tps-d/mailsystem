<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use App\Models\Variable;
use App\Facades\NumberConversion;

class VariableRepository extends BaseRepository
{
    /**
     * @var string
     */
    protected $modelName = Variable::class;

    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data)
    {
        $instance = $this->find($workspaceId, $id);

        $this->executeSave($workspaceId, $instance, $data);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($workspaceId, $id): bool
    {
        $instance = $this->find($workspaceId, $id);
        return $instance->delete();
    }

    public function getCache($workspaceId){
        $cache_json = Cache::get('template_variables_'.$workspaceId);
        return json_decode($cache_json,true);
    }

    public function rebuildCache($workspaceId){
        $variables = $this->all($workspaceId)->mapWithKeys(function ($item, $key) {
            return [$item['name'] => $item];
        });

        $cache_list = $variables->all();

        Cache::forever('template_variables', json_encode($cache_list));
    }

    public function flashVariableContent($name)
    {
        $content = "";



        switch($name){
            case 'CAPTCHA_CODE':
                // 加密
                $encryption = NumberConversion::generateCardByNum(32003, 4);
                echo $encryption;
                echo "<hr/>";
                // 解密;
                $decrypt = NumberConversion::generateNumByCard("gjv5r6vhhvASFBSWW#" );
                echo $decrypt;



                break;
            case 'EXCHANGE_CODE':

                $psa= NumberConversion::encode_pass("woshi ceshi yong de ","123","encode",64);
                echo $psa;
                echo "<hr/>";
                echo NumberConversion::encode_pass($psa,"123",'decode',64);
                // code...
            case 'COUPON_CODE':

                $psa= NumberConversion::alphabet_to_number("cmworldcom");
                echo $psa;
                echo "<hr/>";
                echo NumberConversion::number_to_alphabet($psa);
                break;
            case 'LAST_OFFICE_URL':
                // code...
                break;
            case 'LAST_DWONLOAD_URL':
                // code...
                break;
            case 'LAST_QRCODE_IMG':
                // code...
                break;
            default:

        }

        return $content;
    }
}
