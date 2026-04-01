create database electionAmericaine;
use electionAmericaine;

CREATE TABLE etats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    nbGrandsElecteurs INT NOT NULL
);

CREATE TABLE candidats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idEtat INT NOT NULL,
    idCandidat INT NOT NULL,
    nbVoix INT NOT NULL,
    FOREIGN KEY (idEtat) REFERENCES etats(id),
    FOREIGN KEY (idCandidat) REFERENCES candidats(id)
);


