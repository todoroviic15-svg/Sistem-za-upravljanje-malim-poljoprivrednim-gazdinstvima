<?php

/**
 * Apstraktna bazna klasa za sve modele (entitete).
 *
 * Svaki model dobija ID i konstruktor koji prima asocijativni niz
 * (red iz baze), a klase naslednice u popuni() mapiraju svoja polja.
 */
abstract class BazniModel
{
    /** @var int|null */
    public $id;

    public function __construct(array $podaci = [])
    {
        $this->id = isset($podaci['id']) ? (int) $podaci['id'] : null;
        $this->popuni($podaci);
    }

    /**
     * Mapira polja iz reda baze u atribute modela.
     */
    abstract protected function popuni(array $podaci): void;
}
