<?PHP
/* Requires binary class! */
class pack {
    const DEBUG = 0;
    
    /* Pack the inventory of a dat file */
    function dat($unpacked) {
        $length = strlen($unpacked);
        if(self::DEBUG) printf("Length to pack: %d\n", $length);
        
        $dat = false;

        /* looping through the elements */
        for($i = 0; $i < $length; $i += 0x224) {

            if(self::DEBUG) printf("Packing item %d\n", $i / 0x224 + 1);
            $item = pack::item($unpacked, $i);
            $dat .= pack('i', strlen($item) + 4);
            $dat .= $item;
        }
        
        if(self::DEBUG) printf("Length after packing: %d\n", strlen($dat));
        
        return $dat;
    }
    
    /* Pack a specific item */
    public static function item($content, $offset) {
        $to_pack = 0;
        
        $item = false;
        
        for($i = 0; $i < 0x224; $i++) {
            $e = binary::byte($content, $offset + $i, false);
            if(self::DEBUG) printf("Read: %'02X\n", $e);
            
            if($e == 0x0) {
                $to_pack++;
            } else {
                $maybe_once = 0;
                $followed_by = 0;
                while($e || $maybe_once) {
                    $followed_by++;
                    if(self::DEBUG) printf("Followed by: %d\n", $followed_by);
                    
                    if($i + $followed_by >= 0x224) { 
                        $followed_by -= 1;
                        if(self::DEBUG) printf("Followed by: %d\n", $followed_by);
                        break; 
                    }
                    
                    $e = binary::byte($content, $offset + $i + $followed_by, false);
                    if(self::DEBUG) printf("Read: %'02X\n", $e);
                    
                    if(!$e) {
                        if($maybe_once) {
                            if(self::DEBUG) printf("It's more than once\n");
                            $followed_by -= 1;
                            if(self::DEBUG) printf("Followed by: %d\n", $followed_by);
                            break;
                        }
                        
                        if(self::DEBUG) printf("Maybe it's once\n");
                        $maybe_once = 1;
                    } else {
                        if(self::DEBUG && $maybe_once) printf("It was once\n");
                        $maybe_once = 0;
                    }
                }
                
                if(self::DEBUG) printf("Packed %d, Followed by: %d\n", $to_pack, $followed_by);
                
                while($to_pack > 0x7F) {
                    $item .= pack('c', 0xFF);
                    $to_pack -= 0x7F;
                }
                
                if($to_pack) {
                    $item .= pack('c', 0x80 | $to_pack);
                }
                
                $item .= pack('c', $followed_by);
                $item .= substr($content, $offset + $i, $followed_by);
                $i += $followed_by - 1;
                
                $to_pack = 0;
            }
        }
        
        while($to_pack > 0x7F) {
            $item .= pack('c', 0xFF);
            $to_pack -= 0x7F;
        }
        
        if($to_pack) {
            $item .= pack('c', 0x80 | $to_pack);
        }
        
        return $item;
    }
}
?>