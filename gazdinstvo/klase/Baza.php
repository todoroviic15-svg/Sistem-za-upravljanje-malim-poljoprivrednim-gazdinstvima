<?php

/**
 * Klasa za upravljanje konekcijom na bazu podataka (singleton).
 *
 * Cela aplikacija deli jednu PDO konekciju koja se dobija
 * pozivom Baza::konekcija().
 */
class Baza
{
    /** @var Baza|null Jedina instanca klase */
    private static $instanca = null;

    /** @var PDO */
    private $pdo;

    private function __construct(array $podesavanja)
    {
        $dsn = "mysql:host={$podesavanja['host']};dbname={$podesavanja['baza']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $podesavanja['korisnik'], $podesavanja['lozinka'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /**
     * Vraća deljenu PDO konekciju (pravi je pri prvom pozivu).
     */
    public static function konekcija(): PDO
    {
        if (self::$instanca === null) {
            $podesavanja = require __DIR__ . '/../config.php';
            self::$instanca = new Baza($podesavanja);
        }
        return self::$instanca->pdo;
    }
}
