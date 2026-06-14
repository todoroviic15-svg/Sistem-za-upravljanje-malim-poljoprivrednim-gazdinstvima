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
$greske        = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['obrisi'])) {
        $repo->delete((int) $_POST['obrisi']);
        header('Location: usevi.php');
        exit;
    } elseif (isset($_POST['dodaj'])) {
        $naziv      = trim(isset($_POST['naziv_useva']) ? $_POST['naziv_useva'] : '');
        $kategorija = trim(isset($_POST['kategorija']) ? $_POST['kategorija'] : '');
        $setva      = trim(isset($_POST['datum_setve']) ? $_POST['datum_setve'] : '');
        $berba      = trim(isset($_POST['ocekivana_berba']) ? $_POST['ocekivana_berba'] : '');
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
            $repo->create([
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

$listaUseva     = $repo->sve();
$listaZemljista = $zemljisteRepo->sve();

$naslovStrane = 'Usevi - Moje Gazdinstvo';
$aktivna      = 'usevi';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🌱 Usevi</h1>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
      <div class="card-header">Novi usev</div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <?php if (empty($listaZemljista)): ?>
          <div class="alert alert-warning">Prvo morate da unesete bar jednu parcelu na stranici <a href="zemljiste.php">Zemljište</a>.</div>
        <?php else: ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="naziv_useva">Naziv useva <span class="text-danger">*</span></label>
            <input type="text" id="naziv_useva" name="naziv_useva" class="form-control"
                   value="<?php echo e(isset($_POST['naziv_useva']) ? $_POST['naziv_useva'] : ''); ?>"
                   placeholder="Npr. Kukuruz, Pšenica, Paradajz..." required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="kategorija">Kategorija <span class="text-danger">*</span></label>
            <select id="kategorija" name="kategorija" class="form-select" required>
              <?php foreach (Usev::KATEGORIJE as $kod => $naz): ?>
                <option value="<?php echo e($kod); ?>"
                  <?php echo (isset($_POST['kategorija']) && $_POST['kategorija'] === $kod) ? 'selected' : ''; ?>>
                  <?php echo e($naz); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="zemljiste_id">Parcela <span class="text-danger">*</span></label>
            <select id="zemljiste_id" name="zemljiste_id" class="form-select" required>
              <option value="">-- izaberite parcelu --</option>
              <?php foreach ($listaZemljista as $z): ?>
                <option value="<?php echo (int) $z['id']; ?>"
                  <?php echo (isset($_POST['zemljiste_id']) && (int) $_POST['zemljiste_id'] === (int) $z['id']) ? 'selected' : ''; ?>>
                  <?php echo e($z['naziv_parcele']); ?> (<?php echo e($z['lokacija']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_setve">Datum setve <span class="text-danger">*</span></label>
              <input type="date" id="datum_setve" name="datum_setve" class="form-control"
                     value="<?php echo e(isset($_POST['datum_setve']) ? $_POST['datum_setve'] : ''); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="ocekivana_berba">Očekivana berba</label>
              <input type="date" id="ocekivana_berba" name="ocekivana_berba" class="form-control"
                     value="<?php echo e(isset($_POST['ocekivana_berba']) ? $_POST['ocekivana_berba'] : ''); ?>">
            </div>
          </div>
          <button type="submit" name="dodaj" value="1" class="btn btn-primary w-100">🌱 Sačuvaj usev</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-light">Spisak useva</div>
      <div class="card-body table-responsive">
        <?php if (empty($listaUseva)): ?>
          <p class="text-muted mb-0">Još uvek nema unetih useva.</p>
        <?php else: ?>
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Naziv useva</th>
                <th>Kategorija</th>
                <th>Parcela</th>
                <th>Setva</th>
                <th>Očekivana berba</th>
                <th class="text-end">Akcije</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($listaUseva as $u): ?>
                <tr>
                  <td><?php echo (int) $u['id']; ?></td>
                  <td><?php echo e($u['naziv_useva']); ?></td>
                  <td><?php echo e(isset(Usev::KATEGORIJE[$u['kategorija']]) ? Usev::KATEGORIJE[$u['kategorija']] : $u['kategorija']); ?></td>
                  <td><?php echo e(isset($u['naziv_parcele']) && $u['naziv_parcele'] !== null ? $u['naziv_parcele'] : '-'); ?></td>
                  <td><?php echo datum($u['datum_setve']); ?></td>
                  <td><?php echo datum($u['ocekivana_berba']); ?></td>
                  <td class="text-end text-nowrap">
                    <a href="usevi_izmena.php?id=<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-warning">Izmeni</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Obrisati usev?');">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                      <button type="submit" name="obrisi" value="<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-danger">Obriši</button>
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
