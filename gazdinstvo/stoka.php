<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/StokaRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo   = new StokaRepozitorijum();
$greske = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['obrisi'])) {
        $repo->delete((int) $_POST['obrisi']);
        header('Location: stoka.php');
        exit;
    } elseif (isset($_POST['dodaj'])) {
        $vrsta     = trim(isset($_POST['vrsta']) ? $_POST['vrsta'] : '');
        $rasa      = trim(isset($_POST['rasa']) ? $_POST['rasa'] : '');
        $brojGrla  = (int) (isset($_POST['broj_grla']) ? $_POST['broj_grla'] : 0);
        $nabavka   = trim(isset($_POST['datum_nabavke']) ? $_POST['datum_nabavke'] : '');

        if (!array_key_exists($vrsta, Stoka::VRSTE)) {
            $greske[] = 'Morate izabrati vrstu stoke.';
        }
        if ($brojGrla < 0) {
            $greske[] = 'Broj grla ne može biti negativan.';
        }

        if (empty($greske)) {
            $napomena = trim(isset($_POST['napomena']) ? $_POST['napomena'] : '');
            $repo->create([
                'vrsta'         => $vrsta,
                'rasa'          => $rasa !== '' ? $rasa : null,
                'broj_grla'     => $brojGrla,
                'datum_nabavke' => $nabavka !== '' ? $nabavka : null,
                'napomena'      => $napomena !== '' ? $napomena : null,
            ]);
            header('Location: stoka.php');
            exit;
        }
    }
}

$pojam     = trim(isset($_GET['pretraga']) ? $_GET['pretraga'] : '');
$listaStoke = $pojam !== '' ? $repo->pretraga($pojam) : $repo->sve();
$ukupnoGrla = $repo->ukupnoGrla();

$naslovStrane = 'Stoka - Moje Gazdinstvo';
$aktivna      = 'stoka';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🐄 Stoka <small class="text-muted fs-6">(ukupno <?php echo $ukupnoGrla; ?> grla)</small></h1>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Novo grlo / vrsta stoke</div>
      <div class="card-body">
        <?php if ($greske): ?>
          <div class="alert alert-danger">
            <?php echo implode('<br>', array_map('e', $greske)); ?>
          </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
          <div class="mb-3">
            <label class="form-label" for="vrsta">Vrsta <span class="text-danger">*</span></label>
            <select id="vrsta" name="vrsta" class="form-select" required>
              <option value="">-- izaberite vrstu --</option>
              <?php foreach (Stoka::VRSTE as $kod => $naz): ?>
                <option value="<?php echo e($kod); ?>"
                  <?php echo (isset($_POST['vrsta']) && $_POST['vrsta'] === $kod) ? 'selected' : ''; ?>>
                  <?php echo e($naz); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="rasa">Rasa</label>
            <input type="text" id="rasa" name="rasa" class="form-control"
                   value="<?php echo e(isset($_POST['rasa']) ? $_POST['rasa'] : ''); ?>" placeholder="Npr. Simentalka, Jorkšir...">
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="broj_grla">Broj grla <span class="text-danger">*</span></label>
              <input type="number" min="0" id="broj_grla" name="broj_grla" class="form-control"
                     value="<?php echo e(isset($_POST['broj_grla']) ? $_POST['broj_grla'] : ''); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="datum_nabavke">Datum nabavke</label>
              <input type="date" id="datum_nabavke" name="datum_nabavke" class="form-control"
                     value="<?php echo e(isset($_POST['datum_nabavke']) ? $_POST['datum_nabavke'] : ''); ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" class="form-control" rows="2"><?php echo e(isset($_POST['napomena']) ? $_POST['napomena'] : ''); ?></textarea>
          </div>
          <button type="submit" name="dodaj" value="1" class="btn btn-primary w-100">🐄 Sačuvaj</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Evidencija stoke</span>
        <form method="get" class="d-flex gap-2">
          <input type="text" name="pretraga" class="form-control form-control-sm" placeholder="Pretraga..."
                 value="<?php echo e($pojam); ?>">
          <button type="submit" class="btn btn-sm btn-outline-primary">🔍 Pretraži</button>
          <?php if ($pojam !== ''): ?>
            <a href="stoka.php" class="btn btn-sm btn-outline-secondary">Poništi</a>
          <?php endif; ?>
        </form>
      </div>
      <div class="card-body table-responsive">
        <?php if (empty($listaStoke)): ?>
          <p class="text-muted mb-0">Nema unetih podataka o stoci.</p>
        <?php else: ?>
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Vrsta</th>
                <th>Rasa</th>
                <th class="text-end">Broj grla</th>
                <th>Datum nabavke</th>
                <th class="text-end">Akcije</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($listaStoke as $s): ?>
                <tr>
                  <td><?php echo (int) $s['id']; ?></td>
                  <td><?php echo e(isset(Stoka::VRSTE[$s['vrsta']]) ? Stoka::VRSTE[$s['vrsta']] : $s['vrsta']); ?></td>
                  <td><?php echo e($s['rasa'] !== null && $s['rasa'] !== '' ? $s['rasa'] : '-'); ?></td>
                  <td class="text-end"><?php echo (int) $s['broj_grla']; ?></td>
                  <td><?php echo datum($s['datum_nabavke']); ?></td>
                  <td class="text-end text-nowrap">
                    <a href="stoka_izmena.php?id=<?php echo (int) $s['id']; ?>" class="btn btn-sm btn-warning">Izmeni</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Obrisati zapis o stoci?');">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                      <button type="submit" name="obrisi" value="<?php echo (int) $s['id']; ?>" class="btn btn-sm btn-danger">Obriši</button>
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
