<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/UsevRepozitorijum.php';
require_once __DIR__ . '/klase/ZemljisteRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo          = new UsevRepozitorijum();
$zemljisteRepo = new ZemljisteRepozitorijum();
$id            = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$usev          = $repo->read($id);

if (!$usev) {
    header('Location: usevi.php');
    exit;
}

$greske = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } else {
        $naziv       = trim(isset($_POST['naziv_useva']) ? $_POST['naziv_useva'] : '');
        $kategorija  = trim(isset($_POST['kategorija']) ? $_POST['kategorija'] : '');
        $setva       = trim(isset($_POST['datum_setve']) ? $_POST['datum_setve'] : '');
        $berba       = trim(isset($_POST['ocekivana_berba']) ? $_POST['ocekivana_berba'] : '');
        $zemljisteId = (int) (isset($_POST['zemljiste_id']) ? $_POST['zemljiste_id'] : 0);

        if ($naziv === '') {
            $greske[] = 'Naziv useva je obavezan.';
        }
        if (!array_key_exists($kategorija, Usev::KATEGORIJE)) {
            $greske[] = 'Nepoznata kategorija useva.';
        }
        if ($setva === '') {
            $greske[] = 'Datum setve je obavezan.';
        }
        if ($zemljisteId <= 0) {
            $greske[] = 'Morate izabrati parcelu.';
        }

        if (empty($greske)) {
            $repo->update($id, [
                'naziv_useva'     => $naziv,
                'kategorija'      => $kategorija,
                'datum_setve'     => $setva,
                'ocekivana_berba' => $berba !== '' ? $berba : null,
                'zemljiste_id'    => $zemljisteId,
            ]);
            header('Location: usevi.php');
            exit;
        }
    }
}

$vNaziv      = isset($_POST['naziv_useva']) ? $_POST['naziv_useva'] : $usev->naziv_useva;
$vKategorija = isset($_POST['kategorija']) ? $_POST['kategorija'] : $usev->kategorija;
$vSetva      = isset($_POST['datum_setve']) ? $_POST['datum_setve'] : $usev->datum_setve;
$vBerba      = isset($_POST['ocekivana_berba']) ? $_POST['ocekivana_berba'] : $usev->ocekivana_berba;
$vZemljiste  = isset($_POST['zemljiste_id']) ? (int) $_POST['zemljiste_id'] : $usev->zemljiste_id;

$listaZemljista = $zemljisteRepo->sve();

$naslovStrane = 'Izmena useva - Moje Gazdinstvo';
$aktivna      = 'usevi';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🌱 Izmena useva</h1>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Izmena: <?php echo e($usev->naziv_useva); ?></div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="naziv_useva">Naziv useva <span class="text-danger">*</span></label>
            <input type="text" id="naziv_useva" name="naziv_useva" class="form-control" value="<?php echo e($vNaziv); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="kategorija">Kategorija <span class="text-danger">*</span></label>
            <select id="kategorija" name="kategorija" class="form-select" required>
              <?php foreach (Usev::KATEGORIJE as $kod => $naz): ?>
                <option value="<?php echo e($kod); ?>" <?php echo $vKategorija === $kod ? 'selected' : ''; ?>><?php echo e($naz); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="zemljiste_id">Parcela <span class="text-danger">*</span></label>
            <select id="zemljiste_id" name="zemljiste_id" class="form-select" required>
              <?php foreach ($listaZemljista as $z): ?>
                <option value="<?php echo (int) $z['id']; ?>" <?php echo $vZemljiste === (int) $z['id'] ? 'selected' : ''; ?>>
                  <?php echo e($z['naziv_parcele']); ?> (<?php echo e($z['lokacija']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_setve">Datum setve <span class="text-danger">*</span></label>
              <input type="date" id="datum_setve" name="datum_setve" class="form-control" value="<?php echo e($vSetva); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="ocekivana_berba">Očekivana berba</label>
              <input type="date" id="ocekivana_berba" name="ocekivana_berba" class="form-control" value="<?php echo e($vBerba); ?>">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">Sačuvaj izmene</button>
            <a href="usevi.php" class="btn btn-outline-secondary">Otkaži</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
