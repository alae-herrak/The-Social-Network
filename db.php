<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=socialnetwork', 'root', '');
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
