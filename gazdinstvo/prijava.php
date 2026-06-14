<?php
require_once __DIR__ . '/klase/Autentifikacija.php';

$auth = new Autentifikacija();
if ($auth->jePrijavljen()) {
    header('Location: index.php');
    exit;
}

$greska = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $korisnik = trim(isset($_POST['korisnik']) ? $_POST['korisnik'] : '');
    $lozinka  = isset($_POST['lozinka']) ? $_POST['lozinka'] : '';

    if ($auth->prijavi($korisnik, $lozinka)) {
        header('Location: index.php');
        exit;
    }
    $greska = 'Pogrešno korisničko ime/email ili lozinka.';
}
?>
<!doctype html>
<html lang="sr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prijava - Moje Gazdinstvo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/stil.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
      <div class="text-center mb-4">
        <h1 class="h3 login-naslov">🚜 Moje Gazdinstvo</h1>
        <p class="text-muted">Sistem za upravljanje malim poljoprivrednim gazdinstvima</p>
      </div>
      <div class="card shadow-sm kartica-login">
        <div class="card-body p-4">
          <h2 class="h5 card-title mb-3">Prijava</h2>

          <?php if (isset($_GET['registrovan'])): ?>
            <div class="alert alert-success">Registracija je uspešna! Sada se možete prijaviti.</div>
          <?php endif; ?>

          <?php if ($greska): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($greska); ?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label" for="korisnik">Korisničko ime ili email</label>
              <input type="text" id="korisnik" name="korisnik" class="form-control"
                     value="<?php echo htmlspecialchars(isset($_POST['korisnik']) ? $_POST['korisnik'] : ''); ?>" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label" for="lozinka">Lozinka</label>
              <input type="password" id="lozinka" name="lozinka" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Prijavi se</button>
          </form>
        </div>
      </div>
      <p class="text-center mt-3">Nemate nalog? <a href="registracija.php">Registrujte se</a></p>
    </div>
  </div>
</div>
</body>
</html>
