<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ZemljisteRepozitorijum.php';
require_once __DIR__ . '/klase/UsevRepozitorijum.php';
require_once __DIR__ . '/klase/StokaRepozitorijum.php';
require_once __DIR__ . '/klase/ZalihaRepozitorijum.php';
require_once __DIR__ . '/klase/ProdajaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}

$zemljisteRepo = new ZemljisteRepozitorijum();
$usevRepo      = new UsevRepozitorijum();
$stokaRepo     = new StokaRepozitorijum();
$zalihaRepo    = new ZalihaRepozitorijum();
$prodajaRepo   = new ProdajaRepozitorijum();

$brojParcela    = $zemljisteRepo->prebroj();
$ukupnaPovrsina = $zemljisteRepo->ukupnaPovrsina();
$brojUseva      = $usevRepo->prebroj();
$ukupnoGrla     = $stokaRepo->ukupnoGrla();
$brojZaliha     = $zalihaRepo->prebroj();
$isticeUskoro   = $zalihaRepo->brojSaIstekomUskoro();
$ukupanPromet   = $prodajaRepo->ukupanPromet();

$poslednjeProdaje = array_slice($prodajaRepo->sve(), 0, 5);

$naslovStrane = 'Početna - Moje Gazdinstvo';
$aktivna      = 'pocetna';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">Dobrodošli, <?php echo e($korisnik->korisnicko_ime); ?>! 🌾</h1>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card shadow-sm h-100 position-relative kartica-statistika akcent-braon">
      <div class="card-body">
        <div class="stat-ikona">🌍</div>
        <div class="stat-broj text-success"><?php echo $brojParcela; ?></div>
        <div class="text-muted">Parcela (<?php echo broj($ukupnaPovrsina, 2); ?> ha)</div>
        <a href="zemljiste.php" class="stretched-link" aria-label="Zemljište"></a>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card shadow-sm h-100 position-relative kartica-statistika">
      <div class="card-body">
        <div class="stat-ikona">🌱</div>
        <div class="stat-broj text-success"><?php echo $brojUseva; ?></div>
        <div class="text-muted">Useva u evidenciji</div>
        <a href="usevi.php" class="stretched-link" aria-label="Usevi"></a>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card shadow-sm h-100 position-relative kartica-statistika akcent-braon">
      <div class="card-body">
        <div class="stat-ikona">🐄</div>
        <div class="stat-broj text-success"><?php echo $ukupnoGrla; ?></div>
        <div class="text-muted">Grla stoke</div>
        <a href="stoka.php" class="stretched-link" aria-label="Stoka"></a>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card shadow-sm h-100 position-relative kartica-statistika akcent-zuta">
      <div class="card-body">
        <div class="stat-ikona">📦</div>
        <div class="stat-broj text-success"><?php echo $brojZaliha; ?></div>
        <div class="text-muted">
          Stavki na zalihama
          <?php if ($isticeUskoro > 0): ?>
            <span class="badge badge-istice">⚠ <?php echo $isticeUskoro; ?> ističe uskoro</span>
          <?php endif; ?>
        </div>
        <a href="zalihe.php" class="stretched-link" aria-label="Zalihe"></a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card text-center shadow-sm h-100 position-relative kartica-statistika akcent-svetlozelena">
      <div class="card-body">
        <div class="text-muted mb-1">Ukupan promet od prodaje</div>
        <div class="h4 fw-bold mb-0">💰 <?php echo rsd($ukupanPromet); ?></div>
        <a href="prodaja.php" class="stretched-link" aria-label="Prodaja"></a>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="row g-2 h-100">
      <div class="col-6">
        <a href="izvestaji.php" class="meni-kartica bg-izvestaji h-100 d-flex flex-column justify-content-center">
          <span class="meni-ikona">📊</span>
          Pregled izveštaja
        </a>
      </div>
      <div class="col-6">
        <a href="prodaja_izmena.php" class="meni-kartica bg-prodaja h-100 d-flex flex-column justify-content-center">
          <span class="meni-ikona">➕</span>
          Nova prodaja
        </a>
      </div>
    </div>
  </div>
</div>

<h2 class="h5 mb-3">Brzi pristup</h2>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-lg-2">
    <a href="zemljiste.php" class="meni-kartica bg-zemljiste">
      <span class="meni-ikona">🌍</span>
      Zemljište
    </a>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <a href="usevi.php" class="meni-kartica bg-usevi">
      <span class="meni-ikona">🌱</span>
      Usevi
    </a>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <a href="stoka.php" class="meni-kartica bg-stoka">
      <span class="meni-ikona">🐄</span>
      Stoka
    </a>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <a href="zalihe.php" class="meni-kartica bg-zalihe">
      <span class="meni-ikona">📦</span>
      Zalihe
    </a>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <a href="prodaja.php" class="meni-kartica bg-prodaja">
      <span class="meni-ikona">💰</span>
      Prodaja
    </a>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <a href="izvestaji.php" class="meni-kartica bg-izvestaji">
      <span class="meni-ikona">📊</span>
      Izveštaji
    </a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header">Poslednje prodaje</div>
  <div class="card-body table-responsive">
    <?php if (empty($poslednjeProdaje)): ?>
      <p class="text-muted mb-0">Trenutno nema unetih prodaja.
        <a href="prodaja_izmena.php">Unesite prvu prodaju</a>.</p>
    <?php else: ?>
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Datum</th>
            <th>Kupac</th>
            <th>Prodavac</th>
            <th class="text-end">Ukupan iznos</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($poslednjeProdaje as $p): ?>
            <tr>
              <td><?php echo datum($p['datum_prodaje']); ?></td>
              <td><?php echo e($p['kupac']); ?></td>
              <td><?php echo e(isset($p['prodavac']) ? $p['prodavac'] : '-'); ?></td>
              <td class="text-end"><?php echo rsd($p['ukupan_iznos']); ?></td>
              <td class="text-end">
                <a href="prodaja_detalji.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-outline-primary">Detalji</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/podnozje.php'; ?>
