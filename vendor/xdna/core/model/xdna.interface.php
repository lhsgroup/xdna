<?php
/**
 *
 * @author luca
 */
interface Ixdna {
    //put your code here
    public function set($param,$value,$lang=NULL);
    public function get($param,$lang=NULL);
    public function commit();
    public function delete();
}

?>
