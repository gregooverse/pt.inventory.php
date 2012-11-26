<?PHP
/* Requires binary class! */
class item {
    public $page;

    private $index;
    public $x;
    public $y;
    private $head;
    private $version;
    private $time;
    private $checksum;
    public $name;
    private $weight;
    private $price;
    private $integrity;
    private $resistance;
    private $damage;
    private $range;
    private $attack_speed;
    private $attack_rating;
    private $critical;
    private $absorb;
    private $defence;
    private $block;
    private $speed;
    private $potions;
    private $magic_mastery;
    private $mana_regen;
    private $life_regen;
    private $stamina_regen;
    private $increase_life;
    private $increase_mana;
    private $increase_stamina;
    private $level;
    private $strength;
    private $spirit;
    private $talent;
    private $dexterity;
    private $health;
    private $unique;
    
    public $unpacked;
    
    public function __construct($unpacked) {
        $this->page = 0;
        
        $length = strlen($unpacked);
        
        if($length != 0x224) {
            return;
        }
        
        $this->index = binary::word($unpacked, 0x0);
        $this->x = binary::dword($unpacked, 0x4);
        $this->y = binary::dword($unpacked, 0x8);
        $this->head = binary::dword($unpacked, 0x14);
        $this->version = binary::dword($unpacked, 0x18);
        $this->time = binary::dword($unpacked, 0x1C, false);
        $this->checksum = binary::dword($unpacked, 0x20);
        $this->name = binary::string($unpacked, 0x2C, 0x20);
        $this->weight = binary::dword($unpacked, 0x4C);
        $this->price = binary::dword($unpacked, 0x50);
        $this->integrity = array(
            binary::word($unpacked, 0x24),
            binary::word($unpacked, 0x26)
        );
        $this->resistance = array(
            binary::word($unpacked, 0x5C),
            binary::word($unpacked, 0x5E),
            binary::word($unpacked, 0x60),
            binary::word($unpacked, 0x62),
            binary::word($unpacked, 0x64),
            binary::word($unpacked, 0x66),
            binary::word($unpacked, 0x68),
            binary::word($unpacked, 0x6A)
        );
        $this->damage = array(
            binary::word($unpacked, 0x74),
            binary::word($unpacked, 0x76),
            binary::word($unpacked, 0x156) /* spec */
        );
        $this->range = array(
            binary::dword($unpacked, 0x78),
            binary::dword($unpacked, 0x120) /* spec */
        );
        $this->attack_speed = array(
            binary::dword($unpacked, 0x7C),
            binary::dword($unpacked, 0x118) /* spec */
        );
        $this->attack_rating = array(
            binary::dword($unpacked, 0x80),
            binary::dword($unpacked, 0x150) /* spec */
        );
        $this->critical = array(
            binary::dword($unpacked, 0x84),
            binary::dword($unpacked, 0x11C) /* spec */
        );
        $this->absorb = array(
            binary::float($unpacked, 0x88),
            binary::float($unpacked, 0x108) /* spec */
        );
        $this->defence = array(
            binary::dword($unpacked, 0x8C),
            binary::dword($unpacked, 0x10C) /* spec */
        );
        $this->block = array(
            binary::float($unpacked, 0x90),
            binary::float($unpacked, 0x114) /* spec */
        );
        $this->speed = array(
            binary::float($unpacked, 0x94),
            binary::float($unpacked, 0x110) /* spec */
        );
        $this->potions = binary::dword($unpacked, 0x98);
        $this->magic_mastery = array(
            binary::float($unpacked, 0x9C),
            binary::float($unpacked, 0x124) /* spec */
        );
        $this->mana_regen = array(
            binary::float($unpacked, 0xA0),
            binary::float($unpacked, 0x158) /* spec */
        );
        $this->life_regen = array(
            binary::float($unpacked, 0xA4),
            binary::float($unpacked, 0x15C) /* spec */
        );
        $this->stamina_regen = array(
            binary::float($unpacked, 0xA8),
            binary::float($unpacked, 0x160) /* spec */
        );
        $this->increase_life = binary::float($unpacked, 0xAC);
        $this->increase_mana = binary::float($unpacked, 0xB0);
        $this->increase_stamina = binary::float($unpacked, 0xB0);
        $this->level = binary::dword($unpacked, 0xB8);
        $this->strength = binary::dword($unpacked, 0xBC);
        $this->spirit = binary::dword($unpacked, 0xC0);
        $this->talent = binary::dword($unpacked, 0xC4);
        $this->dexterity = binary::dword($unpacked, 0xC8);
        $this->health = binary::dword($unpacked, 0xCC);
        $this->unique = binary::dword($unpacked, 0xF0);
        
        
        $this->unpacked = $unpacked;
    }
    
    public function ink($debug = true) {
        echo $this->string($debug);
    }
    
    public function string($debug = true) {
        $string = false;
        
        if($debug && $this->index) { $string .= sprintf("Index: %d\n", $this->index); }
        if($debug && $this->x) { $string .= sprintf("X: %d\n", $this->x); }
        if($debug && $this->y) { $string .= sprintf("Y: %d\n", $this->y); }
        if($debug && $this->head) { $string .= sprintf("Head: %d\n", $this->head); }
        if($debug && $this->version) { $string .= sprintf("Version: %d\n", $this->version); }
        if($debug && $this->time) { $string .= sprintf("Time: %d\n", $this->time); }
        if($debug && $this->checksum) { $string .= sprintf("Checksum: %d\n", $this->checksum); }
        
        $string .= sprintf("Name: %s\n", $this->name);
        
        if($this->weight) { $string .= sprintf("Weight: %d\n", $this->weight); }
        if($this->price) { $string .= sprintf("Price: %d\n", $this->price); }
        if($this->integrity[0] && $this->integrity[1]) { $string .= sprintf("Integrity: %s\n", join('/', $this->integrity)); }
        if($this->resistance[0] || $this->resistance[1] || $this->resistance[2] || $this->resistance[3] || $this->resistance[4] || $this->resistance[5] || $this->resistance[6] || $this->resistance[7] ) { $string .= sprintf("Resistance: [%s]\n", join(', ', $this->resistance)); }
        if($this->damage[0] || $this->damage[1] || $this->damage[2]) { $string .= sprintf("Damage: %d - %d +  lv/%d\n", $this->damage[0], $this->damage[1], $this->damage[2]); }
        if($this->range[0] || $this->range[1]) { $string .= sprintf("Range: %s\n", join(' + ', $this->range)); }
        if($this->attack_speed[0] || $this->attack_speed[1]) { $string .= sprintf("Attack Speed: %s\n", join(' + ', $this->attack_speed)); }
        if($this->attack_rating[0] || $this->attack_rating[1]) { $string .= sprintf("Attack Rating: %s + lv/%d\n", $this->attack_rating[0], $this->attack_rating[1]); }
        if($this->critical[0] || $this->critical[1]) { $string .= sprintf("Critical: %s\n", join(' + ', $this->critical)); }
        if($this->absorb[0] || $this->absorb[1]) { $string .= sprintf("Absorb: %s\n", join(' + ', $this->absorb)); }
        if($this->defence[0] || $this->defence[1]) { $string .= sprintf("Defence: %s\n", join(' + ', $this->defence)); }
        if($this->block[0] || $this->block[1]) { $string .= sprintf("Block: %s\n", join(' + ', $this->block)); }
        if($this->speed[0] || $this->speed[1]) { $string .= sprintf("Speed: %s\n", join(' + ', $this->speed)); }
        if($this->potions) { $string .= sprintf("Potions: %d\n", $this->potions); }
        if($this->magic_mastery[0] || $this->magic_mastery[1]) { $string .= sprintf("Magic Mastery: %s\n", join(' + ', $this->magic_mastery)); }
        if($this->mana_regen[0] || $this->mana_regen[1]) { $string .= sprintf("Mana Regen: %s\n", join(' + ', $this->mana_regen)); }
        if($this->life_regen[0] || $this->life_regen[1]) { $string .= sprintf("Life Regen: %s\n", join(' + ', $this->life_regen)); }
        if($this->stamina_regen[0] || $this->stamina_regen[1]) { $string .= sprintf("Stamina Regen: %s\n", join(' + ', $this->stamina_regen)); }
        if($this->increase_life) { $string .= sprintf("Increase Life: %d\n", $this->increase_life); }
        if($this->increase_mana) { $string .= sprintf("Increase Mana: %d\n", $this->increase_mana); }
        if($this->increase_stamina) { $string .= sprintf("Increase Stamina: %d\n", $this->increase_stamina); }
        if($this->level) { $string .= sprintf("Level: %d\n", $this->level); }
        if($this->strength) { $string .= sprintf("Strength: %d\n", $this->strength); }
        if($this->spirit) { $string .= sprintf("Spirit: %d\n", $this->spirit); }
        if($this->talent) { $string .= sprintf("Talent: %d\n", $this->talent); }
        if($this->dexterity) { $string .= sprintf("Dexterity: %d\n", $this->dexterity); }
        if($this->health) { $string .= sprintf("Health: %d\n", $this->health); }
        if($this->unique) { $string .= sprintf("Unique Item: %d\n", $this->unique); }
        
        return $string;
    }
}

