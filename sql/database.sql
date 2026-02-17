USE myapp_db;

CREATE TABLE IF NOT EXISTS Utenti (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    passwordH VARCHAR(255), 
    ruolo VARCHAR(20),
    sospensione DATE
);

INSERT INTO Utenti (userId, nome, email, passwordH, ruolo, sospensione) VALUES 
(1, 'Francesco', 'francesco@panettipitagora.edu.it', '$2y$10$uzAb.qqRjpZ8tdjOoIFJCe5ybpMauWv5OyOPrCdlV7LHMAxoo7aWe', 'BIBLIOTECARIO', NULL),
(2, 'Giuseppe', 'giuseppe@panettipitagora.edu.it', '$2y$10$GBxhQgwz1CcOPchjuQk2rOVdvbXmU5eX5kTdGZ634v4m.5GT5uRb6', 'STUDENTE', NULL),
(3, 'Antonio', 'antonio@panettipitagora.edu.it', '$2y$10$Ac.7B.60ZP3f2eYLkqKZluLBHHIP5jujfqTd8cX7vlg1z5skC9dN.', 'STUDENTE', NULL),
(4, 'Nicola', 'nicola@panettipitagora.edu.it', '$2y$10$ixicCHAG6ToHlfzs3BtdN.ic4gYEJAYyoyeO1zIbhSqSXBGavsX/6', 'STUDENTE', NULL);

CREATE TABLE IF NOT EXISTS Libri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(100),
    genere VARCHAR(50),
    cTotali INT,
    cDisponibili INT
);

INSERT INTO Libri (id, titolo, genere, cTotali, cDisponibili) VALUES 
(1, 'Cime Tempestose', 'romanzo rosa', 10, 10),
(2, 'Il Piccolo Principe', 'romanzo filosofico', 5, 5),
(3, 'Harry Potter', 'romanzo fantasy', 3, 3);

CREATE TABLE IF NOT EXISTS Sessioni (
    sessionId INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    scadenza DATETIME,
    OTP VARCHAR(6),
    scadenzaOTP DATETIME,
    logout DATETIME DEFAULT NULL,
    FOREIGN KEY (userId) REFERENCES Utenti(userId) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);