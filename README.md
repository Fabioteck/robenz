# RovigoCarburanti ⛽

Web app Laravel per visualizzare i prezzi dei carburanti nella provincia di Rovigo (e Veneto),
ordinati per prezzo, con dati aggiornati quotidianamente dagli Open Data MIMIT.

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

### ✅ MVP (questo repo)
- [x] Import CSV MIMIT con filtro provincia
- [x] Modelli Station + Price
- [x] Lista distributori ordinata per prezzo
- [x] Filtro per comune e tipo carburante
- [x] Toggle Self / Servito
- [x] Semaforo freschezza dati
- [x] Badge "più economico"
- [x] Pagina dettaglio con link Google Maps
- [x] Sync automatico (Scheduler Laravel)
- [x] Cache query 30 minuti

### 🔵 v1
- [ ] UI con Tailwind compilato (Vite + npm)
- [ ] Geolocalizzazione browser → distributore più vicino
- [ ] Paginazione / lazy load lista
- [ ] Meta tag SEO per ogni comune

### 🟣 v2
- [ ] Storico prezzi (grafico settimanale)
- [ ] PWA installabile su mobile
- [ ] Alert prezzo via email
- [ ] Estensione a tutto il Veneto

---

## Note sui dati

I prezzi vengono aggiornati ogni mattina dal MIMIT con i dati comunicati dai gestori entro le 8:00
del giorno precedente. Non tutti i gestori aggiornano con regolarità: il **semaforo** indica
la freschezza dell'ultimo dato ricevuto.

Licenza dati: **IODL 2.0** (Italian Open Data License)
