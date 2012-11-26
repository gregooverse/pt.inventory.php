<?PHP
require_once 'class/binary.class.php';
require_once 'class/unpack.class.php';
require_once 'class/pack.class.php';
require_once 'class/inventory.class.php';
require_once 'class/item.class.php';
?>
<pre><?PHP 
    $inventory = new inventory('data/Gregoo.dat');
    
    if(isset($_POST['delete']) && isset($_POST['id'])) {
        $inventory->delete($_POST['id']);
        $inventory->write('data/Gregoo.dat');
        
        header('Location: index.php');
    } else {
        $inventory->form();
    }
?></pre>