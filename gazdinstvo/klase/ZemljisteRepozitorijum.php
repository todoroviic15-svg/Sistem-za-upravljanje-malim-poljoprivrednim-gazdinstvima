<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/Zemljiste.php';

/**
 * Repozitorijum za rad sa tabelom zemljiste (parcele).
 */
class ZemljisteRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'zemljiste';

    protected function napraviModel(array $red)
    {
        return new Zemljiste($red);
    }

    public function create(array $podaci)
    {
        $upit = $this->pdo->prepare(
            'INSERT INTO zemljiste (naziv_parcele, povrsina, tip_zemljista, lokacija, napomena)
             VALUES (?, ?, ?, ?, ?)'
        );
        $upit->execute([
            $podaci['naziv_parcele'],
            $podaci['povrsina'],
            $podaci['tip_zemljista'],
            $podaci['lokacija'],
            $podaci['napomena'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE zemljiste
             SET naziv_parcele = ?, povrsina = ?, tip_zemljista = ?, lokacija = ?, napomena = ?
             WHERE id = ?'
        );
        return $upit->execute([
            $podaci['naziv_parcele'],
            $podaci['povrsina'],
            $podaci['tip_zemljista'],
            $podaci['lokacija'],
            $podaci['napomena'],
            $id,
        ]);
    }

    /**
     * Lista svih parcela sortirana po nazivu.
     */
    public function sve(): array
    {
        $upit = $this->pdo->query('SELECT * FROM zemljiste ORDER BY naziv_parcele ASC');
        return $upit->fetchAll();
    }

    /**
     * Pretraga parcela po nazivu, lokaciji ili tipu zemljišta.
     */
    public function pretraga(string $pojam): array
    {
        $upit = $this->pdo->prepare(
            'SELECT * FROM zemljiste
             WHERE naziv_parcele LIKE ? OR lokacija LIKE ? OR tip_zemljista LIKE ?
             ORDER BY naziv_parcele ASC'
        );
        $like = '%' . $pojam . '%';
        $upit->execute([$like, $like, $like]);
        return $upit->fetchAll();
    }

    /**
     * Ukupna površina svih parcela (u hektarima).
     */
    public function ukupnaPovrsina(): float
    {
        return (float) $this->pdo->query('SELECT COALESCE(SUM(povrsina), 0) FROM zemljiste')->fetchColumn();
    }
}
