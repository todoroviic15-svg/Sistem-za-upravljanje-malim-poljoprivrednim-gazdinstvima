<?php
require_once __DIR__ . '/klase/Autentifikacija.php';

$auth = new Autentifikacija();
if ($auth->jePrijavljen()) {
    header('Location: index.php');
    exit;
}

$greske = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $korisnickoIme = trim(isset($_POST['korisnicko_ime']) ? $_POST['korisnicko_ime'] : '');
    $email         = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $lozinka       = isset($_POST['lozinka']) ? $_POST['lozinka'] : '';
    $potvrda       = isset($_POST['potvrda']) ? $_POST['potvrda'] : '';

    $greske = $auth->registruj($korisnickoIme, $email, $lozinka, $potvrda);

    if (empty($greske)) {
        header('Location: prijava.php?registrovan=1');
        exit;
    }
}
?>
<!doctype html>
<html lang="sr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registracija - Moje Gazdinstvo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/stil.css" rel="stylesheet">
</head>
<body>
<div class="container">
  <div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
      <div class="text-center mb-4">
        <h1 class="h3 login-naslov">🚜 Moje Gazdinstvo</h1>
        <p class="text-muted">Napravite nalog za pristup sistemu</p>
      </div>
      <div class="card shadow-sm kartica-login">
        <div class="card-body p-4">
          <h2 class="h5 card-title mb-3">Registracija</h2>

          <?php if ($greske): ?>
            <div class="alert alert-danger mb-3">
              <ul class="mb-0">
                <?php foreach ($greske as $g): ?>
                  <li><?php echo htmlspecialchars($g); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label" for="korisnicko_ime">Korisničko ime</label>
              <input type="text" id="korisnicko_ime" name="korisnicko_ime" class="form-control"
                     value="<?php echo htmlspecialchars(isset($_POST['korisnicko_ime']) ? $_POST['korisnicko_ime'] : ''); ?>" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email adresa</label>
              <input type="email" id="email" name="email" class="form-control"
                     value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="lozinka">Lozinka <small class="text-muted">(najmanje 6 karaktera)</small></label>
              <input type="password" id="lozinka" name="lozinka" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="potvrda">Potvrda lozinke</label>
              <input type="password" id="potvrda" name="potvrda" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Registruj se</button>
          </form>
        </div>
      </div>
      <p class="text-center mt-3">Već imate nalog? <a href="prijava.php">Prijavite se</a></p>
    </div>
  </div>
</div>
</body>
</html>
