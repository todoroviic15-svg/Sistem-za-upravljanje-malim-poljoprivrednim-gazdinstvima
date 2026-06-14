<?php
require_once __DIR__ . '/klase/Autentifikacija.php';

$auth = new Autentifikacija();
$auth->odjavi();

header('Location: prijava.php');
exit;
