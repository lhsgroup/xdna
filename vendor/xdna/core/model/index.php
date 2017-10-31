<?php
include './xdna.pkg.php';
?><!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        $collection = new xdna_collection();
		$collection[0] = new xdna(1);
		$collection[1] = new xdna(2);
		$collection[2] = new xdna(3);
		$collection[3] = new xdna(4);
        
        $collection->each(function($a,$b){
            echo $b->fava."<br>";
        });
		var_dump(xdna_collection::$cache);
        /*
        $tabella = new cxdna("xdna_elements");
        $tabella->name='Sono mario';
        $tabella->save();
        $tabella->uri = "Stoca";
        $tabella->save();
        $tabella->delete();
         * 
         */
        //$item = new xdna('Secondo livello');
        //xdna_db::istance('xdna_elements');
        //xdna_element::create("prova","prova", "tabella");
        /*
        $item->find('{"nome":"mario","cognome":"gino"}')->each(function($a,$b) {
            echo 'Trovato '.$a." : ".$b.'<br/>';
        });
         * 
         */
        echo 'done';
        ?>
    </body>
</html>
