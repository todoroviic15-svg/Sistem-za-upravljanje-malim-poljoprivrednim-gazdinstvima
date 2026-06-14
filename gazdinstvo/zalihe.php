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
$greske = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['obrisi'])) {
        $repo->delete((int) $_POST['obrisi']);
        header('Location: zalihe.php');
        exit;
    } elseif (isset($_POST['dodaj'])) {
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
            $repo->create([
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

$pojam      = trim(isset($_GET['pretraga']) ? $_GET['pretraga'] : '');
$listaZaliha = $pojam !== '' ? $repo->pretraga($pojam) : $repo->sve();

$naslovStrane = 'Zalihe - Moje Gazdinstvo';
$aktivna      = 'zalihe';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">📦 Zalihe - stanje skladišta</h1>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
      <div class="card-header">Novi unos na zalihe</div>
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
                   value="<?php echo e(isset($_POST['naziv_proizvoda']) ? $_POST['naziv_proizvoda'] : ''); ?>"
                   placeholder="Npr. Pšenica, Kukuruz, Seno..." required>
          </div>
          <div class="row">
            <div class="col-7 mb-3">
              <label class="form-label" for="kolicina">Količina <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="kolicina" name="kolicina" class="form-control"
                     value="<?php echo e(isset($_POST['kolicina']) ? $_POST['kolicina'] : ''); ?>" required>
            </div>
            <div class="col-5 mb-3">
              <label class="form-label" for="jedinica_mere">Jed. mere <span class="text-danger">*</span></label>
              <select id="jedinica_mere" name="jedinica_mere" class="form-select" required>
                <?php foreach (Zaliha::JEDINICE as $kod => $naz): ?>
                  <option value="<?php echo e($kod); ?>"
                    <?php echo (isset($_POST['jedinica_mere']) && $_POST['jedinica_mere'] === $kod) ? 'selected' : ''; ?>>
                    <?php echo e($naz); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_ulaza">Datum ulaza <span class="text-danger">*</span></label>
              <input type="date" id="datum_ulaza" name="datum_ulaza" class="form-control"
                     value="<?php echo e(isset($_POST['datum_ulaza']) ? $_POST['datum_ulaza'] : date('Y-m-d')); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_isteka">Rok trajanja</label>
              <input type="date" id="datum_isteka" name="datum_isteka" class="form-control"
                     value="<?php echo e(isset($_POST['datum_isteka']) ? $_POST['datum_isteka'] : ''); ?>">
            </div>
          </div>
          <button type="submit" name="dodaj" value="1" class="btn btn-primary w-100">📦 Sačuvaj zalihu</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Stanje zaliha</span>
        <form method="get" class="d-flex gap-2">
          <input type="text" name="pretraga" class="form-control form-control-sm" placeholder="Pretraga po nazivu..."
                 value="<?php echo e($pojam); ?>">
          <button type="submit" class="btn btn-sm btn-outline-primary">🔍 Pretraži</button>
          <?php if ($pojam !== ''): ?>
            <a href="zalihe.php" class="btn btn-sm btn-outline-secondary">Poništi</a>
          <?php endif; ?>
        </form>
      </div>
      <div class="card-body table-responsive">
        <?php if (empty($listaZaliha)): ?>
          <p class="text-muted mb-0">Nema unetih stavki na zalihama.</p>
        <?php else: ?>
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Naziv proizvoda</th>
                <th class="text-end">Količina</th>
                <th>Jed.</th>
                <th>Datum ulaza</th>
                <th>Rok trajanja</th>
                <th class="text-end">Akcije</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($listaZaliha as $z):
                $zalihaObj = new Zaliha($z);
              ?>
                <tr>
                  <td><?php echo (int) $z['id']; ?></td>
                  <td><?php echo e($z['naziv_proizvoda']); ?></td>
                  <td class="text-end"><?php echo broj($z['kolicina'], 2); ?></td>
                  <td><?php echo e($z['jedinica_mere']); ?></td>
                  <td><?php echo datum($z['datum_ulaza']); ?></td>
                  <td>
                    <?php if (!empty($z['datum_isteka'])): ?>
                      <?php if ($zalihaObj->jeIstekla()): ?>
                        <span class="badge bg-danger">⚠ Istekla <?php echo datum($z['datum_isteka']); ?></span>
                      <?php elseif ($zalihaObj->isticeUskoro()): ?>
                        <span class="badge badge-istice">⚡ Uskoro <?php echo datum($z['datum_isteka']); ?></span>
                      <?php else: ?>
                        <?php echo datum($z['datum_isteka']); ?>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-nowrap">
                    <a href="zalihe_izmena.php?id=<?php echo (int) $z['id']; ?>" class="btn btn-sm btn-warning">Izmeni</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Obrisati stavku sa zaliha?');">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                      <button type="submit" name="obrisi" value="<?php echo (int) $z['id']; ?>" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
