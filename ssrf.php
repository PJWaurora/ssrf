<?php
if (isset($_REQUEST['leak'])) {
    error_log("LEAK: " . $_REQUEST['leak']);
    echo "LEAK: " . htmlspecialchars($_REQUEST['leak']);
} else {
    echo "No leak parameter received.";
}
?>
