<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/Prodaja.php';

/**
 * Repozitorijum za rad sa tabelom prodaja
 * (CRUD operacije + upravljanje stavkama prodaje).
 */
class ProdajaRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'prodaja';

    protected function napraviModel(array $red)
    {
        return new Prodaja($red);
    }

    public function create(array $podaci)
    {
        $upit = $this->pdo->prepare(
            'INSERT INTO prodaja (datum_prodaje, kupac, ukupan_iznos, kreirao) VALUES (?, ?, ?, ?)'
        );
        $upit->execute([
            $podaci['datum_prodaje'],
            $podaci['kupac'],
            $podaci['ukupan_iznos'],
            $podaci['kreirao'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE prodaja SET datum_prodaje = ?, kupac = ?, ukupan_iznos = ? WHERE id = ?'
        );
        return $upit->execute([
            $podaci['datum_prodaje'],
            $podaci['kupac'],
            $podaci['ukupan_iznos'],
            $id,
        ]);
    }

    /**
     * Lista svih prodaja sa imenom korisnika koji je unela prodaju.
     */
    public function sve(): array
    {
        $upit = $this->pdo->query(
            'SELECT p.*, k.korisnicko_ime AS prodavac
             FROM prodaja p
             LEFT JOIN korisnici k ON p.kreirao = k.id
             ORDER BY p.datum_prodaje DESC, p.id DESC'
        );
        return $upit->fetchAll();
    }

    /**
     * Detalji jedne prodaje.
     */
    public function detalji(int $id): ?array
    {
        $upit = $this->pdo->prepare(
            'SELECT p.*, k.korisnicko_ime AS prodavac
             FROM prodaja p
             LEFT JOIN korisnici k ON p.kreirao = k.id
             WHERE p.id = ?'
        );
        $upit->execute([$id]);
        $red = $upit->fetch();
        return $red ?: null;
    }

    /**
     * Ponovo izračunava i upisuje ukupan iznos prodaje na osnovu njenih stavki.
     */
    public function preracunajUkupanIznos(int $id): void
    {
        $upit = $this->pdo->prepare(
            'UPDATE prodaja SET ukupan_iznos =
                (SELECT COALESCE(SUM(kolicina * cena_po_jedinici), 0) FROM stavke_prodaje WHERE prodaja_id = ?)
             WHERE id = ?'
        );
        $upit->execute([$id, $id]);
    }

    /**
     * Prodaje u zadatom periodu (uključujući granične datume).
     */
    public function zaPeriod(string $od, string $do_): array
    {
        $upit = $this->pdo->prepare(
            'SELECT p.*, k.korisnicko_ime AS prodavac
             FROM prodaja p
             LEFT JOIN korisnici k ON p.kreirao = k.id
             WHERE p.datum_prodaje BETWEEN ? AND ?
             ORDER BY p.datum_prodaje ASC, p.id ASC'
        );
        $upit->execute([$od, $do_]);
        return $upit->fetchAll();
    }

    /**
     * Ukupan promet u zadatom periodu.
     */
    public function ukupnoZaPeriod(string $od, string $do_): float
    {
        $upit = $this->pdo->prepare(
            'SELECT COALESCE(SUM(ukupan_iznos), 0) FROM prodaja WHERE datum_prodaje BETWEEN ? AND ?'
        );
        $upit->execute([$od, $do_]);
        return (float) $upit->fetchColumn();
    }

    /**
     * Ukupan promet svih prodaja (za dashboard).
     */
    public function ukupanPromet(): float
    {
        return (float) $this->pdo->query('SELECT COALESCE(SUM(ukupan_iznos), 0) FROM prodaja')->fetchColumn();
    }
}
