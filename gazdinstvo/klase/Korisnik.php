<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model registrovanog korisnika aplikacije.
 */
class Korisnik extends BazniModel
{
    /** @var string */
    public $korisnicko_ime;

    /** @var string */
    public $email;

    protected function popuni(array $podaci): void
    {
        $this->korisnicko_ime = isset($podaci['korisnicko_ime']) ? $podaci['korisnicko_ime'] : '';
        $this->email          = isset($podaci['email']) ? $podaci['email'] : '';
    }
}
