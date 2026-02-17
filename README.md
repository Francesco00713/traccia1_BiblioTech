BiblioTech - Sistema di Gestione Biblioteca Scolastica
BiblioTech è una piattaforma web sviluppata in PHP per la gestione dei prestiti librari e l'ampliamento del patrimonio scolastico tramite donazioni degli studenti. Il sistema prevede un accesso differenziato per Studenti e Bibliotecari, garantendo sicurezza tramite autenticazione a due fattori (OTP).

1. Requisiti di Sistema
    - Web Server: Apache (consigliato Docker)
    - PHP: Versione 7.4 o superiore
    - Database: MySQL
    - Dipendenze esterne: Bootstrap 5.3.0 (caricato via CDN)

2. Struttura del Database
    Il database myapp_db è composto dalle seguenti tabelle principali:
    UTENTI: Registra le anagrafiche, le password (hashing password_hash) e lo stato di sospensione.
    LIBRI: Catalogo con titoli, generi, copie totali e copie attualmente disponibili.
    PRESTITI: Gestisce le date di inizio, fine e la segnalazione di restituzione.
    NUOVI_LIBRI: Tabella temporanea per le proposte di donazione degli studenti in attesa di approvazione.
    SESSIONI / EFFETTUA: Gestione della persistenza del login, tracciamento logout e integrità referenziale dei prestiti.

3. Avviare il server di sviluppo
    Affinché venga avviato il server di sviluppo, bisogna lanciare il comando: docker-compose up --build, direttamente dal terminale aperto dalla directory del progetto. Dopodiché tutti i container con i vari servizi verranno caricati su Docker desktop e da lì si potrà accedere a ciascuno comodamente cliccando l'apposito link.

4. Caricare i dati
    Accedere a PHPMyAdmin e importare il file database.sql presente nella cartella sql affinché l'intero database con le dovute tabelle venga creato e possa essere pronto all'utilizzo.

5. Utilizzare la piattaforma <3