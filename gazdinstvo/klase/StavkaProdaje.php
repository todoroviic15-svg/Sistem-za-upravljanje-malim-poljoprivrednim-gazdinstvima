<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model jedne stavke u okviru prodaje (jedan prodati proizvod).
 */
class StavkaProdaje extends BazniModel
{
    public $prodaja_id;
    public $zaliha_id;
    public $proizvod;
    public $kolicina;
    public $cena;

    protected function popuni(array $podaci): void
    {
        $this->prodaja_id = isset($podaci['prodaja_id']) ? (int) $podaci['prodaja_id'] : null;
        $this->zaliha_id  = !empty($podaci['zaliha_id']) ? (int) $podaci['zaliha_id'] : null;
        $this->proizvod   = isset($podaci['proizvod']) ? $podaci['proizvod'] : '';
        $this->kolicina   = isset($podaci['kolicina']) ? (float) $podaci['kolicina'] : 0.0;
        $this->cena       = isset($podaci['cena']) ? (float) $podaci['cena'] : 0.0;
    }

    /**
     * Ukupna vrednost stavke (količina x cena).
     */
    public function ukupno(): float
    {
        return $this->kolicina * $this->cena;
    }
}
