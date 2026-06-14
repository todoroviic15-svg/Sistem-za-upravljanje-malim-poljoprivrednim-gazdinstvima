<?php

/**
 * Klasa za upravljanje sesijama.
 *
 * Obavija rad sa $_SESSION nizom: pokretanje sesije, čitanje i upis
 * vrednosti, uništavanje sesije i CSRF zaštitu formi.
 */
class Sesija
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function postavi(string $kljuc, $vrednost): void
    {
        $_SESSION[$kljuc] = $vrednost;
    }

    public function uzmi(string $kljuc, $podrazumevano = null)
    {
        return isset($_SESSION[$kljuc]) ? $_SESSION[$kljuc] : $podrazumevano;
    }

    public function postoji(string $kljuc): bool
    {
        return isset($_SESSION[$kljuc]);
    }

    public function ukloni(string $kljuc): void
    {
        unset($_SESSION[$kljuc]);
    }

    /**
     * Uništava kompletnu sesiju (odjava korisnika).
     */
    public function unisti(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Vraća CSRF token sesije (generiše ga pri prvom pozivu).
     */
    public function csrfToken(): string
    {
        if (!$this->postoji('csrf_token')) {
            $this->postavi('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->uzmi('csrf_token');
    }

    /**
     * Proverava da li CSRF token iz forme odgovara tokenu iz sesije.
     */
    public function proveriCsrf(?string $token): bool
    {
        return $token !== null
            && $this->postoji('csrf_token')
            && hash_equals($this->uzmi('csrf_token'), $token);
    }
}
