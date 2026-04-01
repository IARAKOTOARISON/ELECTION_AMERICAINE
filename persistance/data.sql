-- Insertion des candidats
INSERT INTO candidats (nom) VALUES
('Joe Biden'),
('Donald Trump');

-- Insertion des états avec leurs grands électeurs (totaux réels 2024)
INSERT INTO etats (nom, nbGrandsElecteurs) VALUES
('Californie', 54),
('Texas', 40),
('Floride', 30),
('New York', 28),
('Illinois', 19),
('Pennsylvanie', 19),
('Ohio', 17),
('Géorgie', 16),
('Michigan', 15),
('Caroline du Nord', 16),
('New Jersey', 14),
('Virginie', 13),
('Washington', 12),
('Arizona', 11),
('Massachusetts', 11),
('Tennessee', 11),
('Indiana', 11),
('Maryland', 10),
('Missouri', 10),
('Wisconsin', 10),
('Minnesota', 10),
('Colorado', 10),
('Alabama', 9),
('Caroline du Sud', 9),
('Kentucky', 8),
('Louisiane', 8),
('Oregon', 8),
('Oklahoma', 7),
('Connecticut', 7),
('Iowa', 6),
('Mississippi', 6),
('Arkansas', 6),
('Kansas', 6),
('Utah', 6),
('Nevada', 6),
('Nouveau-Mexique', 5),
('Nebraska', 5),
('Virginie-Occidentale', 4),
('Idaho', 4),
('Hawaï', 4),
('Maine', 4),
('New Hampshire', 4),
('Rhode Island', 4),
('Montana', 4),
('Delaware', 3),
('Dakota du Sud', 3),
('Dakota du Nord', 3),
('Alaska', 3),
('Vermont', 3),
('Wyoming', 3),
('District de Columbia', 3);

-- Insertion des votes (simulation basée sur les résultats réels de 2024)
-- Les pourcentages sont approximatifs pour créer des scénarios réalistes

-- États démocrates (Biden)
INSERT INTO votes (idEtat, idCandidat, nbVoix) VALUES
(1, 1, 11100000), (1, 2, 5500000),  -- Californie: Biden gagne
(4, 1, 4800000), (4, 2, 3200000),   -- New York: Biden gagne
(5, 1, 2900000), (5, 2, 2500000),   -- Illinois: Biden gagne
(11, 1, 2300000), (11, 2, 1900000), -- New Jersey: Biden gagne
(12, 1, 2400000), (12, 2, 1900000), -- Virginie: Biden gagne
(13, 1, 2200000), (13, 2, 1600000), -- Washington: Biden gagne
(15, 1, 1900000), (15, 2, 1300000), -- Massachusetts: Biden gagne
(18, 1, 1600000), (18, 2, 1200000), -- Maryland: Biden gagne
(20, 1, 1700000), (20, 2, 1600000), -- Wisconsin: Biden gagne
(21, 1, 1600000), (21, 2, 1500000), -- Minnesota: Biden gagne
(22, 1, 1500000), (22, 2, 1400000), -- Colorado: Biden gagne
(29, 1, 950000), (29, 2, 750000),   -- Connecticut: Biden gagne
(41, 1, 350000), (41, 2, 320000),    -- Maine: Biden gagne (système proportionnel simplifié)
(43, 1, 300000), (43, 2, 280000),    -- Rhode Island: Biden gagne
(45, 1, 300000), (45, 2, 250000),    -- Delaware: Biden gagne
(50, 1, 240000), (50, 2, 180000),    -- Vermont: Biden gagne
(51, 1, 235000), (51, 2, 190000);    -- District of Columbia: Biden gagne

-- États républicains (Trump)
INSERT INTO votes (idEtat, idCandidat, nbVoix) VALUES
(2, 1, 4800000), (2, 2, 6200000),   -- Texas: Trump gagne
(3, 1, 5200000), (3, 2, 5800000),   -- Floride: Trump gagne
(6, 1, 3300000), (6, 2, 3500000),   -- Pennsylvanie: Trump gagne (très serré)
(7, 1, 2600000), (7, 2, 3200000),   -- Ohio: Trump gagne
(8, 1, 2500000), (8, 2, 2700000),   -- Géorgie: Trump gagne
(9, 1, 2700000), (9, 2, 2800000),   -- Michigan: Trump gagne (très serré)
(10, 1, 2600000), (10, 2, 2800000), -- Caroline du Nord: Trump gagne
(14, 1, 1500000), (14, 2, 1700000), -- Arizona: Trump gagne
(16, 1, 1300000), (16, 2, 1900000), -- Tennessee: Trump gagne
(17, 1, 1200000), (17, 2, 1700000), -- Indiana: Trump gagne
(19, 1, 1200000), (19, 2, 1800000), -- Missouri: Trump gagne
(23, 1, 850000), (23, 2, 1500000),  -- Alabama: Trump gagne
(24, 1, 1100000), (24, 2, 1600000), -- Caroline du Sud: Trump gagne
(25, 1, 850000), (25, 2, 1300000),  -- Kentucky: Trump gagne
(26, 1, 800000), (26, 2, 1300000),  -- Louisiane: Trump gagne
(27, 1, 1100000), (27, 2, 1200000), -- Oregon: Trump gagne (serré)
(28, 1, 700000), (28, 2, 1100000),  -- Oklahoma: Trump gagne
(30, 1, 650000), (30, 2, 950000),   -- Iowa: Trump gagne
(31, 1, 450000), (31, 2, 750000),   -- Mississippi: Trump gagne
(32, 1, 400000), (32, 2, 650000),   -- Arkansas: Trump gagne
(33, 1, 550000), (33, 2, 750000),   -- Kansas: Trump gagne
(34, 1, 500000), (34, 2, 850000),   -- Utah: Trump gagne
(35, 1, 600000), (35, 2, 650000),   -- Nevada: Trump gagne (très serré)
(36, 1, 450000), (36, 2, 500000),   -- Nouveau-Mexique: Trump gagne
(37, 1, 380000), (37, 2, 450000),   -- Nebraska: Trump gagne
(38, 1, 250000), (38, 2, 550000),   -- Virginie-Occidentale: Trump gagne
(39, 1, 280000), (39, 2, 500000),   -- Idaho: Trump gagne
(40, 1, 150000), (40, 2, 200000),   -- Hawaï: Trump gagne (surprise)
(42, 1, 350000), (42, 2, 380000),   -- New Hampshire: Trump gagne
(44, 1, 220000), (44, 2, 320000),   -- Montana: Trump gagne
(46, 1, 150000), (46, 2, 250000),   -- Dakota du Sud: Trump gagne
(47, 1, 120000), (47, 2, 200000),   -- Dakota du Nord: Trump gagne
(48, 1, 110000), (48, 2, 160000),   -- Alaska: Trump gagne
(49, 1, 80000), (49, 2, 210000);    -- Wyoming: Trump gagne

-- Requête pour vérifier le total des grands électeurs par candidat
-- SELECT 
--     c.nom AS candidat,
--     SUM(e.nbGrandsElecteurs) AS total_grands_electeurs
-- FROM candidats c
-- LEFT JOIN votes v ON c.id = v.idCandidat
-- LEFT JOIN etats e ON v.idEtat = e.id
-- GROUP BY c.id
-- ORDER BY total_grands_electeurs DESC;