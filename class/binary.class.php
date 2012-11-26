<?PHP
class binary {
    /* function: 32bit value (no upconversion to float bs) */
    public static function int32($value){
        if($value < -2147483648){
            return -(-($value) & 0xffffffff);
        } elseif($value > 2147483647){
            return ($value & 0xffffffff);
        }
        
        return $value; 
    } 

    /* function: replace data */
    public static function replace(&$content, $data, $start, $length = false){

        $size = ($length) ? $length : strlen($data);
        $end = $start + $size;

        if(strlen($content) < $end) exit("Replacement data is too big");

        if($length)	$data = str_pad($data, $length, pack('h*', 00), STR_PAD_RIGHT);

        $content = substr($content, 0, $start) . substr($data, 0, $size) . substr($content, $end);
    }

    /* function: convert 32 > 16 > 8 */
    public static public static function xbytes($data, $size){
        return hexdec(substr(dechex($data), -($size * 2)));
    }

    /* function: pack data back to binary */
    public static function move(&$content, $data, $start, $pack = 'i'){
        binary::replace($content, pack($pack, $data), $start);
    }

    /* function: switch hexadecimal endian */
    public static function reverse($content){
        $length = strlen($content);

        if($length % 2) { 
            $content = str_pad($content, $length + 1, '0', STR_PAD_LEFT); 
            $length++;
        }
        
        $reverse = false;

        for($i = $length - 2; $i >= 0; $i -= 2)
        {
            $reverse .= substr($content, $i, 2);
        }
        
        return $reverse;
    }

    /* function: extract binary data */
    public static function chunck($content, $start, $length){
        $chunck = bin2hex(substr($content, $start, $length));
        
        return binary::reverse($chunck);
    }

    /* function: extract byte to hexadecimal */
    public static function byte($content, $start, $signed = true){
        $byte = binary::chunck($content, $start, 1);
        $byte = hexdec($byte);
        
        if($signed){
            return (int) ($byte >= pow(2, 7)) ? $byte - pow(2, 8) : $byte;
        }

        return $byte;
    }

    /* function: extract word from binary */
    public static function word($content, $start, $signed = true){
        $word = binary::chunck($content, $start, 2);
        $word = hexdec($word);
        
        if($signed){
            return (int) ($word >= pow(2, 15)) ? $word - pow(2, 16) : $word;
        }

        return $word;
    }

    /* function: extract dword from binary */
    public static function dword($content, $start, $signed = true){
        $dword = binary::chunck($content, $start, 4);
        $dword = hexdec($dword);
        
        if($signed){
            return (int) ($dword >= pow(2, 31)) ? $dword - pow(2, 32) : $dword;
        }

        return $dword;
    }

    /* function: extract float from binary */
    public static function float($content, $start){
        $dword = binary::chunck($content, $start, 4);
        if(!(int) $dword) return 0;
        
        $binary = base_convert($dword, 16, 2);
        $binary = str_pad($binary, 32, '0', STR_PAD_LEFT);

        $sign = substr($binary, 0, 1);

        $exponent = substr($binary, 1, 8);
        $exponent = base_convert($exponent, 2, 10);
        $exponent -= 127;

        $mantissa = substr($binary, 9, 23);

        $decimal = 1;

        for ($i = 0, $j = 0.5; $i < 23; $i++, $j /= 2)
        {
            $decimal += $mantissa[$i] ? $j : 0;
        }

        $decimal *= pow(2, $exponent);
        $decimal *= $sign ? -1 : 1;

        return round($decimal, 3);
    }

    /* function: display hexadecimal data properly */
    public static function hex($content, $size){
        $pattern = "%'0" . $size . "X";
        return sprintf($pattern, $content); 
    }

    /* function: extract string from binary */
    public static function string($content, $start, $length){
        $string = substr($content, $start, $length);

        if(!strlen($string) || $string[0] == "\x00")
        {
            return false;
        }

        $explode = explode("\x00", trim($string, "\x00"));

        return $explode[0];
    }
}
?>