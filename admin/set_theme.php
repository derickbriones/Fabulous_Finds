<?php
session_start();

if (isset($_POST['theme']) && in_array($_POST['theme'], ['light', 'dark'])) {
    $_SESSION['theme'] = $_POST['theme'];
    echo 'Theme saved';
} else {
    http_response_code(400);
    echo 'Invalid theme';
}
