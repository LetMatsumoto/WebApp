<?php
session_start();

function is_logged() {
    return !empty($_SESSION['email']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}
