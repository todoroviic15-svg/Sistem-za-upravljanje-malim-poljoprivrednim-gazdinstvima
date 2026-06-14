<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ZemljisteRepozitorijum.php';
require_once __DIR__ . '/klase/UsevRepozitorijum.php';
require_once __DIR__ . '/klase/StokaRepozitorijum.php';
require_once __DIR__ . '/klase/ZalihaRepozitorijum.php';
require_once __DIR__ . '/klase/ProdajaRepozitorijum.php';
require_once __DIR__ . '/klase/StavkaProdajeRepozitorijum.php';

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
$stavkaRepo    = new StavkaProdajeRepozitorijum();

// Koji izveštaj je aktivan (tab)
$aktivniTab = isset($_GET['tab']) ? $_GET['tab'] : 'parcele';

// Filteri za prodaju po periodu
$odDatum = trim(isset($_GET['od']) ? $_GET['od'] : date('Y-m-01'));
$doDatum = trim(isset($_GET['do']) ? $_GET['do'] : date('Y-m-d'));

// Podaci za izveštaje
$sveParcele   = $zemljisteRepo->sve();
$useviByte    = $usevRepo->pregledPoParcelama();
$svaStokaLista = $stokaRepo->sve();
$sveZalihe    = $zalihaRepo->sve();
$prodajeZaPeriod = $prodajaRepo->zaPeriod($odDatum, $doDatum);
$ukupnoZaPeriod  = $prodajaRepo->ukupnoZaPeriod($odDatum, $doDatum);
$najprodavaniji  = $stavkaRepo->najprodavanijiProizvodi();

$naslovStrane = 'Izveštaji - Moje Gazdinstvo';
$aktivna      = 'izvestaji';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">📊 Izveštaji</h1>

<!-- Tabovi za izbor izveštaja -->
<ul class="nav nav-tabs mb-4 flex-wrap">
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'parcele' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=parcele">🌍 Pregled parcela</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'usevi' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=usevi">🌱 Usevi po parcelama</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'stoka' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=stoka">🐄 Evidencija stoke</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'zalihe' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=zalihe">📦 Stanje zaliha</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'prodaja' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=prodaja">💰 Prodaja po periodu</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo $aktivniTab === 'najprodavaniji' ? 'active' : ''; ?>"
       href="izvestaji.php?tab=najprodavaniji">🏆 Najprodavaniji proizvodi</a>
  </li>
</ul>

<!-- ============================================================
     IZVEŠTAJ 1: Pregled svih parcela
     ============================================================ -->
<?php if ($aktivniTab === 'parcele'): ?>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>🌍 Pregled svih parcela</span>
    <small class="text-white-50">Ukupno: <?php echo count($sveParcele); ?> parcela,
      <?php echo broj($zemljisteRepo->ukupnaPovrsina(), 2); ?> ha</small>
  </div>
  <div class="card-body table-responsive">
    <?php if (empty($sveParcele)): ?>
      <p class="text-muted">Nema unetih parcela.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Naziv parcele</th>
            <th>Tip zemljišta</th>
            <th class="text-end">Površina (ha)</th>
            <th>Lokacija</th>
            <th>Napomena</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sveParcele as $p): ?>
            <tr>
              <td><?php echo (int) $p['id']; ?></td>
              <td><?php echo e($p['naziv_parcele']); ?></td>
              <td><?php echo e(isset(Zemljiste::TIPOVI[$p['tip_zemljista']]) ? Zemljiste::TIPOVI[$p['tip_zemljista']] : $p['tip_zemljista']); ?></td>
              <td class="text-end"><?php echo broj($p['povrsina'], 2); ?></td>
              <td><?php echo e($p['lokacija']); ?></td>
              <td><?php echo e($p['napomena'] ? $p['napomena'] : '-'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-success fw-bold">
            <td colspan="3">UKUPNO POVRŠINA</td>
            <td class="text-end"><?php echo broj($zemljisteRepo->ukupnaPovrsina(), 2); ?> ha</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============================================================
     IZVEŠTAJ 2: Usevi po parcelama
     ============================================================ -->
<?php elseif ($aktivniTab === 'usevi'): ?>
<div class="card shadow-sm">
  <div class="card-header">🌱 Pregled useva po parcelama</div>
  <div class="card-body table-responsive">
    <?php if (empty($useviByte)): ?>
      <p class="text-muted">Nema unetih parcela ili useva.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Parcela</th>
            <th>Lokacija</th>
            <th class="text-end">Površina (ha)</th>
            <th>Tip</th>
            <th>Usev</th>
            <th>Kategorija</th>
            <th>Datum setve</th>
            <th>Očekivana berba</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($useviByte as $r): ?>
            <tr>
              <td><?php echo e($r['naziv_parcele']); ?></td>
              <td><?php echo e($r['lokacija']); ?></td>
              <td class="text-end"><?php echo broj($r['povrsina'], 2); ?></td>
              <td><?php echo e(isset(Zemljiste::TIPOVI[$r['tip_zemljista']]) ? Zemljiste::TIPOVI[$r['tip_zemljista']] : $r['tip_zemljista']); ?></td>
              <td><?php echo $r['naziv_useva'] ? e($r['naziv_useva']) : '<span class="text-muted">-</span>'; ?></td>
              <td><?php echo $r['kategorija'] ? e(isset(Usev::KATEGORIJE[$r['kategorija']]) ? Usev::KATEGORIJE[$r['kategorija']] : $r['kategorija']) : '-'; ?></td>
              <td><?php echo datum($r['datum_setve']); ?></td>
              <td><?php echo datum($r['ocekivana_berba']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============================================================
     IZVEŠTAJ 3: Evidencija stoke
     ============================================================ -->
<?php elseif ($aktivniTab === 'stoka'): ?>
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>🐄 Evidencija stoke</span>
    <small class="text-white-50">Ukupno: <?php echo $stokaRepo->ukupnoGrla(); ?> grla</small>
  </div>
  <div class="card-body table-responsive">
    <?php if (empty($svaStokaLista)): ?>
      <p class="text-muted">Nema unetih podataka o stoci.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Vrsta</th>
            <th>Rasa</th>
            <th class="text-end">Broj grla</th>
            <th>Datum nabavke</th>
            <th>Napomena</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($svaStokaLista as $s): ?>
            <tr>
              <td><?php echo (int) $s['id']; ?></td>
              <td><?php echo e(isset(Stoka::VRSTE[$s['vrsta']]) ? Stoka::VRSTE[$s['vrsta']] : $s['vrsta']); ?></td>
              <td><?php echo e($s['rasa'] ? $s['rasa'] : '-'); ?></td>
              <td class="text-end"><?php echo (int) $s['broj_grla']; ?></td>
              <td><?php echo datum($s['datum_nabavke']); ?></td>
              <td><?php echo e($s['napomena'] ? $s['napomena'] : '-'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-success fw-bold">
            <td colspan="3">UKUPNO GRLA</td>
            <td class="text-end"><?php echo $stokaRepo->ukupnoGrla(); ?></td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============================================================
     IZVEŠTAJ 4: Stanje zaliha
     ============================================================ -->
<?php elseif ($aktivniTab === 'zalihe'): ?>
<div class="card shadow-sm">
  <div class="card-header">📦 Stanje zaliha</div>
  <div class="card-body table-responsive">
    <?php if (empty($sveZalihe)): ?>
      <p class="text-muted">Nema unetih zaliha.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Naziv proizvoda</th>
            <th class="text-end">Količina</th>
            <th>Jed.</th>
            <th>Datum ulaza</th>
            <th>Rok trajanja</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sveZalihe as $z):
            $zObj = new Zaliha($z);
          ?>
            <tr>
              <td><?php echo (int) $z['id']; ?></td>
              <td><?php echo e($z['naziv_proizvoda']); ?></td>
              <td class="text-end"><?php echo broj($z['kolicina'], 2); ?></td>
              <td><?php echo e($z['jedinica_mere']); ?></td>
              <td><?php echo datum($z['datum_ulaza']); ?></td>
              <td><?php echo datum($z['datum_isteka']); ?></td>
              <td>
                <?php if ($zObj->jeIstekla()): ?>
                  <span class="badge bg-danger">Istekla</span>
                <?php elseif ($zObj->isticeUskoro()): ?>
                  <span class="badge badge-istice">⚡ Uskoro ističe</span>
                <?php elseif (empty($z['datum_isteka'])): ?>
                  <span class="badge bg-secondary">Bez roka</span>
                <?php else: ?>
                  <span class="badge bg-success">U redu</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============================================================
     IZVEŠTAJ 5: Prodaja po periodu
     ============================================================ -->
<?php elseif ($aktivniTab === 'prodaja'): ?>
<div class="card shadow-sm mb-3">
  <div class="card-header card-header-light">Odaberite period</div>
  <div class="card-body">
    <form method="get" class="row g-3 align-items-end">
      <input type="hidden" name="tab" value="prodaja">
      <div class="col-md-4">
        <label class="form-label" for="od">Od datuma</label>
        <input type="date" id="od" name="od" class="form-control" value="<?php echo e($odDatum); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="do">Do datuma</label>
        <input type="date" id="do" name="do" class="form-control" value="<?php echo e($doDatum); ?>">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100">🔍 Prikaži izveštaj</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>💰 Prodaja od <?php echo datum($odDatum); ?> do <?php echo datum($doDatum); ?></span>
    <span class="fw-bold text-white">Ukupno: <?php echo rsd($ukupnoZaPeriod); ?></span>
  </div>
  <div class="card-body table-responsive">
    <?php if (empty($prodajeZaPeriod)): ?>
      <p class="text-muted">Nema prodaja u odabranom periodu.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Datum</th>
            <th>Kupac</th>
            <th>Prodavac</th>
            <th class="text-end">Iznos</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($prodajeZaPeriod as $p): ?>
            <tr>
              <td><?php echo (int) $p['id']; ?></td>
              <td><?php echo datum($p['datum_prodaje']); ?></td>
              <td><?php echo e($p['kupac']); ?></td>
              <td><?php echo e(isset($p['prodavac']) ? $p['prodavac'] : '-'); ?></td>
              <td class="text-end fw-semibold"><?php echo rsd($p['ukupan_iznos']); ?></td>
              <td>
                <a href="prodaja_detalji.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-outline-primary">Detalji</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-success fw-bold">
            <td colspan="4">UKUPNO ZA PERIOD</td>
            <td class="text-end"><?php echo rsd($ukupnoZaPeriod); ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- ============================================================
     IZVEŠTAJ 6: Najprodavaniji proizvodi
     ============================================================ -->
<?php elseif ($aktivniTab === 'najprodavaniji'): ?>
<div class="card shadow-sm">
  <div class="card-header">🏆 Najprodavaniji proizvodi</div>
  <div class="card-body table-responsive">
    <?php if (empty($najprodavaniji)): ?>
      <p class="text-muted">Nema podataka o prodaji.</p>
    <?php else: ?>
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
          <tr>
            <th>Mesto</th>
            <th>Naziv proizvoda</th>
            <th class="text-end">Ukupna kol.</th>
            <th class="text-end">Broj prodaja</th>
            <th class="text-end">Ukupan promet</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($najprodavaniji as $i => $np): ?>
            <tr>
              <td>
                <?php if ($i === 0): ?>
                  🥇
                <?php elseif ($i === 1): ?>
                  🥈
                <?php elseif ($i === 2): ?>
                  🥉
                <?php else: ?>
                  <?php echo $i + 1; ?>.
                <?php endif; ?>
              </td>
              <td><?php echo e($np['naziv_proizvoda']); ?></td>
              <td class="text-end"><?php echo broj($np['ukupna_kolicina'], 2); ?></td>
              <td class="text-end"><?php echo (int) $np['broj_prodaja']; ?></td>
              <td class="text-end fw-semibold"><?php echo rsd($np['ukupan_promet']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/podnozje.php'; ?>
