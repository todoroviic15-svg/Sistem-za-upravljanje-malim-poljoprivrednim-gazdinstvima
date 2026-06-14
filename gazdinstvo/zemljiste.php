<?php
require_once __DIR__ . '/klase/Autentifikacija.php';
require_once __DIR__ . '/klase/ZemljisteRepozitorijum.php';

$auth = new Autentifikacija();
if (!$auth->jePrijavljen()) {
    header('Location: prijava.php');
    exit;
}
$sesija = $auth->sesija();

$repo   = new ZemljisteRepozitorijum();
$greske = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$sesija->proveriCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $greske[] = 'Nevažeći zahtev, pokušajte ponovo.';
    } elseif (isset($_POST['obrisi'])) {
        $repo->delete((int) $_POST['obrisi']);
        header('Location: zemljiste.php');
        exit;
    } elseif (isset($_POST['dodaj'])) {
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
            $repo->create([
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

$pojam = trim(isset($_GET['pretraga']) ? $_GET['pretraga'] : '');
$listaZemljista = $pojam !== '' ? $repo->pretraga($pojam) : $repo->sve();

$naslovStrane = 'Zemljište - Moje Gazdinstvo';
$aktivna      = 'zemljiste';
require_once __DIR__ . '/zaglavlje.php';
?>
<h1 class="h3 mb-4">🌍 Zemljište - parcele gazdinstva</h1>

<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-braon">Nova parcela</div>
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
            <input type="text" id="naziv_parcele" name="naziv_parcele" class="form-control"
                   value="<?php echo e(isset($_POST['naziv_parcele']) ? $_POST['naziv_parcele'] : ''); ?>"
                   placeholder="Npr. Donja bara, Vinogradi..." required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label" for="povrsina">Površina (ha) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" id="povrsina" name="povrsina" class="form-control"
                     value="<?php echo e(isset($_POST['povrsina']) ? $_POST['povrsina'] : ''); ?>" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label" for="tip_zemljista">Tip zemljišta <span class="text-danger">*</span></label>
              <select id="tip_zemljista" name="tip_zemljista" class="form-select" required>
                <?php foreach (Zemljiste::TIPOVI as $kod => $naz): ?>
                  <option value="<?php echo e($kod); ?>"
                    <?php echo (isset($_POST['tip_zemljista']) && $_POST['tip_zemljista'] === $kod) ? 'selected' : ''; ?>>
                    <?php echo e($naz); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="lokacija">Lokacija <span class="text-danger">*</span></label>
            <input type="text" id="lokacija" name="lokacija" class="form-control"
                   value="<?php echo e(isset($_POST['lokacija']) ? $_POST['lokacija'] : ''); ?>"
                   placeholder="Selo, opština..." required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="napomena">Napomena</label>
            <textarea id="napomena" name="napomena" class="form-control" rows="2"><?php echo e(isset($_POST['napomena']) ? $_POST['napomena'] : ''); ?></textarea>
          </div>
          <button type="submit" name="dodaj" value="1" class="btn btn-primary w-100">🌍 Sačuvaj parcelu</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8 mb-4">
    <div class="card shadow-sm">
      <div class="card-header card-header-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Spisak parcela</span>
        <form method="get" class="d-flex gap-2">
          <input type="text" name="pretraga" class="form-control form-control-sm" placeholder="Pretraga..."
                 value="<?php echo e($pojam); ?>">
          <button type="submit" class="btn btn-sm btn-outline-primary">🔍 Pretraži</button>
          <?php if ($pojam !== ''): ?>
            <a href="zemljiste.php" class="btn btn-sm btn-outline-secondary">Poništi</a>
          <?php endif; ?>
        </form>
      </div>
      <div class="card-body table-responsive">
        <?php if (empty($listaZemljista)): ?>
          <p class="text-muted mb-0">Nema parcela koje odgovaraju kriterijumu.</p>
        <?php else: ?>
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Naziv parcele</th>
                <th>Tip zemljišta</th>
                <th class="text-end">Površina (ha)</th>
                <th>Lokacija</th>
                <th class="text-end">Akcije</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($listaZemljista as $z): ?>
                <tr>
                  <td><?php echo (int) $z['id']; ?></td>
                  <td><?php echo e($z['naziv_parcele']); ?></td>
                  <td><?php echo e(isset(Zemljiste::TIPOVI[$z['tip_zemljista']]) ? Zemljiste::TIPOVI[$z['tip_zemljista']] : $z['tip_zemljista']); ?></td>
                  <td class="text-end"><?php echo broj($z['povrsina'], 2); ?></td>
                  <td><?php echo e($z['lokacija']); ?></td>
                  <td class="text-end text-nowrap">
                    <a href="zemljiste_izmena.php?id=<?php echo (int) $z['id']; ?>" class="btn btn-sm btn-warning">Izmeni</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Obrisati parcelu?');">
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
