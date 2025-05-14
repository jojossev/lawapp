-- Suppression des tables si elles existent
DROP TABLE IF EXISTS `administrateurs`;

-- Table administrateurs (table indépendante)
CREATE TABLE `administrateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertion de l'administrateur par défaut
INSERT INTO `administrateurs` (`email`, `mot_de_passe`, `nom`) VALUES
('admin@lawapp.com', 'admin123', 'Administrateur');
