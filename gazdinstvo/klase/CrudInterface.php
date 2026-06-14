<?php

/**
 * Interfejs koji propisuje CRUD operacije nad bazom podataka.
 *
 * Svaki repozitorijum u aplikaciji mora da implementira ove metode
 * (create, read, update, delete + listanje svih redova).
 */
interface CrudInterface
{
    /**
     * Upisuje novi red u bazu i vraća ID novog reda.
     */
    public function create(array $podaci);

    /**
     * Čita jedan red po ID-u i vraća odgovarajući model objekat (ili null).
     */
    public function read(int $id);

    /**
     * Osvežava postojeći red novim podacima.
     */
    public function update(int $id, array $podaci): bool;

    /**
     * Briše red po ID-u.
     */
    public function delete(int $id): bool;

    /**
     * Vraća listu svih redova iz tabele.
     */
    public function sve(): array;
}
