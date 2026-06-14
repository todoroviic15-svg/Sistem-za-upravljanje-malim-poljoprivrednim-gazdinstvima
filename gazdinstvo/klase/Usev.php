<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model useva posejanog na određenoj parceli.
 */
class Usev extends BazniModel
{
    /** Kategorije useva. */
    const KATEGORIJE = [
        'zitarice'    => 'Žitarice',
        'povrce'      => 'Povrće',
        'voce'        => 'Voće',
        'industrijski' => 'Industrijski usevi',
        'krmno_bilje' => 'Krmno bilje',
        'ostalo'      => 'Ostalo',
    ];

    public $naziv_useva;
    public $kategorija;
    public $datum_setve;
    public $ocekivana_berba;
    public $zemljiste_id;

    protected function popuni(array $podaci): void
    {
        $this->naziv_useva     = isset($podaci['naziv_useva']) ? $podaci['naziv_useva'] : '';
        $this->kategorija      = isset($podaci['kategorija']) ? $podaci['kategorija'] : 'ostalo';
        $this->datum_setve     = isset($podaci['datum_setve']) ? $podaci['datum_setve'] : '';
        $this->ocekivana_berba = isset($podaci['ocekivana_berba']) ? $podaci['ocekivana_berba'] : '';
        $this->zemljiste_id    = !empty($podaci['zemljiste_id']) ? (int) $podaci['zemljiste_id'] : null;
    }
}
