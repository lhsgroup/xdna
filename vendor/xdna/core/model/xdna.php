<?php
include("xDNA/xdna.pkg.php");
$a = array();
$a[] = new xdna(1);
$a[] = new xdna(2);
$a[] = new xdna(3);
$a[] = new xdna(4);
$a[] = new xdna(5);
$co = new xdna_collection($a);
$co->each(function($m,$l) {
	echo $l->stocazzo;
});