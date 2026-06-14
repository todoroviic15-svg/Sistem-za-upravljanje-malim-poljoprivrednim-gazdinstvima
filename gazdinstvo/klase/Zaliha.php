<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model stavke zaliha (skladišta) gazdinstva.
 */
class Zaliha extends BazniModel
{
    /** Jedinice mere. */
    const JEDINICE = [
        'kg'  => 'kg',
        'l'   => 'l',
        'kom' => 'kom',
        't'   => 't',
        'm3'  => 'm³',
    ];

    public $naziv_proizvoda;
    public $kolicina;
    public $jedinica_mere;
    public $datum_ulaza;
    public $datum_isteka;

    protected function popuni(array $podaci): void
    {
        $this->naziv_proizvoda = isset($podaci['naziv_proizvoda']) ? $podaci['naziv_proizvoda'] : '';
        $this->kolicina        = isset($podaci['kolicina']) ? (float) $podaci['kolicina'] : 0.0;
        $this->jedinica_mere   = isset($podaci['jedinica_mere']) ? $podaci['jedinica_mere'] : 'kg';
        $this->datum_ulaza     = isset($podaci['datum_ulaza']) ? $podaci['datum_ulaza'] : '';
        $this->datum_isteka    = isset($podaci['datum_isteka']) ? $podaci['datum_isteka'] : '';
    }

    /**
     * Da li je rok trajanja ove zalihe istekao.
     */
    public function jeIstekla(): bool
    {
        return !empty($this->datum_isteka) && strtotime($this->datum_isteka) < strtotime(date('Y-m-d'));
    }

    /**
     * Da li rok trajanja ističe u narednih 7 dana.
     */
    public function isticeUskoro(): bool
    {
        if (empty($this->datum_isteka)) {
            return false;
        }
        $razlika = (strtotime($this->datum_isteka) - strtotime(date('Y-m-d'))) / 86400;
        return $razlika >= 0 && $razlika <= 7;
    }
}
