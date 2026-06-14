<?php

require_once __DIR__ . '/CrudInterface.php';
require_once __DIR__ . '/Baza.php';

/**
 * Apstraktna bazna klasa za sve repozitorijume.
 *
 * Implementira CrudInterface i obezbeđuje zajedničku funkcionalnost
 * (PDO konekcija, generičko čitanje, brisanje i brojanje redova) koju
 * sve klase naslednice dele. Ovim se demonstrira nasleđivanje (extends)
 * uz implementaciju interfejsa (implements).
 */
abstract class BazniRepozitorijum implements CrudInterface
{
    /** @var PDO */
    protected $pdo;

    /** @var string Naziv tabele u bazi (postavlja klasa naslednica) */
    protected $tabela;

    public function __construct()
    {
        $this->pdo = Baza::konekcija();
    }

    /**
     * Generičko čitanje jednog reda po ID-u.
     * Klasa naslednica preko napraviModel() određuje koji se objekat vraća.
     */
    public function read(int $id)
    {
        $upit = $this->pdo->prepare("SELECT * FROM {$this->tabela} WHERE id = ?");
        $upit->execute([$id]);
        $red = $upit->fetch();
        return $red ? $this->napraviModel($red) : null;
    }

    /**
     * Generičko brisanje reda po ID-u.
     */
    public function delete(int $id): bool
    {
        $upit = $this->pdo->prepare("DELETE FROM {$this->tabela} WHERE id = ?");
        return $upit->execute([$id]);
    }

    /**
     * Generičko listanje svih redova (naslednice mogu da pregaze
     * ovu metodu kada im je potreban JOIN sa drugim tabelama).
     */
    public function sve(): array
    {
        $upit = $this->pdo->query("SELECT * FROM {$this->tabela} ORDER BY id DESC");
        return $upit->fetchAll();
    }

    /**
     * Vraća ukupan broj redova u tabeli (za statistiku na početnoj strani).
     */
    public function prebroj(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->tabela}")->fetchColumn();
    }

    /**
     * Svaka klasa naslednica definiše kako se red iz baze
     * pretvara u odgovarajući model objekat.
     */
    abstract protected function napraviModel(array $red);
}
