
drop database if exists election_americaine;


CREATE DATABASE election_americaine;
USE election_americaine;

-- Table des États
CREATE TABLE etats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    nb_grands_electeurs INT NOT NULL
);

-- Table des candidats
CREATE TABLE candidats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL
);

-- Table des votes (nécessaire pour la saisie)
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_etat INT NOT NULL,
    id_candidat INT NOT NULL,
    nb_voix INT NOT NULL,
    FOREIGN KEY (id_etat) REFERENCES etats(id),
    FOREIGN KEY (id_candidat) REFERENCES candidats(id),
    UNIQUE KEY unique_vote (id_etat, id_candidat) -- Un candidat ne peut avoir qu'un seul résultat par état
);


