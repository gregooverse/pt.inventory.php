<?PHP
/* Requires unpack class! */
/* Requires pack class! */
/* Requires binary class! */
/* Requires item class! */
class inventory {
    private $items = array();
    private $packed;
    private $unpacked;
    private $table;
    
    private $total;
    private $second;
    private $prime;
    
    private $factor = 1.5;
    
    public function __construct($file, $unpack = false) {
        /* reading file content */
        $stream = fopen($file, 'r') or exit("Invalid file");
        $this->packed = fread($stream, filesize($file));
        fclose($stream);
        
        $this->total = binary::dword($this->packed, 0x6E4);
        $this->second = binary::dword($this->packed, 0x6E8);
        $this->prime = $this->total - $this->second;
    }
    
    public function unpack() {
        $this->unpacked = unpack::dat($this->packed);
    }
    
    public function pack() {
        $packed = pack::dat($this->unpacked);
        $length = strlen($packed);
        
        binary::replace($this->packed, pack('i', $this->total), 0x6E4);
        binary::replace($this->packed, pack('i', $this->second), 0x6E8);
        binary::replace($this->packed, pack('i', $length), 0x6EC);
        
        binary::replace($this->packed, $packed, 0x6F0, $length);
    }
    
    public function write($target, $unpack = false) {
        if($unpack) {
            $content = $this->unpacked;
        } else {
            $this->pack();
            $content = $this->packed;
        }
    
        $stream = fopen($target, 'w+') or exit("Invalid file");
        fwrite($stream, $content, strlen($content));
        fclose($stream);
    }
    
    public function read() {
        if(!$this->unpacked) {
            $this->unpack();
        }
        
        /* looping through the elements */
        for($i = 0; $i < strlen($this->unpacked); $i += 0x224) {
            if(!$this->add(substr($this->unpacked, $i, 0x224))) {
                break;
            }
        }
    }
    
    public function add($unpacked) {
        $item = new item($unpacked);
        
        if(!$item) {
            return false;
        }
        
        $item->page = (count($this->items) >= $this->prime) ? 2 : 1;
        
        $this->items[] = $item;
        
        return true;
    }
    
    public function delete($id) {
        if(!count($this->items)) {
            $this->read();
        }
        
        if($id < 0 || $id >= count($this->items)) {
            return;
        }
        
        if($id > $this->prime) {
            $this->second--;
        }
        
        $this->total--;
        
        array_splice($this->items, $id, 1);
        
        $this->unpacked = false;
        
        for($i = 0; $i < count($this->items); $i++) {
            $this->unpacked .= $this->items[$i]->unpacked;
        }
    }
    
    public function ink() {
        if(!count($this->items)) {
            $this->read();
        }
        
        for($i = 0; $i < count($this->items); $i++) {
            $this->items[$i]->ink();
            printf("%s\n", str_repeat('*', 60));
        }
    }
    
    public function form() {
        if(!count($this->items)) {
            $this->read();
        }
        
        $xy = array(
            'first-row' => 10,
            'second-row' => 210,
            'page-left' => 10,
            'weapon-left' => 585,
            'equipped-left' => 305,
            'quest-left' => 820,
            'potions-top' => 140,
            'potions-left' => 495
        );
        
        $factorize = create_function('$value','return $value * ' . $this->factor . ';');
        $xy = array_map($factorize, $xy);
            
        print <<<EOF
<div style=" position:absolute; top:${xy['first-row']}px; left:${xy['page-left']}px; background-color:#888; color:#FFF">First page</div>
<div style=" position:absolute; top:${xy['first-row']}px; left:${xy['equipped-left']}px; background-color:#888; color:#FFF">Equipped</div>
<div style=" position:absolute; top:${xy['first-row']}px; left:${xy['weapon-left']}px; background-color:#888; color:#FFF">First weapon</div>
<div style=" position:absolute; top:${xy['potions-top']}px; left:${xy['potions-left']}px; background-color:#888; color:#FFF">Potions</div>
<div style=" position:absolute; top:${xy['second-row']}px; left:${xy['page-left']}px; background-color:#888; color:#FFF">Second page</div>
<div style=" position:absolute; top:${xy['second-row']}px; left:${xy['weapon-left']}px; background-color:#888; color:#FFF">Second weapon</div>
<div style=" position:absolute; top:${xy['second-row']}px; left:${xy['quest-left']}px; background-color:#888; color:#FFF">Quest</div>
EOF;
        
        $width = 22 * $this->factor;
        $height = 22 * $this->factor;
        $page = $_SERVER['SCRIPT_NAME'];
        
        for($i = 0; $i < count($this->items); $i++) {
            $y = ($this->items[$i]->y - 406 + (($this->items[$i]->page == 2) * 200)) * $this->factor;
            $x = ($this->items[$i]->x - (($this->items[$i]->x > 10000) * 9200)) * $this->factor;
            
            $title = $this->items[$i]->string(false);
            $content = $this->items[$i]->name;
            
            print <<<EOF
<form method="post" action="${page}" onclick="return confirm('Are you sure you want to delete ${content} ?');" style="
        position:absolute;
        top:0px;
        left:0px;
        margin:0;
        padding:0;
        width:0;
        height:0;
    ">
    <input type="hidden" name="id" value="${i}" />
    <input type="submit" name="delete" value="${content}" title="${title}" style="
        position:absolute;
        top:{$y}px;
        left:{$x}px;
        display:block;
        width:${width}px;
        height:{$height}px;
        background:#FFF;
        border:#000 1px solid;
        margin:0;
        padding:0;
        line-height:22px;
    " />
</form>
EOF;
        }
    }
}