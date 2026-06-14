<?php

require_once __DIR__ . '/BazniRepozitorijum.php';
require_once __DIR__ . '/Stoka.php';

/**
 * Repozitorijum za rad sa tabelom stoka.
 */
class StokaRepozitorijum extends BazniRepozitorijum
{
    protected $tabela = 'stoka';

    protected function napraviModel(array $red)
    {
        return new Stoka($red);
    }

    public function create(array $podaci)
    {
        $upit = $this->pdo->prepare(
            'INSERT INTO stoka (vrsta, rasa, broj_grla, datum_nabavke, napomena)
             VALUES (?, ?, ?, ?, ?)'
        );
        $upit->execute([
            $podaci['vrsta'],
            $podaci['rasa'],
            $podaci['broj_grla'],
            $podaci['datum_nabavke'],
            $podaci['napomena'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $podaci): bool
    {
        $upit = $this->pdo->prepare(
            'UPDATE stoka
             SET vrsta = ?, rasa = ?, broj_grla = ?, datum_nabavke = ?, napomena = ?
             WHERE id = ?'
        );
        return $upit->execute([
            $podaci['vrsta'],
            $podaci['rasa'],
            $podaci['broj_grla'],
            $podaci['datum_nabavke'],
            $podaci['napomena'],
            $id,
        ]);
    }

    /**
     * Lista stoke sortirana po vrsti.
     */
    public function sve(): array
    {
        $upit = $this->pdo->query('SELECT * FROM stoka ORDER BY vrsta ASC, rasa ASC');
        return $upit->fetchAll();
    }

    /**
     * Pretraga stoke po vrsti ili rasi.
     */
    public function pretraga(string $pojam): array
    {
        $upit = $this->pdo->prepare(
            'SELECT * FROM stoka WHERE vrsta LIKE ? OR rasa LIKE ? ORDER BY vrsta ASC'
        );
        $like = '%' . $pojam . '%';
        $upit->execute([$like, $like]);
        return $upit->fetchAll();
    }

    /**
     * Ukupan broj grla stoke na gazdinstvu.
     */
    public function ukupnoGrla(): int
    {
        return (int) $this->pdo->query('SELECT COALESCE(SUM(broj_grla), 0) FROM stoka')->fetchColumn();
    }
}
