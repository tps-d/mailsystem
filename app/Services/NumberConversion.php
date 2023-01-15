<?php

namespace App\Services;

class NumberConversion
{

    // 进制数
    private static $dnum = 36;
    // 前缀值
    private static $pre = 'sy';
    // 验长度
    private static $vc_len = 2;
    // 密码字典
    private static $cipherDic = [
        0  =>'0', 1  =>'1', 2  =>'2', 3  =>'3', 4  =>'4', 5  =>'5', 6  =>'6', 7  =>'7', 8  =>'8',
        9  =>'9', 10 =>'A', 11 =>'B', 12 =>'C', 13 =>'D', 14 =>'E', 15 =>'F', 16 =>'G', 17 =>'H',
        18 =>'I', 19 =>'J', 20 =>'K', 21 =>'L', 22 =>'M', 23 =>'N', 24 =>'O', 25 =>'P', 26 =>'Q',
        27 =>'R', 28 =>'S', 29 =>'T', 30 =>'U', 31 =>'V', 32 =>'W', 33 =>'X', 34 =>'Y', 35 =>'Z',
    ];

    /**
     * 数字转换
     * @param int $int 数字
     * @param int $format 格式长度
     * @return string
     */
    public static function encodeID($int, $format = 6) {
        // 初始化值
        $arr = [];
        // 处理状态
        $loop = true;
        // 数据处理
        while ($loop) {
            //try{
                $arr[] = self::$cipherDic[intval(bcmod($int, self::$dnum))];
            // }catch(\Exception $e){
            //     echo $int.'--'.self::$dnum;die;
            // }
            $int = bcdiv($int, self::$dnum, 0);
            if ($int == '0') {
                $loop = false;
            }
        }

        // 长度补位
        if (count($arr) < $format)
        {
            $arr = array_pad($arr, $format, self::$cipherDic[0]);
        }

        // 数据转换
        return implode('', array_reverse($arr));
    }

    /**
     * 卡号转换
     * @param string $card 卡号
     * @return int
     */
    public static function decodeID($card) {
        // 反转数组
        $dedic = array_flip(self::$cipherDic);
        // 去掉补值
        $id = ltrim($card, $dedic[0]);
        // 反转字符
        $id = strrev($id);
        // 初始化值
        $v = 0;
        // 遍历数据
        for ($i = 0, $j = strlen($id); $i < $j; $i++) {
            $v = bcadd(bcmul($dedic[$id {$i}], bcpow(self::$dnum, $i, 0), 0), $v, 0);
        }

        return $v;
    }

    /**
     * 数字2卡号
     * @param int $int 数字
     * @param int $format 格式长度
     * @return string
     */
    public static function generateCardByNum($int, $format = 6)
    {
        // 数字转换
        $card = self::encodeID($int, $format);
        // 校验生成
        $card_vc = substr(md5(self::$pre . $card), 0, self::$vc_len);
        // 数据拼接
        return strtolower(self::$pre . $card . $card_vc);
    }

    /**
     * 卡号2数字
     * @param string $card 卡号
     * @return int
     */
    public static function generateNumByCard($card)
    {
        // 前缀长度
        $pre_len = strlen(self::$pre);
        // 卡号长度
        $card_len = strlen($card) - $pre_len - self::$vc_len;
        // 数据截取
        $card = substr($card, $pre_len, $card_len);

        return self::decodeID(strtoupper($card));
    }

    //带时效
    public static function encode_pass($tex,$key,$type="encode",$expiry=0){
        $chrArr=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                      'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                      '0','1','2','3','4','5','6','7','8','9');
        if($type=="decode"){
            if(strlen($tex)<14)return false;
            $verity_str=substr($tex, 0,8);
            $tex=substr($tex, 8);
            if($verity_str!=substr(md5($tex),0,8)){
                //完整性验证失败
                return false;
            }    
        }
        $key_b=$type=="decode"?substr($tex,0,6):$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62].$chrArr[rand()%62];
        
        $rand_key=$key_b.$key;    
        //设置时间选项
        $modnum=0;$modCount=0;$modCountStr="";
        if($expiry>0){
            if($type=="decode"){
                $modCountStr=substr($tex,6,1);
                $modCount=$modCountStr=="a"?10:floor($modCountStr);
                $modnum=substr($tex,7,$modCount);
                $rand_key=$rand_key.(floor((time()-$modnum)/$expiry));
            }else{
                $modnum=time()%$expiry;
                $modCount=strlen($modnum);
                $modCountStr=$modCount==10?"a":$modCount;
                
                $rand_key=$rand_key.(floor(time()/$expiry));            
            }
            $tex=$type=="decode"?base64_decode(substr($tex, (7+$modCount))):"xugui".$tex;
        }else{
            $tex=$type=="decode"?base64_decode(substr($tex, 6)):"xugui".$tex;
        }
        $rand_key=md5($rand_key);


        $texlen=strlen($tex);
        $reslutstr="";
        for($i=0;$i<$texlen;$i++){
            $reslutstr.=$tex{$i}^$rand_key{$i%32};
        }
        if($type!="decode"){
            $reslutstr=trim(base64_encode($reslutstr),"==");
            $reslutstr=$modCount?$modCountStr.$modnum.$reslutstr:$reslutstr;
            $reslutstr=$key_b.$reslutstr;
            $reslutstr=substr(md5($reslutstr), 0,8).$reslutstr;
        }else{
            if(substr($reslutstr,0, 5)!="xugui"){
                return false;
            }
            $reslutstr=substr($reslutstr, 5);
        }
        return $reslutstr;
    }

}
