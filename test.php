<?php
// test.php - à placer à la racine du site
echo "Test direct PHP fonctionne<br>";

// Vérifier l'environnement
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";