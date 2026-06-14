<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ZalihaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo   = new ZalihaRepozitorijum();
$id     = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$zaliha = $repo->read($id);

if (!$zaliha) {
    header('Location: zalihe.php');
    exit;
}

$greske = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } else {
        $naziv       = trim(isset($_POST['naziv_proizvoda']) ? $_POST['naziv_proizvoda'] : '');
        $kolicina    = (float) (isset($_POST['kolicina']) ? $_POST['kolicina'] : 0);
        $jedinica    = trim(isset($_POST['jedinica_mere']) ? $_POST['jedinica_mere'] : '');
        $datumUlaza  = trim(isset($_POST['datum_ulaza']) ? $_POST['datum_ulaza'] : '');
        $datumIsteka = trim(isset($_POST['datum_isteka']) ? $_POST['datum_isteka'] : '');

        if ($naziv === '') {
            $greske[] = 'Naziv proizvoda je obavezan.';
        }
        if ($kolicina < 0) {
            $greske[] = 'Količina ne može biti negativna.';
        }
        if (!array_key_exists($jedinica, Zaliha::JEDINICE)) {
            $greske[] = 'Morate izabrati jedinicu mere.';
        }
        if ($datumUlaza === '') {
            $greske[] = 'Datum ulaza je obavezan.';
        }

        if (empty($greske)) {
            $repo->update($id, [
                'naziv_proizvoda' => $naziv,
                'kolicina'        => $kolicina,
                'jedinica_mere'   => $jedinica,
                'datum_ulaza'     => $datumUlaza,
                'datum_isteka'    => $datumIsteka !== '' ? $datumIsteka : null,
            ]);
            header('Location: zalihe.php');
            exit;
        }
    }
}

$vNaziv       = isset($_POST['naziv_proizvoda']) ? $_POST['naziv_proizvoda'] : $zaliha->naziv_proizvoda;
$vKolicina    = isset($_POST['kolicina']) ? $_POST['kolicina'] : $zaliha->kolicina;
$vJedinica    = isset($_POST['jedinica_mere']) ? $_POST['jedinica_mere'] : $zaliha->jedinica_mere;
$vDatumUlaza  = isset($_POST['datum_ulaza']) ? $_POST['datum_ulaza'] : $zaliha->datum_ulaza;
$vDatumIsteka = isset($_POST['datum_isteka']) ? $_POST['datum_isteka'] : $zaliha->datum_isteka;

$naslovStrane = 'Izmena zalihe - Moje Gazdinstvo';
$aktivna      = 'zalihe';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">📦 Izmena zalihe</h1>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Izmena: <?php echo e($zaliha->naziv_proizvoda); ?></div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="naziv_proizvoda">Naziv proizvoda <span class="text-danger">*</span></label>
            <input type="text" id="naziv_proizvoda" name="naziv_proizvoda" class="form-control"
                   value="<?php echo e($vNaziv); ?>" required>
          </div>
          <div class="row">
            <div class="col-7 mb-3">
              <label class="form-label" for="kolicina">Količina <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="kolicina" name="kolicina" class="form-control"
                     value="<?php echo e($vKolicina); ?>" required>
            </div>
            <div class="col-5 mb-3">
              <label class="form-label" for="jedinica_mere">Jed. mere <span class="text-danger">*</span></label>
              <select id="jedinica_mere" name="jedinica_mere" class="form-select" required>
                <?php foreach (Zaliha::JEDINICE as $kod => $naz): ?>
                  <option value="<?php echo e($kod); ?>" <?php echo $vJedinica === $kod ? 'selected' : ''; ?>><?php echo e($naz); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_ulaza">Datum ulaza <span class="text-danger">*</span></label>
              <input type="date" id="datum_ulaza" name="datum_ulaza" class="form-control"
                     value="<?php echo e($vDatumUlaza); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_isteka">Rok trajanja</label>
              <input type="date" id="datum_isteka" name="datum_isteka" class="form-control"
                     value="<?php echo e($vDatumIsteka); ?>">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">Sačuvaj izmene</button>
            <a href="zalihe.php" class="btn btn-outline-secondary">Otkaži</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
