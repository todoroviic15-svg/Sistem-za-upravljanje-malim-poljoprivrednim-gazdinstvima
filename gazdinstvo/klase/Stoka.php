<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model evidencije stoke na gazdinstvu.
 */
class Stoka extends BazniModel
{
    /** Vrste stoke. */
    const VRSTE = [
        'krave'    => 'Krave',
        'svinje'   => 'Svinje',
        'ovce'     => 'Ovce',
        'koze'     => 'Koze',
        'kokoske'  => 'Kokoške',
        'konji'    => 'Konji',
        'ostalo'   => 'Ostalo',
    ];

    public $vrsta;
    public $rasa;
    public $broj_grla;
    public $datum_nabavke;
    public $napomena;

    protected function popuni(array $podaci): void
    {
        $this->vrsta         = isset($podaci['vrsta']) ? $podaci['vrsta'] : '';
        $this->rasa          = isset($podaci['rasa']) ? $podaci['rasa'] : '';
        $this->broj_grla     = isset($podaci['broj_grla']) ? (int) $podaci['broj_grla'] : 0;
        $this->datum_nabavke = isset($podaci['datum_nabavke']) ? $podaci['datum_nabavke'] : '';
        $this->napomena      = isset($podaci['napomena']) ? $podaci['napomena'] : '';
    }
}
