<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model prodaje proizvoda sa gazdinstva.
 */
class Prodaja extends BazniModel
{
    public $datum_prodaje;
    public $kupac;
    public $ukupan_iznos;
    public $kreirao;

    protected function popuni(array $podaci): void
    {
        $this->datum_prodaje = isset($podaci['datum_prodaje']) ? $podaci['datum_prodaje'] : '';
        $this->kupac         = isset($podaci['kupac']) ? $podaci['kupac'] : '';
        $this->ukupan_iznos  = isset($podaci['ukupan_iznos']) ? (float) $podaci['ukupan_iznos'] : 0.0;
        $this->kreirao       = !empty($podaci['kreirao']) ? (int) $podaci['kreirao'] : null;
    }
}
