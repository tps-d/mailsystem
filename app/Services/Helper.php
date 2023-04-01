<?php

namespace App\Services;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class Helper
{

    /**
     * Display a given date in the active user's timezone.
     *
     * @param mixed $date
     * @param string $timezone
     * @return mixed
     */
    public function displayDate($date, string $timezone = null)
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->copy()->setTimezone($timezone);
    }

    public function str_starts_with($str, $start) {
        return (@substr_compare($str, $start, 0, strlen($start))==0);
    }

    public static function convertString2Num($card) {
        $card = strtoupper($card);
        $vc_len = 2;
        $dnum = 36;

        // 密码字典
        $cipherDic = [
            0  =>'0', 1  =>'1', 2  =>'2', 3  =>'3', 4  =>'4', 5  =>'5', 6  =>'6', 7  =>'7', 8  =>'8',
            9  =>'9', 10 =>'A', 11 =>'B', 12 =>'C', 13 =>'D', 14 =>'E', 15 =>'F', 16 =>'G', 17 =>'H',
            18 =>'I', 19 =>'J', 20 =>'K', 21 =>'L', 22 =>'M', 23 =>'N', 24 =>'O', 25 =>'P', 26 =>'Q',
            27 =>'R', 28 =>'S', 29 =>'T', 30 =>'U', 31 =>'V', 32 =>'W', 33 =>'X', 34 =>'Y', 35 =>'Z',
        ];
        // 反转数组
        $dedic = array_flip($cipherDic);
        // 去掉补值
        $id = ltrim($card, $dedic[0]);
        // 反转字符
        $id = strrev($id);
        // 初始化值
        $v = 0;
        // 遍历数据
        for ($i = 0, $j = strlen($id); $i < $j; $i++) {
            $v = bcadd(bcmul($dedic[$id {$i}], bcpow($dnum, $i, 0), 0), $v, 0);
        }

        return $v;
    }

    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
        $ckey_length = 4;
        $key = md5($key != '' ? $key : '123456');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
              return substr($result, 26);
            } else {
              return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }

    public function httpFetch($url,$method,$data = [])
    {
        if(!filter_var($url,FILTER_VALIDATE_URL)){
            throw new Exception('"'.$url.'" is not a valid URL');
        }

        $_post_data = http_build_query($data);
        $opts = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peername' => false
            ),
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n"."Content-Length: " . strlen($_post_data) . "\r\n",
                'content' => $_post_data
            )
        );

        return file_get_contents($url, false, stream_context_create($opts));
    }

    public function http_post_fetch_json($url,$data = [],$headers = [])
    {
        $client = new \GuzzleHttp\Client();

        $request = new \GuzzleHttp\Psr7\Request('POST', $url);

        $headers = array_merge([ 'Content-Type' => 'application/x-www-form-urlencoded'],$headers);
        $options = [
                'verify' => false,
                'allow_redirects' => false,
                'debug' => false,
                'headers' => $headers
        ];

        if($headers['Content-Type'] == 'application/json'){
            $options = array_merge($options,['json' => $data]);
        }else{
            $options = array_merge($options,['form_params' => $data]);
        }

        $response = $client->send($request, $options);
        $response_content = $response->getbody()->getContents();
        
        Log::build([
          'driver' => 'single',
          'path' => storage_path('logs/platform_request.log'),
        ])->info("POST ".$url.', data: '.json_encode($data) .", response:\n".$response_content);

        return \GuzzleHttp\json_decode($response_content, true);
        
    }

    public function http_get_fetch_json($url,$data = [],$headers = [])
    {
        $client = new \GuzzleHttp\Client();

        $request = new \GuzzleHttp\Psr7\Request('GET', $url);
        $response = $client->send($request, [
            'verify' => false,
            'allow_redirects' => false,
            'debug' => false,
            'headers' => $headers,
            'query' => $data
        ]);

        return \GuzzleHttp\json_decode($response->getbody()->getContents(), true);
        
    }
}
