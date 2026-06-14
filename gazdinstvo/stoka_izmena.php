<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/StokaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo  = new StokaRepozitorijum();
$id    = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$stoka = $repo->read($id);

if (!$stoka) {
    header('Location: stoka.php');
    exit;
}

$greske = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } else {
        $vrsta    = trim(isset($_POST['vrsta']) ? $_POST['vrsta'] : '');
        $rasa     = trim(isset($_POST['rasa']) ? $_POST['rasa'] : '');
        $brojGrla = (int) (isset($_POST['broj_grla']) ? $_POST['broj_grla'] : 0);
        $nabavka  = trim(isset($_POST['datum_nabavke']) ? $_POST['datum_nabavke'] : '');

        if (!array_key_exists($vrsta, Stoka::VRSTE)) {
            $greske[] = 'Morate izabrati vrstu stoke.';
        }
        if ($brojGrla < 0) {
            $greske[] = 'Broj grla ne može biti negativan.';
        }

        if (empty($greske)) {
            $napomena = trim(isset($_POST['napomena']) ? $_POST['napomena'] : '');
            $repo->update($id, [
                'vrsta'         => $vrsta,
                'rasa'          => $rasa !== '' ? $rasa : null,
                'broj_grla'     => $brojGrla,
                'datum_nabavke' => $nabavka !== '' ? $nabavka : null,
                'napomena'      => $napomena !== '' ? $napomena : null,
            ]);
            header('Location: stoka.php');
            exit;
        }
    }
}

$vVrsta    = isset($_POST['vrsta']) ? $_POST['vrsta'] : $stoka->vrsta;
$vRasa     = isset($_POST['rasa']) ? $_POST['rasa'] : $stoka->rasa;
$vBrojGrla = isset($_POST['broj_grla']) ? $_POST['broj_grla'] : $stoka->broj_grla;
$vNabavka  = isset($_POST['datum_nabavke']) ? $_POST['datum_nabavke'] : $stoka->datum_nabavke;
$vNapomena = isset($_POST['napomena']) ? $_POST['napomena'] : $stoka->napomena;

$naslovStrane = 'Izmena stoke - Moje Gazdinstvo';
$aktivna      = 'stoka';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🐄 Izmena evidencije stoke</h1>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Izmena: <?php echo e(isset(Stoka::VRSTE[$stoka->vrsta]) ? Stoka::VRSTE[$stoka->vrsta] : $stoka->vrsta); ?></div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="vrsta">Vrsta <span class="text-danger">*</span></label>
            <select id="vrsta" name="vrsta" class="form-select" required>
              <?php foreach (Stoka::VRSTE as $kod => $naz): ?>
                <option value="<?php echo e($kod); ?>" <?php echo $vVrsta === $kod ? 'selected' : ''; ?>><?php echo e($naz); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="rasa">Rasa</label>
            <input type="text" id="rasa" name="rasa" class="form-control" value="<?php echo e($vRasa); ?>">
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="broj_grla">Broj grla <span class="text-danger">*</span></label>
              <input type="number" min="0" id="broj_grla" name="broj_grla" class="form-control" value="<?php echo e($vBrojGrla); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_nabavke">Datum nabavke</label>
              <input type="date" id="datum_nabavke" name="datum_nabavke" class="form-control" value="<?php echo e($vNabavka); ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" class="form-control" rows="2"><?php echo e($vNapomena); ?></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">Sačuvaj izmene</button>
            <a href="stoka.php" class="btn btn-outline-secondary">Otkaži</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
