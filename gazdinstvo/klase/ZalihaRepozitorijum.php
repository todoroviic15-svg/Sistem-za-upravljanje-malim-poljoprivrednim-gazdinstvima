<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/Zaliha.php';

/**
 * Repozitorijum za rad sa tabelom zalihe (skladište).
 */
class ZalihaRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'zalihe';

    protected function napraviModel(array $red)
    {
        return new Zaliha($red);
    }

    public function create(array $podaci)
    {
        $upit = $this->pdo->prepare(
            'INSERT INTO zalihe (naziv_proizvoda, kolicina, jedinica_mere, datum_ulaza, datum_isteka)
             VALUES (?, ?, ?, ?, ?)'
        );
        $upit->execute([
            $podaci['naziv_proizvoda'],
            $podaci['kolicina'],
            $podaci['jedinica_mere'],
            $podaci['datum_ulaza'],
            $podaci['datum_isteka'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE zalihe
             SET naziv_proizvoda = ?, kolicina = ?, jedinica_mere = ?, datum_ulaza = ?, datum_isteka = ?
             WHERE id = ?'
        );
        return $upit->execute([
            $podaci['naziv_proizvoda'],
            $podaci['kolicina'],
            $podaci['jedinica_mere'],
            $podaci['datum_ulaza'],
            $podaci['datum_isteka'],
            $id,
        ]);
    }

    /**
     * Lista svih zaliha sortirana po nazivu proizvoda.
     */
    public function sve(): array
    {
        $upit = $this->pdo->query('SELECT * FROM zalihe ORDER BY naziv_proizvoda ASC');
        return $upit->fetchAll();
    }

    /**
     * Pretraga zaliha po nazivu proizvoda.
     */
    public function pretraga(string $pojam): array
    {
        $upit = $this->pdo->prepare('SELECT * FROM zalihe WHERE naziv_proizvoda LIKE ? ORDER BY naziv_proizvoda ASC');
        $upit->execute(['%' . $pojam . '%']);
        return $upit->fetchAll();
    }

    /**
     * Smanjuje količinu zalihe za prodatu robu (koristi se prilikom prodaje).
     */
    public function smanjiKolicinu(int $id, float $kolicina): bool
    {
        $upit = $this->pdo->prepare('UPDATE zalihe SET kolicina = GREATEST(kolicina - ?, 0) WHERE id = ?');
        return $upit->execute([$kolicina, $id]);
    }

    /**
     * Broj stavki sa istekom roka u narednih 7 dana (za dashboard).
     */
    public function brojSaIstekomUskoro(): int
    {
        $upit = $this->pdo->query(
            "SELECT COUNT(*) FROM zalihe
             WHERE datum_isteka IS NOT NULL
               AND datum_isteka >= CURDATE()
               AND datum_isteka <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        return (int) $upit->fetchColumn();
    }
}
