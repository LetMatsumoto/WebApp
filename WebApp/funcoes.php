<?php
session_start();

//evita que entrem sem login (protege as páginas restritas)
function is_logged(): bool {
    return !empty($_SESSION['email']);
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}