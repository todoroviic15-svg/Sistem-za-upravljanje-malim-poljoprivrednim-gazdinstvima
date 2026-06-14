<?php
require_once __DIR__ . '/klase/Autentifikacija.php';

// Stranice po pravilu same prave $auth pre obrade formi;
// ovo je rezervna provera za one koje to ne rade.
$auth = isset($auth) ? $auth : new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}

$sesija   = isset($sesija) ? $sesija : $auth->sesija();
$csrf     = $sesija->csrfToken();
$korisnik = $auth->trenutniKorisnik();

$naslovStrane = isset($naslovStrane) ? $naslovStrane : 'Gazdinstvo - Sistem za upravljanje gazdinstvom';
$aktivna      = isset($aktivna) ? $aktivna : '';

/**
 * Skraćeno HTML eskejpovanje.
 */
function e($tekst): string
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8');
}

/**
 * CSS klasa za link u navigaciji (označava aktivnu stranicu).
 */
function nav(string $strana, string $aktivna): string
{
    return $strana === $aktivna ? 'nav-link active' : 'nav-link';
}

/**
 * Formatira datum iz baze u d.m.Y. format.
 */
function datum($vrednost): string
{
    return $vrednost ? date('d.m.Y.', strtotime($vrednost)) : '-';
}

/**
 * Formatira novčani iznos u dinarima.
 */
function rsd($iznos): string
{
    return number_format((float) $iznos, 2, ',', '.') . ' RSD';
}

/**
 * Formatira broj sa decimalama (za površinu, količine...).
 */
function broj($vrednost, int $decimale = 2): string
{
    return number_format((float) $vrednost, $decimale, ',', '.');
}
?>
<!doctype html>
<html lang="sr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo e($naslovStrane); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/stil.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark navbar-gazdinstvo">
  <div class="container">
    <a class="navbar-brand" href="index.php">🚜 Moje Gazdinstvo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#glavnaNavigacija"
            aria-controls="glavnaNavigacija" aria-expanded="false" aria-label="Prikaži navigaciju">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="glavnaNavigacija">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="<?php echo nav('pocetna', $aktivna); ?>" href="index.php">🏠 Početna</a></li>
        <li class="nav-item"><a class="<?php echo nav('zemljiste', $aktivna); ?>" href="zemljiste.php">🌍 Zemljište</a></li>
        <li class="nav-item"><a class="<?php echo nav('usevi', $aktivna); ?>" href="usevi.php">🌱 Usevi</a></li>
        <li class="nav-item"><a class="<?php echo nav('stoka', $aktivna); ?>" href="stoka.php">🐄 Stoka</a></li>
        <li class="nav-item"><a class="<?php echo nav('zalihe', $aktivna); ?>" href="zalihe.php">📦 Zalihe</a></li>
        <li class="nav-item"><a class="<?php echo nav('prodaja', $aktivna); ?>" href="prodaja.php">💰 Prodaja</a></li>
        <li class="nav-item"><a class="<?php echo nav('izvestaji', $aktivna); ?>" href="izvestaji.php">📊 Izveštaji</a></li>
      </ul>
      <span class="navbar-text me-3 text-white">👤 <?php echo e($korisnik->korisnicko_ime); ?></span>
      <a href="odjava.php" class="btn btn-outline-light btn-sm">Odjavi se</a>
    </div>
  </div>
</nav>
<main class="container my-4 flex-grow-1">
