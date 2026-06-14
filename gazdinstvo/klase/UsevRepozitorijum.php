<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/Usev.php';

/**
 * Repozitorijum za rad sa tabelom usevi.
 */
class UsevRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'usevi';

    protected function napraviModel(array $red)
    {
        return new Usev($red);
    }

    public function create(array $podaci)
    {
        $upit = $this->pdo->prepare(
            'INSERT INTO usevi (naziv_useva, kategorija, datum_setve, ocekivana_berba, zemljiste_id)
             VALUES (?, ?, ?, ?, ?)'
        );
        $upit->execute([
            $podaci['naziv_useva'],
            $podaci['kategorija'],
            $podaci['datum_setve'],
            $podaci['ocekivana_berba'],
            $podaci['zemljiste_id'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE usevi
             SET naziv_useva = ?, kategorija = ?, datum_setve = ?, ocekivana_berba = ?, zemljiste_id = ?
             WHERE id = ?'
        );
        return $upit->execute([
            $podaci['naziv_useva'],
            $podaci['kategorija'],
            $podaci['datum_setve'],
            $podaci['ocekivana_berba'],
            $podaci['zemljiste_id'],
            $id,
        ]);
    }

    /**
     * Lista svih useva sa nazivom parcele na kojoj su posejani.
     */
    public function sve(): array
    {
        $upit = $this->pdo->query(
            'SELECT u.*, z.naziv_parcele, z.lokacija
             FROM usevi u
             LEFT JOIN zemljiste z ON u.zemljiste_id = z.id
             ORDER BY u.datum_setve DESC'
        );
        return $upit->fetchAll();
    }

    /**
     * Svi usevi posejani na jednoj parceli.
     */
    public function zaParcelu(int $zemljisteId): array
    {
        $upit = $this->pdo->prepare(
            'SELECT * FROM usevi WHERE zemljiste_id = ? ORDER BY datum_setve DESC'
        );
        $upit->execute([$zemljisteId]);
        return $upit->fetchAll();
    }

    /**
     * Pregled useva po parcelama (za izveštaj) - svaki red je usev sa podacima o parceli.
     */
    public function pregledPoParcelama(): array
    {
        $upit = $this->pdo->query(
            'SELECT z.naziv_parcele, z.lokacija, z.povrsina, z.tip_zemljista,
                    u.naziv_useva, u.kategorija, u.datum_setve, u.ocekivana_berba
             FROM zemljiste z
             LEFT JOIN usevi u ON u.zemljiste_id = z.id
             ORDER BY z.naziv_parcele ASC, u.datum_setve DESC'
        );
        return $upit->fetchAll();
    }
}
