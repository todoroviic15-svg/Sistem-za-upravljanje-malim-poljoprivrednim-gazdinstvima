<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ProdajaRepozitorijum.php';
require_once __DIR__ . '/klase/StavkaProdajeRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}

$prodajaRepo = new ProdajaRepozitorijum();
$stavkaRepo  = new StavkaProdajeRepozitorijum();

$id      = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$prodaja = $prodajaRepo->detalji($id);

if (!$prodaja) {
    header('Location: prodaja.php');
    exit;
}

$stavke = $stavkaRepo->zaProdaju($id);

$naslovStrane = 'Detalji prodaje - Moje Gazdinstvo';
$aktivna      = 'prodaja';
require_once __DIR__ . '/zaglavlje.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <h1 class="h3 mb-0">📄 Detalji prodaje #<?php echo $id; ?></h1>
  <div class="d-flex gap-2">
    <a href="prodaja_izmena.php?id=<?php echo $id; ?>" class="btn btn-warning">Izmeni</a>
    <a href="prodaja.php" class="btn btn-outline-secondary">← Nazad</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1">Datum prodaje</div>
        <div class="fw-semibold fs-5"><?php echo datum($prodaja['datum_prodaje']); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1">Kupac</div>
        <div class="fw-semibold fs-5"><?php echo e($prodaja['kupac']); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100 kartica-statistika akcent-zuta">
      <div class="card-body">
        <div class="text-muted small mb-1">Ukupan iznos</div>
        <div class="fw-bold fs-4 text-success"><?php echo rsd($prodaja['ukupan_iznos']); ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header">Stavke prodaje</div>
  <div class="card-body table-responsive">
    <?php if (empty($stavke)): ?>
      <p class="text-muted mb-0">Ova prodaja nema stavki.</p>
    <?php else: ?>
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Proizvod</th>
            <th class="text-end">Količina</th>
            <th class="text-end">Cena po jed.</th>
            <th class="text-end">Ukupno</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stavke as $i => $st): ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo e($st['naziv_proizvoda']); ?></td>
              <td class="text-end"><?php echo broj($st['kolicina'], 2); ?></td>
              <td class="text-end"><?php echo rsd($st['cena_po_jedinici']); ?></td>
              <td class="text-end fw-semibold"><?php echo rsd($st['kolicina'] * $st['cena_po_jedinici']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-success fw-bold">
            <td colspan="4">UKUPNO</td>
            <td class="text-end"><?php echo rsd($prodaja['ukupan_iznos']); ?></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
