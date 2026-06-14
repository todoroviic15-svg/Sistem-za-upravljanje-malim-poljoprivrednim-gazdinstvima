<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/StavkaProdaje.php';

/**
 * Repozitorijum za rad sa tabelom stavke_prodaje.
 *
 * Stavke su deo prodaje (master-detail), pa se najčešće
 * pristupa preko metode zaProdaju(), ali klasa i dalje
 * implementira kompletan CrudInterface.
 */
class StavkaProdajeRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'stavke_prodaje';

    protected function napraviModel(array $red)
    {
        return new StavkaProdaje($red);
    }

   public function create(array $podaci)
{
    $upit = $this->pdo->prepare(
        'INSERT INTO stavke_prodaje
         (prodaja_id, naziv_proizvoda, kolicina, jedinica_mere, cena_po_jedinici)
         VALUES (?, ?, ?, ?, ?)'
    );

    $upit->execute([
        $podaci['prodaja_id'],
        $podaci['proizvod'],
        $podaci['kolicina'],
        'kom', // ili druga podrazumevana jedinica
        $podaci['cena'],
    ]);

    return (int)$this->pdo->lastInsertId();
}

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE stavke_prodaje SET zaliha_id = ?, proizvod = ?, kolicina = ?, cena = ? WHERE id = ?'
        );
        return $upit->execute([
            $podaci['zaliha_id'],
            $podaci['proizvod'],
            $podaci['kolicina'],
            $podaci['cena'],
            $id,
        ]);
    }

    /**
     * Sve stavke jedne prodaje.
     */
    public function zaProdaju(int $prodajaId): array
    {
        $upit = $this->pdo->prepare('SELECT * FROM stavke_prodaje WHERE prodaja_id = ? ORDER BY id ASC');
        $upit->execute([$prodajaId]);
        return $upit->fetchAll();
    }

    /**
     * Najprodavaniji proizvodi - ukupna prodata količina i ukupan promet po proizvodu.
     */
    public function najprodavanijiProizvodi(): array
{
    $upit = $this->pdo->query(
        'SELECT naziv_proizvoda,
                SUM(kolicina) AS ukupna_kolicina,
                SUM(kolicina * cena_po_jedinici) AS ukupan_promet,
                COUNT(*) AS broj_prodaja
         FROM stavke_prodaje
         GROUP BY naziv_proizvoda
         ORDER BY ukupna_kolicina DESC'
    );

    return $upit->fetchAll();
}
}
