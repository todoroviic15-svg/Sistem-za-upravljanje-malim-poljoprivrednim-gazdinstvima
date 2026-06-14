-- ============================================================
-- Baza podataka: Sistem za upravljanje malim gazdinstvima
-- Importovati u phpMyAdmin ili pokrenuti: mysql -u root < baza.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS gazdinstvo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gazdinstvo;

CREATE TABLE IF NOT EXISTS korisnici (
  id INT AUTO_INCREMENT PRIMARY KEY,
  korisnicko_ime VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  lozinka VARCHAR(255) NOT NULL,
  datum_registracije TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS zemljiste (
  id INT AUTO_INCREMENT PRIMARY KEY,
  naziv_parcele VARCHAR(120) NOT NULL,
  povrsina DECIMAL(10,4) NOT NULL DEFAULT 0,
  tip_zemljista ENUM('njiva','vocnjak','livada','pasnjak','staklenika','ostalo') NOT NULL DEFAULT 'njiva',
  lokacija VARCHAR(150),
  napomena TEXT,
  kreirao INT,
  datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kreirao) REFERENCES korisnici(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usevi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  naziv_useva VARCHAR(120) NOT NULL,
  kategorija ENUM('zitarice','povrtarstvo','vocarstvo','industrijsko','krmno','ostalo') NOT NULL DEFAULT 'zitarice',
  datum_setve DATE,
  ocekivana_berba DATE,
  zemljiste_id INT,
  napomena TEXT,
  kreirao INT,
  datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zemljiste_id) REFERENCES zemljiste(id) ON DELETE SET NULL,
  FOREIGN KEY (kreirao) REFERENCES korisnici(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stoka (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vrsta VARCHAR(80) NOT NULL,
  rasa VARCHAR(80),
  broj_grla INT NOT NULL DEFAULT 1,
  datum_nabavke DATE,
  napomena TEXT,
  kreirao INT,
  datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kreirao) REFERENCES korisnici(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS zalihe (
  id INT AUTO_INCREMENT PRIMARY KEY,
  naziv_proizvoda VARCHAR(150) NOT NULL,
  kategorija ENUM('seme','djubrivo','hemija','hrana_za_stoku','gotov_proizvod','alat','ostalo') NOT NULL DEFAULT 'gotov_proizvod',
  kolicina DECIMAL(12,3) NOT NULL DEFAULT 0,
  jedinica_mere VARCHAR(20) NOT NULL DEFAULT 'kg',
  datum_ulaza DATE NOT NULL,
  datum_isteka DATE,
  cena_po_jedinici DECIMAL(10,2),
  napomena TEXT,
  kreirao INT,
  datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kreirao) REFERENCES korisnici(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prodaja (
  id INT AUTO_INCREMENT PRIMARY KEY,
  datum_prodaje DATE NOT NULL,
  kupac VARCHAR(150) NOT NULL,
  ukupan_iznos DECIMAL(12,2) NOT NULL DEFAULT 0,
  napomena TEXT,
  kreirao INT,
  datum_kreiranja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kreirao) REFERENCES korisnici(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stavke_prodaje (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prodaja_id INT NOT NULL,
  naziv_proizvoda VARCHAR(150) NOT NULL,
  kolicina DECIMAL(12,3) NOT NULL DEFAULT 1,
  jedinica_mere VARCHAR(20) NOT NULL DEFAULT 'kg',
  cena_po_jedinici DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (prodaja_id) REFERENCES prodaja(id) ON DELETE CASCADE
) ENGINE=InnoDB;
