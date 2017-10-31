<?php
while( true ) {

// Print the menu on console
printMenu();

// Read user choice
$choice = trim( fgets(STDIN) );

// Exit application
if( $choice == 5 ) {

break;
}
if($choice == 1) {
    $out = shell_exec ('php -S localhost:80 xdna.php');
    echo $out;
}
}

function printMenu() {

echo "************ Reservation System ******************\n";
echo "1 - Choose Source\n";
echo "2 - Choose Destination\n";
echo "3 - Personal Details\n";
echo "4 - Make Reservation\n";
echo "5 - Quit\n";
echo "************ Reservation System ******************\n";
echo "Enter your choice from 1 to 5 ::";
}