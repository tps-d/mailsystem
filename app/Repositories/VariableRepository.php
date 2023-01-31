<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use App\Models\Variable;

use App\Facades\Helper;

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
        return $cache_json ? json_decode($cache_json,true) : [];
    }

    public function rebuildCache($workspaceId){
        $variables = $this->all($workspaceId)->mapWithKeys(function ($item, $key) {
            return [$item['name'] => $item];
        });

        $cache_list = $variables->all();

        Cache::forever('template_variables_'.$workspaceId, json_encode($cache_list));
    }

    public function flashVariableContent($workspaceId,$recipient_email,$variable){

        $tag_name = $variable['name'] ?? '';
        $value_type = $variable['value_type'] ?? '';
        $value_from = $variable['value_from'] ?? '';

        $time_limit = 600;

        switch($value_type){
            //随机数值
            case 1:
                $variableContent = \ShortCode\Random::get(6);
                break;

            //随机可逆字符串
            case 2:

                $variableContent = Helper::authcode($recipient_email,'ENCODE', 'workspace_'.$workspaceId );
                break;

            //随机可逆字符串带时间限制
            case 3:
                
                $variableContent = Helper::authcode($recipient_email,'ENCODE', 'workspace_'.$workspaceId , $time_limit);
                break;

            //固定值
            case 4:

                $value_from = $variable['value_from'] ?? null;
                if(!$value_from){
                    throw new Exception('No value for "'.$variable_content.'"');
                }

                $variableContent = $variable['value_from'];
                break;

            //Web hook
            case 5:

                $value_from = $variable['value_from'] ?? null;
                if(!$value_from){
                    throw new Exception('No value for "'.$variable_content.'"');
                }

                $_post_data = ['name' => $recipient_email];
                $result = Helper::httpFetch($value_from,'POST',$_post_data);
                $json_res = json_decode($result,true);
                if(!$json_res || !isset($json_res['value'])){
                    throw new Exception('"'.$value_from.'" get unknow result: '.$result);
                }

                $variableContent = $json_res['value'];

                break;
            default:
                 throw new Exception('unknow value_type for "'.$tag_name.'" with : '.json_encode($variable));
        }
        
        return $variableContent;
    }
}
