# Upravljanje malim gazdinstvima

Aplikacija baza podataka za upravljanje malim poljoprivrednim gazdinstvima — vođenje evidencije o **zemljištu, usevima, stoci, zalihama i prodaji proizvoda**.

Projektni zadatak iz predmeta *Projektovanje aplikacija baza podataka*.

## Korišćene tehnologije

- HTML, CSS, **Bootstrap 5**
- **PHP** (objektno-orijentisano programiranje)
- **MySQL** (PDO konekcija sa pripremljenim upitima)

## Pokretanje (XAMPP)

1. Kopirati folder `gazdinstvo` u `C:\xampp\htdocs\`.
2. Pokrenuti **Apache** i **MySQL** u XAMPP kontrolnom panelu.
3. U phpMyAdmin-u importovati fajl `baza/baza.sql`.
4. Po potrebi izmeniti pristupne podatke u `config.php`.
5. Otvoriti `http://localhost/gazdinstvo/` u pregledaču.
6. Registrovati nalog preko forme **Registracija**, a zatim se prijaviti.

## Struktura projekta

```text
gazdinstvo/
├── README.md
├── config.php
├── index.php
├── prijava.php
├── registracija.php
├── odjava.php
├── zaglavlje.php / podnozje.php
├── zemljiste.php / zemljiste_izmena.php
├── usevi.php / usev_izmena.php
├── stoka.php / stoka_izmena.php
├── zalihe.php / zaliha_izmena.php
├── prodaja.php / prodaja_izmena.php / prodaja_detalji.php
├── izvestaji.php
├── baza/
│   └── baza.sql
├── css/
│   └── stil.css
└── klase/
    ├── Baza.php
    ├── CrudInterface.php
    ├── BazniRepozitorijum.php
    ├── BazniModel.php
    ├── Sesija.php
    ├── Autentifikacija.php
    ├── Korisnik.php
    ├── Zemljiste.php
    ├── Usev.php
    ├── Stoka.php
    ├── Zaliha.php
    ├── Prodaja.php
    ├── StavkaProdaje.php
    └── *Repozitorijum.php
```

## Ispunjenost zahteva projektnog zadatka

1. **Bootstrap interfejs** — aplikacija koristi Bootstrap komponente za navigaciju, tabele, kartice i forme.
2. **Prijava i registracija korisnika** — implementirana autentifikacija kroz klase `Autentifikacija` i `Sesija`.
3. **Baza podataka** — sistem koristi tabele:
   - korisnici
   - zemljiste
   - usevi
   - stoka
   - zalihe
   - prodaja
   - stavke_prodaje
4. **CRUD operacije** — omogućeno dodavanje, pregled, izmena i brisanje svih glavnih entiteta.
5. **OOP principi** — korišćeni interfejsi, apstraktne klase, nasleđivanje i repozitorijumski obrazac.
6. **Bezbednost** — PDO pripremljeni upiti, heširanje lozinki i zaštita korisničkih podataka.

## Dijagram nasleđivanja

```text
CrudInterface (create, read, update, delete, sve)
        ▲ implements
BazniRepozitorijum (apstraktna klasa)
        ▲ extends
ZemljisteRepozitorijum
UsevRepozitorijum
StokaRepozitorijum
ZalihaRepozitorijum
ProdajaRepozitorijum
StavkaProdajeRepozitorijum

BazniModel (apstraktna klasa)
        ▲ extends
Korisnik
Zemljiste
Usev
Stoka
Zaliha
Prodaja
StavkaProdaje
```

## Funkcionalnosti sistema

- Upravljanje parcelama i zemljištem
- Evidencija useva i planiranje setve/berbe
- Upravljanje stočnim fondom
- Praćenje zaliha i skladišta
- Evidencija prodaje proizvoda
- Generisanje izveštaja
- Upravljanje korisničkim nalozima
