<?php
session_start();

function is_logged(): bool {
    return !empty($_SESSION['email']);
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}