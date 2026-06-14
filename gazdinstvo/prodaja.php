<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ProdajaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo   = new ProdajaRepozitorijum();
$greske = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['obrisi'])) {
        $repo->delete((int) $_POST['obrisi']);
        header('Location: prodaja.php');
        exit;
    }
}

$listaProdaja = $repo->sve();
$ukupanPromet = $repo->ukupanPromet();

$naslovStrane = 'Prodaja - Moje Gazdinstvo';
$aktivna      = 'prodaja';
require_once __DIR__ . '/zaglavlje.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <h1 class="h3 mb-0">💰 Prodaja proizvoda</h1>
  <a href="prodaja_izmena.php" class="btn btn-primary">➕ Nova prodaja</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card text-center shadow-sm kartica-statistika akcent-zuta">
      <div class="card-body">
        <div class="text-muted mb-1">Ukupan promet</div>
        <div class="h4 fw-bold mb-0"><?php echo rsd($ukupanPromet); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm kartica-statistika">
      <div class="card-body">
        <div class="text-muted mb-1">Broj prodaja</div>
        <div class="h4 fw-bold mb-0"><?php echo count($listaProdaja); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center shadow-sm kartica-statistika akcent-svetlozelena">
      <div class="card-body">
        <div class="text-muted mb-1">Prosek po prodaji</div>
        <div class="h4 fw-bold mb-0">
          <?php echo count($listaProdaja) > 0 ? rsd($ukupanPromet / count($listaProdaja)) : rsd(0); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($greske): ?>
  <div class="alert alert-danger"><?php echo implode('<br>', array_map('e', $greske)); ?></div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-header card-header-light">Sve prodaje</div>
  <div class="card-body table-responsive">
    <?php if (empty($listaProdaja)): ?>
      <p class="text-muted mb-0">Još uvek nema unetih prodaja. <a href="prodaja_izmena.php">Unesite prvu prodaju.</a></p>
    <?php else: ?>
      <table class="table table-striped table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Datum</th>
            <th>Kupac</th>
            <th>Prodavac</th>
            <th class="text-end">Ukupan iznos</th>
            <th class="text-end">Akcije</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($listaProdaja as $p): ?>
            <tr>
              <td><?php echo (int) $p['id']; ?></td>
              <td><?php echo datum($p['datum_prodaje']); ?></td>
              <td><?php echo e($p['kupac']); ?></td>
              <td><?php echo e(isset($p['prodavac']) ? $p['prodavac'] : '-'); ?></td>
              <td class="text-end fw-semibold"><?php echo rsd($p['ukupan_iznos']); ?></td>
              <td class="text-end text-nowrap">
                <a href="prodaja_detalji.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-outline-primary">Detalji</a>
                <a href="prodaja_izmena.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-warning">Izmeni</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Obrisati prodaju i sve njene stavke?');">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                  <button type="submit" name="obrisi" value="<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-danger">Obriši</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
