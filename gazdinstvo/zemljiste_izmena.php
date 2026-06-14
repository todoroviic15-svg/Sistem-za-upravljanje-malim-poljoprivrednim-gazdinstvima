<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ZemljisteRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo      = new ZemljisteRepozitorijum();
$id        = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$zemljiste = $repo->read($id);

if (!$zemljiste) {
    header('Location: zemljiste.php');
    exit;
}

$greske = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } else {
        $naziv    = trim(isset($_POST['naziv_parcele']) ? $_POST['naziv_parcele'] : '');
        $povrsina = (float) (isset($_POST['povrsina']) ? $_POST['povrsina'] : 0);
        $tip      = trim(isset($_POST['tip_zemljista']) ? $_POST['tip_zemljista'] : '');
        $lokacija = trim(isset($_POST['lokacija']) ? $_POST['lokacija'] : '');

        if ($naziv === '') {
            $greske[] = 'Naziv parcele je obavezan.';
        }
        if ($povrsina < 0) {
            $greske[] = 'Površina ne može biti negativna.';
        }
        if (!array_key_exists($tip, Zemljiste::TIPOVI)) {
            $greske[] = 'Nepoznat tip zemljišta.';
        }
        if ($lokacija === '') {
            $greske[] = 'Lokacija je obavezna.';
        }

        if (empty($greske)) {
            $napomena = trim(isset($_POST['napomena']) ? $_POST['napomena'] : '');
            $repo->update($id, [
                'naziv_parcele' => $naziv,
                'povrsina'      => $povrsina,
                'tip_zemljista' => $tip,
                'lokacija'      => $lokacija,
                'napomena'      => $napomena !== '' ? $napomena : null,
            ]);
            header('Location: zemljiste.php');
            exit;
        }
    }
}

$vNaziv    = isset($_POST['naziv_parcele']) ? $_POST['naziv_parcele'] : $zemljiste->naziv_parcele;
$vPovrsina = isset($_POST['povrsina']) ? $_POST['povrsina'] : $zemljiste->povrsina;
$vTip      = isset($_POST['tip_zemljista']) ? $_POST['tip_zemljista'] : $zemljiste->tip_zemljista;
$vLokacija = isset($_POST['lokacija']) ? $_POST['lokacija'] : $zemljiste->lokacija;
$vNapomena = isset($_POST['napomena']) ? $_POST['napomena'] : $zemljiste->napomena;

$naslovStrane = 'Izmena parcele - Moje Gazdinstvo';
$aktivna      = 'zemljiste';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🌍 Izmena parcele</h1>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Izmena: <?php echo e($zemljiste->naziv_parcele); ?></div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="naziv_parcele">Naziv parcele <span class="text-danger">*</span></label>
            <input type="text" id="naziv_parcele" name="naziv_parcele" class="form-control" value="<?php echo e($vNaziv); ?>" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="povrsina">Površina (ha) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="povrsina" name="povrsina" class="form-control" value="<?php echo e($vPovrsina); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="tip_zemljista">Tip zemljišta <span class="text-danger">*</span></label>
              <select id="tip_zemljista" name="tip_zemljista" class="form-select" required>
                <?php foreach (Zemljiste::TIPOVI as $kod => $naz): ?>
                  <option value="<?php echo e($kod); ?>" <?php echo $vTip === $kod ? 'selected' : ''; ?>><?php echo e($naz); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="lokacija">Lokacija <span class="text-danger">*</span></label>
            <input type="text" id="lokacija" name="lokacija" class="form-control" value="<?php echo e($vLokacija); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" class="form-control" rows="2"><?php echo e($vNapomena); ?></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">Sačuvaj izmene</button>
            <a href="zemljiste.php" class="btn btn-outline-secondary">Otkaži</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
