# ⛽ RoBenz v1.0 - Monitoraggio Prezzi Carburanti (Rovigo & Provincia)

RoBenz è una Web Application basata su **Laravel 13**, **PHP 8.4** e **Tailwind CSS v4** (compilato staticamente tramite **Vite**). Il portale è ottimizzato per la produzione e monitora in tempo reale i prezzi praticati dai distributori di carburante in tutta la provincia di Rovigo.
Dati aggiornati quotidianamente dagli Open Data MIMIT.

## 🚀 Funzionalità Implementate (v1.0 Stabile)

- **Filtri di Ricerca Avanzati (Index):** Sistema di filtraggio asincrono e dinamico basato su **Alpine.js**. Permette di segmentare i distributori per tipo di carburante (*Benzina, Gasolio, GPL, Metano*), per Comune della provincia di Rovigo e per tipo di erogazione (*Self / Servito*).
- **Algoritmo di Convenienza:** La lista ordina automaticamente i distributori dal più economico al più caro. Il distributore in prima posizione riceve l'evidenziazione estetica e il badge speciale `🏆 Più economico`.
- **Scheda di Dettaglio Geolocalizzata (Show):** Ogni distributore ha una pagina dedicata che mostra i listini prezzi completi per ogni tipo di erogazione e include un modulo cartografico **Leaflet.js** locale (senza dipendenze da chiavi API esterne), centrato e zoomato in close-up sulla posizione esatta della stazione.
- **Semaforo di Freschezza Dati:** Un indicatore visivo dinamico a tre colori (*Verde, Giallo, Rosso*) mostra lo stato di aggiornamento dei prezzi (ultime 24h, ieri, o più di 2 giorni).

## 🛠️ Architettura Tecnica & Ottimizzazioni di Produzione

- **Ottimizzazione Livello Dati:** Architettura basata su database **SQLite** protetta da un doppio strato di **Cache (Laravel Cache System)** con scadenze differenziate (30 minuti per le stazioni filtrate, 24 ore per l'elenco comuni) per minimizzare le interrogazioni al disco. I dati Eloquent sono normalizzati in array nativi in cache per prevenire errori di serializzazione degli oggetti.
- **Front-End Performance:** Migrazione completa dal vecchio Play CDN al compilatore statico **Vite + Tailwind CSS v4**. La palette del brand è mappata nativamente nel tema CSS.
- **Server Web & Sicurezza:** Servito tramite **Apache** su ambiente Ubuntu, configurato con puntamento rigido sulla sotto-cartella `/public` e modulo `mod_rewrite` attivo. Sicurezza crittografica HTTPS gestita tramite certificato SSL **Let's Encrypt (Certbot)** con rinnovo automatico.
- **Permessi del File System:** Struttura dei permessi allineata (`775` su `storage` e `bootstrap/cache`) per garantire la corretta compilazione delle viste Blade sotto l'utente `www-data`.

## 💻 Comandi Utili per la Manutenzione

In caso di aggiornamenti del codice o dei fogli di stile in produzione, eseguire:

```bash
# Compilazione statica degli asset (Tailwind / Vite)
npm run build

# Svuotamento e rigenerazione delle cache di Laravel
php artisan view:clear
php artisan config:cache
php artisan route:cache
```


---

## Stack tecnico

| Layer      | Tecnologia                        |
|------------|-----------------------------------|
| Backend    | PHP 8.2+, Laravel 11              |
| Frontend   | Blade, Tailwind CSS (CDN)         |
| Database   | SQLite (default), migrazione MySQL facile |
| Dati       | MIMIT Open Data CSV (pipe-separated) |
| Deploy     | Qualsiasi server PHP / Laravel Forge / Shared hosting |

---

## Setup rapido

```bash
# 1. Crea progetto Laravel
composer create-project laravel/laravel rovigocarburanti
cd rovigocarburanti

# 2. Copia i file di questo repo nelle cartelle corrispondenti

# 3. Configura .env
cp .env.example .env
php artisan key:generate

# SQLite (default, zero configurazione)
touch database/database.sqlite
# Nel .env: DB_CONNECTION=sqlite

# 4. Migra il database
php artisan migrate

# 5. Primo import dati (filtra provincia RO)
php artisan fuel:sync --province=RO

# 6. Avvia il server
php artisan serve
```

---

## Comandi Artisan

```bash
# Sync provincia Rovigo
php artisan fuel:sync --province=RO

# Sync più province (Veneto completo)
php artisan fuel:sync --province=RO,VE,PD,VR,VI,TV,BL

# Forza re-sync anche se già eseguito oggi
php artisan fuel:sync --province=RO --force
```

---

## Cron automatico (crontab server)

Aggiungi questa riga al crontab del server:

```cron
* * * * * cd /path/to/rovigocarburanti && php artisan schedule:run >> /dev/null 2>&1
```

Il sync avviene automaticamente ogni giorno alle **09:15**.

---

## Struttura CSV MIMIT

### anagrafica_impianti_attivi.csv
```
idImpianto|Gestore|Bandiera|Tipo Impianto|Nome Impianto|Indirizzo|Comune|Provincia|Latitudine|Longitudine
```

### prezzo_alle_8.csv
```
idImpianto|descCarburante|prezzo|isSelf|dtComu
```

Delimitatore: `|` (pipe)
Encoding: UTF-8

---

## Roadmap

### ✅ MVP - Version 1 (questo repo)
- [x] Import CSV MIMIT con filtro provincia
- [x] Modelli Station + Price + Mappa
- [x] Lista distributori ordinata per prezzo 
- [x] Filtro per comune e tipo carburante
- [x] Toggle Self / Servito
- [x] Semaforo freschezza dati
- [x] Badge "più economico"
- [x] Pagina dettaglio con link Google Maps
- [x] Sync automatico (Scheduler Laravel)
- [x] Cache query 30 minuti

---

## Note sui dati

I prezzi vengono aggiornati ogni mattina dal MIMIT con i dati comunicati dai gestori entro le 8:00
del giorno precedente. Non tutti i gestori aggiornano con regolarità: il **semaforo** indica
la freschezza dell'ultimo dato ricevuto.

Licenza dati: **IODL 2.0** (Italian Open Data License)
