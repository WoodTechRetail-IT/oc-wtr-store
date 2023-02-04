<?php

$option = $_GET['option'];

if ( $option == 1 ) {
    $data = [ 'a', 'b', 'c' ];
    // will encode to JSON array: ["a","b","c"]
    // accessed as example in JavaScript like: result[1] (returns "b")
} else {
    $data = [ 'name' => 'God', 'age' => -1 ];
    // will encode to JSON object: {"name":"God","age":-1}  
    // accessed as example in JavaScript like: result.name or result['name'] (returns "God")
}
echo json_encode( $data );

header('Content-type: application/json');
$db = new mysqli('149.154.67.20:3311', 'dveri-i-ru', 'zS5fD8zN1j', 'dveri-i-ru');
if(mysqli_connect_errno()){
    echo mysqli_connect_error();
}
$getresult = $db->query("SELECT * FROM oc_section");
if($getresult){
$result = $getresult->fetch_array();
$getresult->close();
} else echo($db->error);
$db->close();
echo json_encode( $result );

?>