<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ProdajaRepozitorijum.php';
require_once __DIR__ . '/klase/StavkaProdajeRepozitorijum.php';
require_once __DIR__ . '/klase/ZalihaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija  = $auth->sesija();
$korisnikId = $auth->trenutniKorisnik()->id;

$prodajaRepo  = new ProdajaRepozitorijum();
$stavkaRepo   = new StavkaProdajeRepozitorijum();
$zalihaRepo   = new ZalihaRepozitorijum();

// Da li je izmena postojeće prodaje ili nova
$id     = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$prodaja = $id > 0 ? $prodajaRepo->read($id) : null;

if ($id > 0 && !$prodaja) {
    header('Location: prodaja.php');
    exit;
}

$greske  = [];
$uspeh   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['sacuvaj_zaglavlje'])) {
        // Sačuvaj / ažuriraj zaglavlje prodaje
        $datumProdaje = trim(isset($_POST['datum_prodaje']) ? $_POST['datum_prodaje'] : '');
        $kupac        = trim(isset($_POST['kupac']) ? $_POST['kupac'] : '');

        if ($datumProdaje === '') {
            $greske[] = 'Datum prodaje je obavezan.';
        }
        if ($kupac === '') {
            $greske[] = 'Ime kupca je obavezno.';
        }

        if (empty($greske)) {
            if ($prodaja) {
                $prodajaRepo->update($id, [
                    'datum_prodaje' => $datumProdaje,
                    'kupac'         => $kupac,
                    'ukupan_iznos'  => $prodaja->ukupan_iznos,
                ]);
                $uspeh = 'Zaglavlje prodaje je sačuvano.';
            } else {
                $noviId = $prodajaRepo->create([
                    'datum_prodaje' => $datumProdaje,
                    'kupac'         => $kupac,
                    'ukupan_iznos'  => 0,
                    'kreirao'       => $korisnikId,
                ]);
                header('Location: prodaja_izmena.php?id=' . $noviId . '&nova=1');
                exit;
            }
            $prodaja = $prodajaRepo->read($id);
        }
    } elseif (isset($_POST['dodaj_stavku']) && $prodaja) {
        // Dodaj stavku prodaje
        $proizvodNaziv = trim(isset($_POST['proizvod']) ? $_POST['proizvod'] : '');
        $kolicina      = (float) (isset($_POST['kolicina']) ? $_POST['kolicina'] : 0);
        $cena          = (float) (isset($_POST['cena']) ? $_POST['cena'] : 0);
        $zalihaId      = (int) (isset($_POST['zaliha_id']) ? $_POST['zaliha_id'] : 0);

        if ($proizvodNaziv === '') {
            $greske[] = 'Naziv proizvoda je obavezan.';
        }
        if ($kolicina <= 0) {
            $greske[] = 'Količina mora biti veća od nule.';
        }
        if ($cena < 0) {
            $greske[] = 'Cena ne može biti negativna.';
        }

        if (empty($greske)) {
            $stavkaRepo->create([
                'prodaja_id' => $id,
                'zaliha_id'  => $zalihaId > 0 ? $zalihaId : null,
                'proizvod'   => $proizvodNaziv,
                'kolicina'   => $kolicina,
                'cena'       => $cena,
            ]);
            $prodajaRepo->preracunajUkupanIznos($id);
            $prodaja = $prodajaRepo->read($id);
            $uspeh   = 'Stavka je dodata.';
        }
    } elseif (isset($_POST['obrisi_stavku']) && $prodaja) {
        $stavkaRepo->delete((int) $_POST['obrisi_stavku']);
        $prodajaRepo->preracunajUkupanIznos($id);
        $prodaja = $prodajaRepo->read($id);
        $uspeh   = 'Stavka je obrisana.';
    }
}

$listaZaliha = $zalihaRepo->sve();
$stavke      = $prodaja ? $stavkaRepo->zaProdaju($id) : [];

$jNova = isset($_GET['nova']);

$naslovStrane = $prodaja ? 'Izmena prodaje - Moje Gazdinstvo' : 'Nova prodaja - Moje Gazdinstvo';
$aktivna      = 'prodaja';
require_once __DIR__ . '/zaglavlje.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <h1 class="h3 mb-0">💰 <?php echo $prodaja ? 'Izmena prodaje' : 'Nova prodaja'; ?></h1>
  <a href="prodaja.php" class="btn btn-outline-secondary">← Nazad na prodaje</a>
</div>

<?php if ($jNova): ?>
  <div class="alert alert-success">✅ Prodaja je kreirana. Sada dodajte stavke ispod.</div>
<?php endif; ?>
<?php if ($uspeh): ?>
  <div class="alert alert-success"><?php echo e($uspeh); ?></div>
<?php endif; ?>
<?php if ($greske): ?>
  <div class="alert alert-danger"><?php echo implode('<br>', array_map('e', $greske)); ?></div>
<?php endif; ?>

<!-- Zaglavlje prodaje -->
<div class="card shadow-sm mb-4">
  <div class="card-header">Podaci o prodaji</div>
  <div class="card-body">
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label" for="datum_prodaje">Datum prodaje <span class="text-danger">*</span></label>
          <input type="date" id="datum_prodaje" name="datum_prodaje" class="form-control"
                 value="<?php echo e($prodaja ? $prodaja->datum_prodaje : date('Y-m-d')); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="kupac">Kupac <span class="text-danger">*</span></label>
          <input type="text" id="kupac" name="kupac" class="form-control"
                 value="<?php echo e($prodaja ? $prodaja->kupac : ''); ?>"
                 placeholder="Ime i prezime / naziv firme..." required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" name="sacuvaj_zaglavlje" value="1" class="btn btn-primary w-100">
            <?php echo $prodaja ? 'Ažuriraj' : 'Kreiraj prodaju'; ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($prodaja): ?>
<!-- Stavke prodaje -->
<div class="row">
  <div class="col-lg-5 mb-4">
    <div class="card shadow-sm">
      <div class="card-header">Dodaj stavku</div>
      <div class="card-body">
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="zaliha_id">Iz zaliha (opciono)</label>
            <select id="zaliha_id" name="zaliha_id" class="form-select"
                    onchange="popuniProizvod(this)">
              <option value="">-- slobodan unos --</option>
              <?php foreach ($listaZaliha as $z): ?>
                <option value="<?php echo (int) $z['id']; ?>"
                        data-naziv="<?php echo e($z['naziv_proizvoda']); ?>">
                  <?php echo e($z['naziv_proizvoda']); ?>
                  (<?php echo broj($z['kolicina'], 2); ?> <?php echo e($z['jedinica_mere']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="proizvod">Naziv proizvoda <span class="text-danger">*</span></label>
            <input type="text" id="proizvod" name="proizvod" class="form-control"
                   placeholder="Npr. Pšenica, Mleko..." required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="kolicina">Količina <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0.01" id="kolicina" name="kolicina" class="form-control" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="cena">Cena (RSD) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="cena" name="cena" class="form-control" required>
            </div>
          </div>
          <button type="submit" name="dodaj_stavku" value="1" class="btn btn-primary w-100">➕ Dodaj stavku</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-light d-flex justify-content-between">
        <span>Stavke prodaje</span>
        <span class="fw-bold text-success">Ukupno: <?php echo rsd($prodaja->ukupan_iznos); ?></span>
      </div>
      <div class="card-body table-responsive">
        <?php if (empty($stavke)): ?>
          <p class="text-muted mb-0">Još nema stavki. Dodajte prvu stavku levo.</p>
        <?php else: ?>
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Proizvod</th>
                <th class="text-end">Kol.</th>
                <th class="text-end">Cena</th>
                <th class="text-end">Ukupno</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($stavke as $st): ?>
                <tr>
                  <td><?php echo e($st['naziv_proizvoda']); ?></td>
                  <td class="text-end"><?php echo broj($st['kolicina'], 2); ?></td>
                  <td class="text-end"><?php echo rsd($st['cena_po_jedinici']); ?></td>
                  <td class="text-end fw-semibold"><?php echo rsd($st['kolicina'] * $st['cena_po_jedinici']); ?></td>
                  <td class="text-end">
                    <form method="post" class="d-inline" onsubmit="return confirm('Obrisati stavku?');">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                      <button type="submit" name="obrisi_stavku" value="<?php echo (int) $st['id']; ?>"
                              class="btn btn-sm btn-danger">✕</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-success fw-bold">
                <td colspan="3">UKUPNO</td>
                <td class="text-end"><?php echo rsd($prodaja->ukupan_iznos); ?></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        <?php endif; ?>
      </div>
      <div class="card-footer text-end">
        <a href="prodaja_detalji.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">📄 Pregled detalja</a>
        <a href="prodaja.php" class="btn btn-success">✅ Završi</a>
      </div>
    </div>
  </div>
</div>

<script>
function popuniProizvod(sel) {
    var naziv = sel.options[sel.selectedIndex].getAttribute('data-naziv');
    if (naziv) {
        document.getElementById('proizvod').value = naziv;
    }
}
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/podnozje.php'; ?>
