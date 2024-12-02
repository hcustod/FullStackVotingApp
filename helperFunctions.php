<?php


// Returns the user to the login screen
function logout() {
    session_unset();
    session_destroy();
    header("location: ./login.php");
    exit;
}

?>

