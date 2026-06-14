<?php

require_once __DIR__ . '/Baza.php';
require_once __DIR__ . '/Sesija.php';
require_once __DIR__ . '/Korisnik.php';

/**
 * Klasa za upravljanje korisnicima: registracija, prijava i odjava.
 *
 * Koristi klasu Sesija za čuvanje podataka o prijavljenom korisniku
 * i klasu Baza za pristup tabeli korisnika.
 */
class Autentifikacija
{
    /** @var PDO */
    private $pdo;

    /** @var Sesija */
    private $sesija;

    public function __construct()
    {
        $this->pdo    = Baza::konekcija();
        $this->sesija = new Sesija();
    }

    public function sesija(): Sesija
    {
        return $this->sesija;
    }

    /**
     * Registruje novog korisnika.
     * Vraća listu grešaka — prazna lista znači da je registracija uspela.
     */
    public function registruj(string $korisnickoIme, string $email, string $lozinka, string $potvrda): array
    {
        $greske = [];

        if (mb_strlen($korisnickoIme) < 3) {
            $greske[] = 'Korisničko ime mora imati najmanje 3 karaktera.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $greske[] = 'Email adresa nije ispravna.';
        }
        if (mb_strlen($lozinka) < 6) {
            $greske[] = 'Lozinka mora imati najmanje 6 karaktera.';
        }
        if ($lozinka !== $potvrda) {
            $greske[] = 'Lozinke se ne poklapaju.';
        }

        if (empty($greske)) {
            $upit = $this->pdo->prepare('SELECT id FROM korisnici WHERE korisnicko_ime = ? OR email = ? LIMIT 1');
            $upit->execute([$korisnickoIme, $email]);
            if ($upit->fetch()) {
                $greske[] = 'Korisničko ime ili email adresa su već zauzeti.';
            }
        }

        if (empty($greske)) {
            $hes  = password_hash($lozinka, PASSWORD_DEFAULT);
            $upit = $this->pdo->prepare('INSERT INTO korisnici (korisnicko_ime, email, lozinka) VALUES (?, ?, ?)');
            $upit->execute([$korisnickoIme, $email, $hes]);
        }

        return $greske;
    }

    /**
     * Prijavljuje korisnika po korisničkom imenu ili email adresi.
     */
    public function prijavi(string $imeIliEmail, string $lozinka): bool
    {
        $upit = $this->pdo->prepare('SELECT * FROM korisnici WHERE korisnicko_ime = ? OR email = ? LIMIT 1');
        $upit->execute([$imeIliEmail, $imeIliEmail]);
        $red = $upit->fetch();

        if ($red && password_verify($lozinka, $red['lozinka'])) {
            $this->sesija->postavi('korisnik_id', (int) $red['id']);
            $this->sesija->postavi('korisnicko_ime', $red['korisnicko_ime']);
            return true;
        }
        return false;
    }

    /**
     * Odjavljuje korisnika i uništava sesiju.
     */
    public function odjavi(): void
    {
        $this->sesija->unisti();
    }

    public function jePrijavljen(): bool
    {
        return $this->sesija->postoji('korisnik_id');
    }

    /**
     * Vraća model trenutno prijavljenog korisnika (ili null).
     */
    public function trenutniKorisnik(): ?Korisnik
    {
        if (!$this->jePrijavljen()) {
            return null;
        }
        return new Korisnik([
            'id'             => $this->sesija->uzmi('korisnik_id'),
            'korisnicko_ime' => $this->sesija->uzmi('korisnicko_ime'),
        ]);
    }
}
