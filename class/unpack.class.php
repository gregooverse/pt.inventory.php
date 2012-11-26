<?PHP
/* Requires binary class! */
class unpack {
    const DEBUG = 0;
    
    /* Unpack the inventory of a dat file */
    function dat($content) {
        /* number of items */
        $offset = 0x6E4;
        
        $total = binary::dword($content, $offset);
        
        /* beginning of the item table */
        $offset = 0x6F0;
        
        $dat = false;

        /* looping through the elements */
        for($i = 0; $i < $total; $i++) {
            if(self::DEBUG) printf("Unpacking item %d/%d\n", $i + 1, $total);
            $dat .= unpack::item($content, $offset, $i);
        }
        
        return $dat;
    }
    
    /* Unpack a specific item */
    public static function item($content, &$offset, $index) {
        /* size of the item */
        $size = binary::dword($content, $offset);
        if(self::DEBUG) printf("Size of the item: %d\n", $size);
        
        /* starting offset */
        $start = $offset;
        if(self::DEBUG) printf("Offset of the item: %d\n", $start);
        
        $item = false;
        
        /* shifting to the first element */
        $offset += 0x4;
        
        while($offset - $start < $size) {
            if(self::DEBUG) printf("Unpacking a new element of the item\n");
            $item .= unpack::element($content, $offset);
        }
        
        /* Dumping item */
        if(self::DEBUG) {
            printf("Item: %s\n", binary::string($item, 0x2C, 0x20));
            printf("Packed item hexadecimal dump (size: %d):\n", $size);
            
            for($i = 0; $i < $size; $i++) {
                if(!($i % 0xF)) {
                    printf("\n");
                }
                
                printf("%'02X ", binary::byte($content, $start + 0x4 + $i, false));
            }
            printf("\n");
            
            $length = strlen($item);
            
            printf("Unpacked item hexadecimal dump (size: %d):\n", $length);
            
            for($i = 0; $i < $length; $i++) {
                if(!($i % 0xF)) {
                    printf("\n");
                }
                
                printf("%'02X ", binary::byte($item, $i, false));
            }
            printf("\n");
        }
        
        return $item;
    }

    /* Unpack an element of the item */
    public static function element($content, &$offset) {
        $element = false;
        
        $e = binary::byte($content, $offset, false);
        if(self::DEBUG) printf("Element's first byte: %'02X (logical AND to 0x80: %X)\n", $e, $e & 0x80);
        
        if(($e & 0x80) != 0) {
            if(self::DEBUG) printf("This item is padding: expanding by %'02X bytes\n", $e & 0x7F);
        
            $offset++;
        
            for($i = 0; $i < ($e & 0x7F); $i++) {
                $element .= pack('c', 0);
            }
        } else {
            if(self::DEBUG) printf("This item is actual data: fetching %d bytes\n", $e & 0x7F);
        
            $offset++;
            
            for($i = 0; $i < ($e & 0x7F); $i++, $offset++) {
                $element .= substr($content, $offset, 1);
            }
        }
        
        return $element;
    }
}
?>