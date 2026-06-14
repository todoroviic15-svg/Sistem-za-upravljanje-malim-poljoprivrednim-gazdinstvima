<?php

require_once __DIR__ . '/BazniModel.php';

/**
 * Model poljoprivredne parcele (zemljišta).
 */
class Zemljiste extends BazniModel
{
    /** Mogući tipovi zemljišta. */
    const TIPOVI = [
        'oranica'   => 'Oranica',
        'livada'    => 'Livada',
        'pasnjak'   => 'Pašnjak',
        'vocnjak'   => 'Voćnjak',
        'vinograd'  => 'Vinograd',
        'plastenik' => 'Plastenik',
        'ostalo'    => 'Ostalo',
    ];

    public $naziv_parcele;
    public $povrsina;
    public $tip_zemljista;
    public $lokacija;
    public $napomena;

    protected function popuni(array $podaci): void
    {
        $this->naziv_parcele = isset($podaci['naziv_parcele']) ? $podaci['naziv_parcele'] : '';
        $this->povrsina      = isset($podaci['povrsina']) ? (float) $podaci['povrsina'] : 0.0;
        $this->tip_zemljista = isset($podaci['tip_zemljista']) ? $podaci['tip_zemljista'] : 'ostalo';
        $this->lokacija      = isset($podaci['lokacija']) ? $podaci['lokacija'] : '';
        $this->napomena      = isset($podaci['napomena']) ? $podaci['napomena'] : '';
    }
}
