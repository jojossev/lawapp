DROP TABLE IF EXISTS `acces_livres`;
CREATE TABLE `acces_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_livre` int(11) NOT NULL,
  `date_acces` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_acces` (`id_utilisateur`,`id_livre`),
  KEY `id_livre` (`id_livre`),
  CONSTRAINT `acces_livres_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `acces_livres_ibfk_2` FOREIGN KEY (`id_livre`) REFERENCES `livres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `achats_livres`;
CREATE TABLE `achats_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_livre` int(11) NOT NULL,
  `date_achat` timestamp NOT NULL DEFAULT current_timestamp(),
  `prix_paye` decimal(10,2) NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `statut` enum('en_cours','complete','rembourse') DEFAULT 'en_cours',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_achat` (`id_utilisateur`,`id_livre`),
  KEY `id_livre` (`id_livre`),
  CONSTRAINT `achats_livres_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `achats_livres_ibfk_2` FOREIGN KEY (`id_livre`) REFERENCES `livres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `avis_livres`;
CREATE TABLE `avis_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_livre` int(11) NOT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_avis` (`id_utilisateur`,`id_livre`),
  KEY `id_livre` (`id_livre`),
  CONSTRAINT `avis_livres_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `avis_livres_ibfk_2` FOREIGN KEY (`id_livre`) REFERENCES `livres` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `condition_obtention` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `categories_cours`;
CREATE TABLE `categories_cours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories_cours` VALUES ('1', 'Droit Civil', 'Cours de droit civil', '2025-05-13 23:07:00', '2025-05-13 23:07:00');
INSERT INTO `categories_cours` VALUES ('2', 'Droit Civil', 'Cours sur le droit civil', '2025-05-13 23:49:17', '2025-05-13 23:49:17');
INSERT INTO `categories_cours` VALUES ('3', 'Droit PÚnal', 'Cours sur le droit pÚnal', '2025-05-13 23:49:17', '2025-05-13 23:49:17');
INSERT INTO `categories_cours` VALUES ('4', 'Droit Commercial', 'Cours sur le droit commercial', '2025-05-13 23:49:17', '2025-05-13 23:49:17');
INSERT INTO `categories_cours` VALUES ('5', 'Droit Pénal', 'Cours de droit pénal et procédure', '2025-05-14 12:17:01', '2025-05-14 12:17:01');
INSERT INTO `categories_cours` VALUES ('6', 'Droit Constitutionnel', 'Cours sur la constitution', '2025-05-14 12:17:01', '2025-05-14 12:17:01');
INSERT INTO `categories_cours` VALUES ('7', 'Droit International', 'Formation en droit international', '2025-05-14 12:17:01', '2025-05-14 12:17:01');
INSERT INTO `categories_cours` VALUES ('8', 'Droit du Travail', 'Cours de droit social', '2025-05-14 12:17:01', '2025-05-14 12:17:01');
INSERT INTO `categories_cours` VALUES ('9', 'Droit Administratif', 'Formation en droit public', '2025-05-14 12:17:02', '2025-05-14 12:17:02');
INSERT INTO `categories_cours` VALUES ('10', 'Droit Fiscal', 'Cours sur la fiscalité', '2025-05-14 12:17:02', '2025-05-14 12:17:02');

DROP TABLE IF EXISTS `categories_livres`;
CREATE TABLE `categories_livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories_livres` VALUES ('1', 'Droit Civil', 'Livres sur le droit civil', 'droit-civil', '2025-05-13 23:45:24', '2025-05-13 23:45:24');
INSERT INTO `categories_livres` VALUES ('2', 'Droit PÚnal', 'Livres sur le droit pÚnal', 'droit-penal', '2025-05-13 23:45:24', '2025-05-13 23:45:24');
INSERT INTO `categories_livres` VALUES ('3', 'Droit Commercial', 'Livres sur le droit commercial', 'droit-commercial', '2025-05-13 23:45:24', '2025-05-13 23:45:24');

DROP TABLE IF EXISTS `categories_podcasts`;
CREATE TABLE `categories_podcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories_podcasts` VALUES ('1', 'ActualitÚs Juridiques', 'Podcasts sur les derniÞres actualitÚs du droit', '2025-05-14 00:04:32', '2025-05-14 00:04:32');
INSERT INTO `categories_podcasts` VALUES ('2', '╔tudes de Cas', 'Analyses dÚtaillÚes de cas juridiques intÚressants', '2025-05-14 00:04:32', '2025-05-14 00:04:32');
INSERT INTO `categories_podcasts` VALUES ('3', 'Interviews d\'Experts', 'Entretiens avec des professionnels du droit', '2025-05-14 00:04:32', '2025-05-14 00:04:32');
INSERT INTO `categories_podcasts` VALUES ('4', 'Actualités Juridiques', 'Les dernières nouvelles du monde juridique', '2025-05-14 12:21:44', '2025-05-14 12:21:44');
INSERT INTO `categories_podcasts` VALUES ('5', 'Cas Pratiques', 'Analyse de cas juridiques concrets', '2025-05-14 12:21:47', '2025-05-14 12:21:47');
INSERT INTO `categories_podcasts` VALUES ('6', 'Conseils Juridiques', 'Conseils pratiques sur des questions juridiques', '2025-05-14 12:21:47', '2025-05-14 12:21:47');
INSERT INTO `categories_podcasts` VALUES ('7', 'Débats Juridiques', 'Discussions sur des sujets juridiques controversés', '2025-05-14 12:21:47', '2025-05-14 12:21:47');

DROP TABLE IF EXISTS `categories_videos`;
CREATE TABLE `categories_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories_videos` VALUES ('1', 'Cours de Droit Civil', 'VidÚos de cours sur le droit civil', '2025-05-13 23:53:21', '2025-05-13 23:53:21');
INSERT INTO `categories_videos` VALUES ('2', 'Tutoriels Juridiques', 'VidÚos explicatives sur des concepts juridiques', '2025-05-13 23:53:21', '2025-05-13 23:53:21');
INSERT INTO `categories_videos` VALUES ('3', 'ConfÚrences', 'Enregistrements de confÚrences juridiques', '2025-05-13 23:53:21', '2025-05-13 23:53:21');

DROP TABLE IF EXISTS `chatbot_conversations`;
CREATE TABLE `chatbot_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `chatbot_conversations_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `chatbot_messages`;
CREATE TABLE `chatbot_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_conversation` int(11) NOT NULL,
  `message` text NOT NULL,
  `est_utilisateur` tinyint(1) DEFAULT 1,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_conversation` (`id_conversation`),
  CONSTRAINT `chatbot_messages_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `chatbot_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `chatbot_reponses`;
CREATE TABLE `chatbot_reponses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `reponse` text NOT NULL,
  `mots_cles` text NOT NULL,
  `categorie` varchar(50) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `chatbot_reponses` VALUES ('1', 'Qu\'est-ce qu\'un contrat de travail ?', 'Un contrat de travail est une convention par laquelle une personne (le salarié) s\'engage à travailler pour le compte et sous la direction d\'une autre personne (l\'employeur) en contrepartie d\'une rémunération.', 'contrat travail emploi cdi cdd', 'Droit du Travail', '2025-05-14 12:41:36');
INSERT INTO `chatbot_reponses` VALUES ('2', 'Comment créer une entreprise ?', 'Pour créer une entreprise, vous devez suivre plusieurs étapes : 1) Choisir la forme juridique (SARL, SAS, etc.), 2) Rédiger les statuts, 3) Déposer le capital social, 4) Publier une annonce légale, 5) Immatriculer l\'entreprise au RCS.', 'entreprise création sarl sas rcs statuts', 'Droit des Sociétés', '2025-05-14 12:41:36');
INSERT INTO `chatbot_reponses` VALUES ('3', 'Quels sont mes droits en cas de licenciement ?', 'En cas de licenciement, vous avez droit à : 1) Un préavis ou une indemnité compensatrice, 2) Une indemnité de licenciement si vous avez plus de 8 mois d\'ancienneté, 3) Une indemnité compensatrice de congés payés, 4) La possibilité de contester le licenciement aux prud\'hommes.', 'licenciement droits indemnités préavis', 'Droit du Travail', '2025-05-14 12:41:36');
INSERT INTO `chatbot_reponses` VALUES ('4', 'Comment se déroule un divorce ?', 'Le divorce peut se dérouler de plusieurs manières :\n1. Divorce par consentement mutuel : les époux sont d\'accord sur tout\n2. Divorce pour faute : un époux demande le divorce en raison des fautes de l\'autre\n3. Divorce pour altération définitive du lien conjugal : après 2 ans de séparation\n4. Divorce pour acceptation du principe de la rupture du mariage\n\nDans tous les cas, il est recommandé de consulter un avocat pour être bien conseillé.', 'divorce separation mariage epoux conjoint', 'Droit de la Famille', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('5', 'Comment adopter un enfant ?', 'L\'adoption peut être simple ou plénière. Les conditions principales sont :\n1. Avoir plus de 28 ans (sauf cas particuliers)\n2. Avoir 15 ans de plus que l\'enfant à adopter\n3. Être marié depuis plus de 2 ans ou être célibataire\n4. Obtenir l\'agrément des services sociaux\n\nLa procédure implique plusieurs étapes administratives et judiciaires.', 'adoption enfant famille agrément', 'Droit de la Famille', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('6', 'Quels sont mes droits en tant que locataire ?', 'En tant que locataire, vous avez plusieurs droits fondamentaux :\n1. Droit à un logement décent\n2. Droit au maintien dans les lieux\n3. Droit de donner congé avec un préavis\n4. Droit de sous-louer avec l\'accord du propriétaire\n5. Droit de faire des travaux d\'aménagement\n\nLe propriétaire doit respecter ces droits sous peine de sanctions.', 'location bail locataire propriétaire logement', 'Droit Immobilier', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('7', 'Comment acheter un bien immobilier ?', 'Les étapes pour acheter un bien immobilier :\n1. Définir son budget et obtenir un prêt\n2. Rechercher le bien\n3. Faire une offre d\'achat\n4. Signer un compromis de vente\n5. Obtenir le financement définitif\n6. Signer l\'acte authentique chez le notaire\n\nIl est conseillé de se faire accompagner par un professionnel.', 'achat immobilier maison appartement notaire', 'Droit Immobilier', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('8', 'Que faire en cas de garde à vue ?', 'En cas de garde à vue, vous avez des droits :\n1. Droit au silence\n2. Droit à un avocat\n3. Droit de prévenir un proche\n4. Droit à un médecin\n5. Droit de connaître les faits reprochés\n\nIl est fortement conseillé de demander l\'assistance d\'un avocat dès le début de la garde à vue.', 'garde vue police arrestation droits', 'Droit Pénal', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('9', 'Comment porter plainte ?', 'Pour porter plainte, vous avez plusieurs options :\n1. Au commissariat ou à la gendarmerie\n2. Par courrier au procureur de la République\n3. En ligne pour certaines infractions\n\nLa plainte doit contenir :\n- Vos coordonnées\n- Le récit détaillé des faits\n- Les preuves éventuelles\n- L\'identité de l\'auteur si connue', 'plainte police justice victime', 'Droit Pénal', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('10', 'Quels sont mes droits en cas de produit défectueux ?', 'En cas de produit défectueux, vous bénéficiez :\n1. De la garantie légale de conformité (2 ans)\n2. De la garantie des vices cachés\n3. Du droit de retour dans le cas d\'un achat en ligne (14 jours)\n\nVous pouvez demander :\n- La réparation ou le remplacement\n- Le remboursement si les solutions précédentes sont impossibles', 'garantie défaut produit consommation remboursement', 'Droit de la Consommation', '2025-05-14 12:42:22');
INSERT INTO `chatbot_reponses` VALUES ('11', 'Comment résilier un abonnement ?', 'Pour résilier un abonnement :\n1. Vérifier les conditions de résiliation dans le contrat\n2. Respecter le préavis si prévu\n3. Envoyer une lettre recommandée avec AR\n4. Conserver une copie du courrier et l\'AR\n\nCertains contrats peuvent être résiliés à tout moment (loi Chatel), d\'autres nécessitent un motif légitime.', 'résiliation abonnement contrat préavis', 'Droit de la Consommation', '2025-05-14 12:42:22');

DROP TABLE IF EXISTS `cours`;
CREATE TABLE `cours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT 'debutant',
  `duree_estimee` varchar(100) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT 0.00,
  `statut` varchar(50) DEFAULT 'brouillon',
  `image_url` varchar(255) DEFAULT NULL,
  `id_createur` int(11) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `objectifs` text DEFAULT NULL,
  `prerequis` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `type_contenu` enum('texte','video','quiz') DEFAULT 'texte',
  `duree` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_categorie` (`id_categorie`),
  CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories_cours` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cours` VALUES ('1', 'Introduction au Droit Civil', 'Cours d\'introduction au droit civil', '1', 'debutant', '10 heures', '0.00', 'publie', NULL, NULL, '2025-05-13 23:07:00', '2025-05-13 23:07:00', NULL, NULL, NULL, 'texte', '0');
INSERT INTO `cours` VALUES ('2', 'Introduction au Droit Civil', 'Formation complète pour comprendre les bases du droit civil', '1', 'Avancé', NULL, '201.00', 'publie', '/LawApp/assets/images/cours/introduction-au-droit-civil.jpg', NULL, '2025-05-14 12:19:26', '2025-05-14 12:19:26', 'Objectifs du cours Introduction au Droit Civil', 'Aucun prérequis particulier', NULL, 'texte', '5');
INSERT INTO `cours` VALUES ('3', 'Introduction au Droit Civil', 'Formation complète pour comprendre les bases du droit civil', '1', 'Débutant', NULL, '157.00', 'publie', '/LawApp/assets/images/cours/introduction-au-droit-civil.jpg', NULL, '2025-05-14 12:20:01', '2025-05-14 12:20:01', 'Objectifs du cours Introduction au Droit Civil', 'Aucun prérequis particulier', NULL, 'texte', '5');
INSERT INTO `cours` VALUES ('4', 'Introduction au Droit Civil', 'Formation complète pour comprendre les bases du droit civil', '1', 'Avancé', NULL, '253.00', 'publie', '/LawApp/assets/images/cours/introduction-au-droit-civil.jpg', NULL, '2025-05-14 12:20:45', '2025-05-14 12:20:45', 'Objectifs du cours Introduction au Droit Civil', 'Aucun prérequis particulier', NULL, 'texte', '5');
INSERT INTO `cours` VALUES ('5', 'Introduction au Droit Civil', 'Formation complète pour comprendre les bases du droit civil', '1', 'Débutant', NULL, '163.00', 'publie', '/LawApp/assets/images/cours/introduction-au-droit-civil.jpg', NULL, '2025-05-14 12:21:22', '2025-05-14 12:21:22', 'Objectifs du cours Introduction au Droit Civil', 'Aucun prérequis particulier', NULL, 'texte', '5');
INSERT INTO `cours` VALUES ('6', 'Droit Pénal Général', 'Maîtrisez les principes fondamentaux du droit pénal', '5', 'Intermédiaire', NULL, '228.00', 'publie', '/LawApp/assets/images/cours/droit-pénal-général.jpg', NULL, '2025-05-14 12:21:29', '2025-05-14 12:21:29', 'Objectifs du cours Droit Pénal Général', 'Aucun prérequis particulier', NULL, 'texte', '4');
INSERT INTO `cours` VALUES ('7', 'Droit des Sociétés Approfondi', 'Maîtrisez tous les aspects du droit des sociétés', '4', 'Avancé', NULL, '398.00', 'publie', '/LawApp/assets/images/cours/droit-des-sociétés-approfondi.jpg', NULL, '2025-05-14 12:24:54', '2025-05-14 12:24:54', 'À la fin de ce cours, vous serez capable de :\n- Comprendre les concepts fondamentaux\n- Appliquer les connaissances en pratique\n- Analyser des situations complexes', 'Niveau précédent recommandé', NULL, 'texte', '5');
INSERT INTO `cours` VALUES ('8', 'Relations de Travail', 'Tout sur les relations employeur-employé', '8', 'Avancé', NULL, '322.00', 'publie', '/LawApp/assets/images/cours/relations-de-travail.jpg', NULL, '2025-05-14 12:24:56', '2025-05-14 12:24:56', 'À la fin de ce cours, vous serez capable de :\n- Comprendre les concepts fondamentaux\n- Appliquer les connaissances en pratique\n- Analyser des situations complexes', 'Niveau précédent recommandé', NULL, 'texte', '4');
INSERT INTO `cours` VALUES ('9', 'Fiscalité des Entreprises', 'Formation complète sur la fiscalité des entreprises', '10', 'Avancé', NULL, '199.00', 'publie', '/LawApp/assets/images/cours/fiscalité-des-entreprises.jpg', NULL, '2025-05-14 12:24:56', '2025-05-14 12:24:56', 'À la fin de ce cours, vous serez capable de :\n- Comprendre les concepts fondamentaux\n- Appliquer les connaissances en pratique\n- Analyser des situations complexes', 'Niveau précédent recommandé', NULL, 'texte', '4');
INSERT INTO `cours` VALUES ('10', 'Commerce International', 'Les bases du droit du commerce international', '7', 'Intermédiaire', NULL, '395.00', 'publie', '/LawApp/assets/images/cours/commerce-international.jpg', NULL, '2025-05-14 12:24:57', '2025-05-14 12:24:57', 'À la fin de ce cours, vous serez capable de :\n- Comprendre les concepts fondamentaux\n- Appliquer les connaissances en pratique\n- Analyser des situations complexes', 'Niveau précédent recommandé', NULL, 'texte', '5');

DROP TABLE IF EXISTS `inscriptions`;
CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_cours','termine','abandonne') DEFAULT 'en_cours',
  `progression` int(11) DEFAULT 0,
  `derniere_activite` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_inscription` (`id_utilisateur`,`id_cours`),
  KEY `id_cours` (`id_cours`),
  CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `lecons`;
CREATE TABLE `lecons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_module` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type_contenu` enum('texte','video','pdf','docx','mp3','mp4') DEFAULT 'texte',
  `contenu_principal` longtext DEFAULT NULL,
  `fichier_path` varchar(255) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `duree_estimee` varchar(50) DEFAULT NULL,
  `statut` enum('brouillon','publie') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `objectifs` text DEFAULT NULL,
  `ressources` text DEFAULT NULL,
  `fichiers_url` text DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `duree` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_module` (`id_module`),
  CONSTRAINT `lecons_ibfk_1` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lecons` VALUES ('1', '1', 'DROIT', NULL, 'pdf', '', 'uploads/lecons/6823e22279bbf_Vendredi saint - Psaume 30.pdf', '0', NULL, 'publie', '2025-05-14 01:21:54', '2025-05-14 01:21:54', NULL, NULL, NULL, NULL, '0');
INSERT INTO `lecons` VALUES ('2', '5', 'Les Sources du Droit', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Les Sources du Droit', '45');
INSERT INTO `lecons` VALUES ('3', '5', 'Les Personnes Juridiques', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Les Personnes Juridiques', '30');
INSERT INTO `lecons` VALUES ('4', '5', 'Les Biens et la Propriété', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Les Biens et la Propriété', '40');
INSERT INTO `lecons` VALUES ('5', '6', 'Introduction aux Obligations', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Introduction aux Obligations', '35');
INSERT INTO `lecons` VALUES ('6', '6', 'Les Contrats', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Les Contrats', '50');
INSERT INTO `lecons` VALUES ('7', '6', 'La Responsabilité Civile', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon La Responsabilité Civile', '45');
INSERT INTO `lecons` VALUES ('8', '7', 'La Loi Pénale', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon La Loi Pénale', '40');
INSERT INTO `lecons` VALUES ('9', '7', 'L\'Infraction', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon L\'Infraction', '45');
INSERT INTO `lecons` VALUES ('10', '7', 'La Responsabilité Pénale', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon La Responsabilité Pénale', '50');
INSERT INTO `lecons` VALUES ('11', '8', 'Classification des Peines', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Classification des Peines', '35');
INSERT INTO `lecons` VALUES ('12', '8', 'L\'Application des Peines', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon L\'Application des Peines', '40');
INSERT INTO `lecons` VALUES ('13', '8', 'Les Alternatives aux Poursuites', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', NULL, NULL, NULL, 'Contenu de la leçon Les Alternatives aux Poursuites', '30');
INSERT INTO `lecons` VALUES ('14', '9', 'La SARL', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', NULL, NULL, NULL, 'Caractéristiques et fonctionnement de la SARL', '45');
INSERT INTO `lecons` VALUES ('15', '9', 'La SA', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', NULL, NULL, NULL, 'Structure et gouvernance de la SA', '40');
INSERT INTO `lecons` VALUES ('16', '9', 'La SAS', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', NULL, NULL, NULL, 'Flexibilité et avantages de la SAS', '35');
INSERT INTO `lecons` VALUES ('17', '10', 'Les Assemblées Générales', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', NULL, NULL, NULL, 'Organisation et déroulement des AG', '50');
INSERT INTO `lecons` VALUES ('18', '10', 'La Responsabilité des Dirigeants', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Droits et obligations des dirigeants', '45');
INSERT INTO `lecons` VALUES ('19', '10', 'Les Opérations sur Capital', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Augmentation et réduction de capital', '40');
INSERT INTO `lecons` VALUES ('20', '11', 'Types de Contrats', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'CDI, CDD, Intérim', '40');
INSERT INTO `lecons` VALUES ('21', '11', 'Clauses Essentielles', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Analyse des clauses importantes', '35');
INSERT INTO `lecons` VALUES ('22', '11', 'Modification du Contrat', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Procédures de modification', '30');
INSERT INTO `lecons` VALUES ('23', '12', 'Le Licenciement', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Procédure et motifs', '45');
INSERT INTO `lecons` VALUES ('24', '12', 'La Démission', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Droits et obligations', '30');
INSERT INTO `lecons` VALUES ('25', '12', 'La Rupture Conventionnelle', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Procédure et avantages', '40');
INSERT INTO `lecons` VALUES ('26', '13', 'Calcul du Résultat Fiscal', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Détermination de l\'assiette', '50');
INSERT INTO `lecons` VALUES ('27', '13', 'Régimes Spéciaux', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'PME, holdings, groupes', '45');
INSERT INTO `lecons` VALUES ('28', '13', 'Optimisation Fiscale', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Stratégies légales', '40');
INSERT INTO `lecons` VALUES ('29', '14', 'Mécanisme de la TVA', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Principes fondamentaux', '35');
INSERT INTO `lecons` VALUES ('30', '14', 'TVA Déductible', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Conditions de déduction', '40');
INSERT INTO `lecons` VALUES ('31', '14', 'Déclarations de TVA', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', NULL, NULL, NULL, 'Obligations déclaratives', '30');
INSERT INTO `lecons` VALUES ('32', '15', 'Incoterms', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Comprendre et utiliser les Incoterms', '45');
INSERT INTO `lecons` VALUES ('33', '15', 'Clause d\'Arbitrage', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Résolution des litiges', '40');
INSERT INTO `lecons` VALUES ('34', '15', 'Droit Applicable', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Choix de la loi applicable', '35');
INSERT INTO `lecons` VALUES ('35', '16', 'Régimes Douaniers', NULL, 'texte', NULL, NULL, '1', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Les différents régimes', '50');
INSERT INTO `lecons` VALUES ('36', '16', 'Réglementation des Changes', NULL, 'texte', NULL, NULL, '2', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Contrôle des changes', '45');
INSERT INTO `lecons` VALUES ('37', '16', 'Documents d\'Export', NULL, 'texte', NULL, NULL, '3', NULL, 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', NULL, NULL, NULL, 'Documentation nécessaire', '40');

DROP TABLE IF EXISTS `livre_categories`;
CREATE TABLE `livre_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `livre_categories` VALUES ('1', 'Droit Civil', 'Livres sur le droit civil', '2025-05-14 12:05:06');
INSERT INTO `livre_categories` VALUES ('2', 'Droit Pénal', 'Ouvrages sur le droit pénal et la procédure pénale', '2025-05-14 12:10:28');
INSERT INTO `livre_categories` VALUES ('3', 'Droit Commercial', 'Livres sur le droit des affaires et le commerce', '2025-05-14 12:10:29');
INSERT INTO `livre_categories` VALUES ('4', 'Droit Constitutionnel', 'Ouvrages sur la constitution et les institutions', '2025-05-14 12:10:32');
INSERT INTO `livre_categories` VALUES ('5', 'Droit International', 'Livres sur le droit international et les relations entre États', '2025-05-14 12:10:32');
INSERT INTO `livre_categories` VALUES ('6', 'Droit du Travail', 'Ouvrages sur le droit social et le travail', '2025-05-14 12:10:32');
INSERT INTO `livre_categories` VALUES ('7', 'Droit Administratif', 'Livres sur l\'administration et le droit public', '2025-05-14 12:10:32');
INSERT INTO `livre_categories` VALUES ('8', 'Droit Fiscal', 'Ouvrages sur la fiscalité et les impôts', '2025-05-14 12:10:32');

DROP TABLE IF EXISTS `livres`;
CREATE TABLE `livres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `auteur` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `statut` enum('brouillon','publie') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `url_document` varchar(255) DEFAULT NULL,
  `type_document` varchar(50) DEFAULT NULL,
  `couverture_url` varchar(255) DEFAULT NULL,
  `annee_publication` int(11) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `id_createur` int(11) DEFAULT NULL,
  `nombre_pages` int(11) DEFAULT NULL,
  `editeur` varchar(100) DEFAULT NULL,
  `langue` varchar(50) DEFAULT 'Français',
  `isbn` varchar(13) DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_createur` (`id_createur`),
  KEY `fk_livre_categorie` (`id_categorie`),
  CONSTRAINT `fk_livre_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `livre_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `livres_ibfk_2` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `livres` VALUES ('1', 'Introduction au Droit Civil', 'Jean Dupont', 'Un guide complet sur les bases du droit civil', '29.99', '1', NULL, 'publie', '2025-05-13 23:50:50', '2025-05-14 12:07:12', '/LawApp/assets/documents/droit-civil.pdf', 'PDF', '/LawApp/assets/images/livres/droit-civil.jpg', '2025', 'Débutant', NULL, '350', NULL, 'Français', NULL, NULL);
INSERT INTO `livres` VALUES ('2', 'Le Droit PÚnal ExpliquÚ', 'Marie Martin', 'Une approche pratique du droit pÚnal', '34.99', '2', NULL, 'publie', '2025-05-13 23:50:50', '2025-05-13 23:50:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Français', NULL, NULL);
INSERT INTO `livres` VALUES ('3', 'Guide du Droit Commercial', 'Pierre Durand', 'Tout ce qu\'il faut savoir sur le droit commercial', '39.99', '3', NULL, 'publie', '2025-05-13 23:50:50', '2025-05-13 23:50:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Français', NULL, NULL);
INSERT INTO `livres` VALUES ('4', 'Les Fondamentaux du Droit Civil', 'Marie Dupont', 'Une introduction complète au droit civil.', '29.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:11:24', '2025-05-14 12:11:24', '/LawApp/assets/documents/les-fondamentaux-du-droit-civil.pdf', 'PDF', NULL, '2022', 'Intermédiaire', NULL, '683', 'Larcier', 'Français', '9785805209156', NULL);
INSERT INTO `livres` VALUES ('5', 'Le Contrat en Droit Civil', 'Pierre Martin', 'Tout sur la théorie des contrats.', '34.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:11:25', '2025-05-14 12:11:25', '/LawApp/assets/documents/le-contrat-en-droit-civil.pdf', 'PDF', NULL, '2025', 'Débutant', NULL, '373', 'Larcier', 'Français', '9785323881957', NULL);
INSERT INTO `livres` VALUES ('6', 'La Responsabilité Civile', 'Sophie Laurent', 'Comprendre la responsabilité civile.', '39.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/la-responsabilité-civile.pdf', 'PDF', NULL, '2020', 'Débutant', NULL, '370', 'LexisNexis', 'Français', '9786358343654', NULL);
INSERT INTO `livres` VALUES ('7', 'Les Obligations en Droit Civil', 'Jean Dubois', 'Manuel complet sur les obligations.', '44.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/les-obligations-en-droit-civil.pdf', 'PDF', NULL, '2022', 'Intermédiaire', NULL, '336', 'Larcier', 'Français', '9781810805730', NULL);
INSERT INTO `livres` VALUES ('8', 'Le Droit de la Famille', 'Claire Moreau', 'Guide pratique du droit familial.', '32.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/le-droit-de-la-famille.pdf', 'PDF', NULL, '2024', 'Avancé', NULL, '666', 'LGDJ', 'Français', '9786264325500', NULL);
INSERT INTO `livres` VALUES ('9', 'Manuel de Droit Pénal', 'Thomas Bernard', 'Les bases du droit pénal.', '45.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/manuel-de-droit-pénal.pdf', 'PDF', NULL, '2022', 'Débutant', NULL, '778', 'LexisNexis', 'Français', '9783343483467', NULL);
INSERT INTO `livres` VALUES ('10', 'La Procédure Pénale', 'Luc Girard', 'Guide de la procédure pénale.', '49.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/la-procédure-pénale.pdf', 'PDF', NULL, '2021', 'Débutant', NULL, '324', 'LGDJ', 'Français', '9788766025821', NULL);
INSERT INTO `livres` VALUES ('11', 'Les Infractions Pénales', 'Marie Leclerc', 'Classification des infractions.', '39.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/les-infractions-pénales.pdf', 'PDF', NULL, '2021', 'Intermédiaire', NULL, '515', 'LGDJ', 'Français', '9785128907678', NULL);
INSERT INTO `livres` VALUES ('12', 'Droit Pénal Spécial', 'Paul Durant', 'Étude des infractions particulières.', '42.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/droit-pénal-spécial.pdf', 'PDF', NULL, '2023', 'Avancé', NULL, '344', 'Larcier', 'Français', '9788936331377', NULL);
INSERT INTO `livres` VALUES ('13', 'La Responsabilité Pénale', 'Anne Richard', 'Analyse de la responsabilité.', '37.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/la-responsabilité-pénale.pdf', 'PDF', NULL, '2020', 'Débutant', NULL, '752', 'Dalloz', 'Français', '9789839841384', NULL);
INSERT INTO `livres` VALUES ('14', 'Droit des Sociétés', 'Michel Robert', 'Les différentes formes de sociétés.', '54.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/droit-des-sociétés.pdf', 'PDF', NULL, '2022', 'Avancé', NULL, '322', 'Dalloz', 'Français', '9782045157843', NULL);
INSERT INTO `livres` VALUES ('15', 'Le Fonds de Commerce', 'Julie Petit', 'Tout sur le fonds de commerce.', '47.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/le-fonds-de-commerce.pdf', 'PDF', NULL, '2023', 'Débutant', NULL, '433', 'Dalloz', 'Français', '9788467515109', NULL);
INSERT INTO `livres` VALUES ('16', 'Les Contrats Commerciaux', 'François Martin', 'Guide des contrats commerciaux.', '49.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/les-contrats-commerciaux.pdf', 'PDF', NULL, '2023', 'Débutant', NULL, '224', 'LexisNexis', 'Français', '9783612366634', NULL);
INSERT INTO `livres` VALUES ('17', 'Droit de la Concurrence', 'Sophie Blanc', 'Comprendre la concurrence.', '52.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/droit-de-la-concurrence.pdf', 'PDF', NULL, '2023', 'Intermédiaire', NULL, '428', 'Dalloz', 'Français', '9789235958981', NULL);
INSERT INTO `livres` VALUES ('18', 'Le Droit des Entreprises', 'Pierre Durand', 'Manuel du droit des entreprises.', '56.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:11:26', '2025-05-14 12:11:26', '/LawApp/assets/documents/le-droit-des-entreprises.pdf', 'PDF', NULL, '2020', 'Avancé', NULL, '268', 'Éditions Juridiques', 'Français', '9787751203681', NULL);
INSERT INTO `livres` VALUES ('20', 'Les Fondamentaux du Droit Civil', 'Marie Dupont', 'Une introduction complète au droit civil.', '29.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:12:20', '2025-05-14 12:12:20', '/LawApp/assets/documents/les-fondamentaux-du-droit-civil.pdf', 'PDF', NULL, '2021', 'Débutant', NULL, '419', 'Éditions Juridiques', 'Français', '9785540839523', NULL);
INSERT INTO `livres` VALUES ('21', 'Le Contrat en Droit Civil', 'Pierre Martin', 'Tout sur la théorie des contrats.', '34.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:12:22', '2025-05-14 12:12:22', '/LawApp/assets/documents/le-contrat-en-droit-civil.pdf', 'PDF', NULL, '2020', 'Avancé', NULL, '360', 'LGDJ', 'Français', '9787905016475', NULL);
INSERT INTO `livres` VALUES ('22', 'La Responsabilité Civile', 'Sophie Laurent', 'Comprendre la responsabilité civile.', '39.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/la-responsabilité-civile.pdf', 'PDF', NULL, '2024', 'Débutant', NULL, '430', 'Dalloz', 'Français', '9783893512942', NULL);
INSERT INTO `livres` VALUES ('23', 'Les Obligations en Droit Civil', 'Jean Dubois', 'Manuel complet sur les obligations.', '44.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/les-obligations-en-droit-civil.pdf', 'PDF', NULL, '2023', 'Débutant', NULL, '458', 'LexisNexis', 'Français', '9787494448834', NULL);
INSERT INTO `livres` VALUES ('24', 'Le Droit de la Famille', 'Claire Moreau', 'Guide pratique du droit familial.', '32.99', '1', '/LawApp/assets/images/livres/default-droit-civil.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/le-droit-de-la-famille.pdf', 'PDF', NULL, '2024', 'Intermédiaire', NULL, '419', 'Éditions Juridiques', 'Français', '9789138957468', NULL);
INSERT INTO `livres` VALUES ('25', 'Manuel de Droit Pénal', 'Thomas Bernard', 'Les bases du droit pénal.', '45.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/manuel-de-droit-pénal.pdf', 'PDF', NULL, '2025', 'Intermédiaire', NULL, '590', 'LGDJ', 'Français', '9785070864703', NULL);
INSERT INTO `livres` VALUES ('26', 'La Procédure Pénale', 'Luc Girard', 'Guide de la procédure pénale.', '49.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/la-procédure-pénale.pdf', 'PDF', NULL, '2020', 'Débutant', NULL, '687', 'Éditions Juridiques', 'Français', '9787888823657', NULL);
INSERT INTO `livres` VALUES ('27', 'Les Infractions Pénales', 'Marie Leclerc', 'Classification des infractions.', '39.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/les-infractions-pénales.pdf', 'PDF', NULL, '2023', 'Avancé', NULL, '507', 'Dalloz', 'Français', '9788794613163', NULL);
INSERT INTO `livres` VALUES ('28', 'Droit Pénal Spécial', 'Paul Durant', 'Étude des infractions particulières.', '42.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/droit-pénal-spécial.pdf', 'PDF', NULL, '2025', 'Débutant', NULL, '327', 'LexisNexis', 'Français', '9785942485829', NULL);
INSERT INTO `livres` VALUES ('29', 'La Responsabilité Pénale', 'Anne Richard', 'Analyse de la responsabilité.', '37.99', '2', '/LawApp/assets/images/livres/default-droit-pénal.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/la-responsabilité-pénale.pdf', 'PDF', NULL, '2024', 'Intermédiaire', NULL, '496', 'Éditions Juridiques', 'Français', '9788085081426', NULL);
INSERT INTO `livres` VALUES ('30', 'Droit des Sociétés', 'Michel Robert', 'Les différentes formes de sociétés.', '54.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/droit-des-sociétés.pdf', 'PDF', NULL, '2023', 'Intermédiaire', NULL, '757', 'Larcier', 'Français', '9786532359242', NULL);
INSERT INTO `livres` VALUES ('31', 'Le Fonds de Commerce', 'Julie Petit', 'Tout sur le fonds de commerce.', '47.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/le-fonds-de-commerce.pdf', 'PDF', NULL, '2024', 'Intermédiaire', NULL, '686', 'Larcier', 'Français', '9789979826210', NULL);
INSERT INTO `livres` VALUES ('32', 'Les Contrats Commerciaux', 'François Martin', 'Guide des contrats commerciaux.', '49.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/les-contrats-commerciaux.pdf', 'PDF', NULL, '2025', 'Débutant', NULL, '316', 'Éditions Juridiques', 'Français', '9782470843006', NULL);
INSERT INTO `livres` VALUES ('33', 'Droit de la Concurrence', 'Sophie Blanc', 'Comprendre la concurrence.', '52.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/droit-de-la-concurrence.pdf', 'PDF', NULL, '2025', 'Débutant', NULL, '201', 'LGDJ', 'Français', '9785498657025', NULL);
INSERT INTO `livres` VALUES ('34', 'Le Droit des Entreprises', 'Pierre Durand', 'Manuel du droit des entreprises.', '56.99', '3', '/LawApp/assets/images/livres/default-droit-commercial.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/le-droit-des-entreprises.pdf', 'PDF', NULL, '2024', 'Intermédiaire', NULL, '543', 'LexisNexis', 'Français', '9789167181517', NULL);
INSERT INTO `livres` VALUES ('35', 'La Constitution Expliquée', 'Jean-Marc Simon', 'Analyse de la constitution.', '44.99', '4', '/LawApp/assets/images/livres/default-droit-constitutionnel.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/la-constitution-expliquée.pdf', 'PDF', NULL, '2020', 'Intermédiaire', NULL, '422', 'LGDJ', 'Français', '9785632698953', NULL);
INSERT INTO `livres` VALUES ('36', 'Les Institutions Politiques', 'Marie-Claire Dubois', 'Guide des institutions.', '42.99', '4', '/LawApp/assets/images/livres/default-droit-constitutionnel.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/les-institutions-politiques.pdf', 'PDF', NULL, '2022', 'Débutant', NULL, '431', 'Éditions Juridiques', 'Français', '9781549677381', NULL);
INSERT INTO `livres` VALUES ('37', 'Le Conseil Constitutionnel', 'Paul Henri', 'Rôle et fonctionnement.', '39.99', '4', '/LawApp/assets/images/livres/default-droit-constitutionnel.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/le-conseil-constitutionnel.pdf', 'PDF', NULL, '2021', 'Intermédiaire', NULL, '250', 'Éditions Juridiques', 'Français', '9784996420635', NULL);
INSERT INTO `livres` VALUES ('38', 'Les Libertés Fondamentales', 'Claire Martin', 'Étude des droits fondamentaux.', '45.99', '4', '/LawApp/assets/images/livres/default-droit-constitutionnel.jpg', 'publie', '2025-05-14 12:12:28', '2025-05-14 12:12:28', '/LawApp/assets/documents/les-libertés-fondamentales.pdf', 'PDF', NULL, '2025', 'Avancé', NULL, '669', 'LGDJ', 'Français', '9786193870005', NULL);
INSERT INTO `livres` VALUES ('39', 'Le Système Politique', 'Thomas Leroy', 'Analyse du système politique.', '41.99', '4', '/LawApp/assets/images/livres/default-droit-constitutionnel.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-système-politique.pdf', 'PDF', NULL, '2023', 'Débutant', NULL, '257', 'Dalloz', 'Français', '9782965208380', NULL);
INSERT INTO `livres` VALUES ('40', 'Droit International Public', 'Marie Rousseau', 'Les bases du droit international.', '59.99', '5', '/LawApp/assets/images/livres/default-droit-international.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/droit-international-public.pdf', 'PDF', NULL, '2022', 'Avancé', NULL, '564', 'LGDJ', 'Français', '9788739498449', NULL);
INSERT INTO `livres` VALUES ('41', 'Droit International Privé', 'Jean-Pierre Mercier', 'Relations juridiques privées.', '57.99', '5', '/LawApp/assets/images/livres/default-droit-international.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/droit-international-privé.pdf', 'PDF', NULL, '2022', 'Débutant', NULL, '429', 'Larcier', 'Français', '9783411152787', NULL);
INSERT INTO `livres` VALUES ('42', 'Les Traités Internationaux', 'Sophie Moreau', 'Étude des traités.', '54.99', '5', '/LawApp/assets/images/livres/default-droit-international.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/les-traités-internationaux.pdf', 'PDF', NULL, '2020', 'Avancé', NULL, '660', 'Dalloz', 'Français', '9781349643538', NULL);
INSERT INTO `livres` VALUES ('43', 'Droit Européen', 'Pierre Lambert', 'Le droit de l\'Union Européenne.', '56.99', '5', '/LawApp/assets/images/livres/default-droit-international.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/droit-européen.pdf', 'PDF', NULL, '2021', 'Intermédiaire', NULL, '620', 'LexisNexis', 'Français', '9786221765537', NULL);
INSERT INTO `livres` VALUES ('44', 'Les Organisations Internationales', 'Claire Dupuis', 'Fonctionnement des OI.', '52.99', '5', '/LawApp/assets/images/livres/default-droit-international.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/les-organisations-internationales.pdf', 'PDF', NULL, '2022', 'Débutant', NULL, '456', 'Éditions Juridiques', 'Français', '9782135474652', NULL);
INSERT INTO `livres` VALUES ('45', 'Le Contrat de Travail', 'Laurent Martin', 'Tout sur le contrat de travail.', '47.99', '6', '/LawApp/assets/images/livres/default-droit-du-travail.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-contrat-de-travail.pdf', 'PDF', NULL, '2021', 'Débutant', NULL, '743', 'Éditions Juridiques', 'Français', '9783767689547', NULL);
INSERT INTO `livres` VALUES ('46', 'Le Droit Syndical', 'Marie Dumont', 'Les syndicats et leurs droits.', '44.99', '6', '/LawApp/assets/images/livres/default-droit-du-travail.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-droit-syndical.pdf', 'PDF', NULL, '2021', 'Intermédiaire', NULL, '377', 'LexisNexis', 'Français', '9782137110109', NULL);
INSERT INTO `livres` VALUES ('47', 'Les Conflits du Travail', 'Pierre Roussel', 'Gestion des conflits.', '42.99', '6', '/LawApp/assets/images/livres/default-droit-du-travail.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/les-conflits-du-travail.pdf', 'PDF', NULL, '2023', 'Débutant', NULL, '711', 'Larcier', 'Français', '9781066553019', NULL);
INSERT INTO `livres` VALUES ('48', 'La Protection Sociale', 'Sophie Bernard', 'Système de protection sociale.', '46.99', '6', '/LawApp/assets/images/livres/default-droit-du-travail.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/la-protection-sociale.pdf', 'PDF', NULL, '2021', 'Débutant', NULL, '671', 'Dalloz', 'Français', '9788192083108', NULL);
INSERT INTO `livres` VALUES ('49', 'Le Licenciement', 'Jean Dupuis', 'Procédures de licenciement.', '43.99', '6', '/LawApp/assets/images/livres/default-droit-du-travail.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-licenciement.pdf', 'PDF', NULL, '2021', 'Débutant', NULL, '435', 'Dalloz', 'Français', '9783665862791', NULL);
INSERT INTO `livres` VALUES ('50', 'Le Droit Public', 'Claire Lambert', 'Introduction au droit public.', '49.99', '7', '/LawApp/assets/images/livres/default-droit-administratif.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-droit-public.pdf', 'PDF', NULL, '2025', 'Avancé', NULL, '219', 'Dalloz', 'Français', '9781638737892', NULL);
INSERT INTO `livres` VALUES ('51', 'Les Actes Administratifs', 'Thomas Martin', 'Nature et régime juridique.', '47.99', '7', '/LawApp/assets/images/livres/default-droit-administratif.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/les-actes-administratifs.pdf', 'PDF', NULL, '2024', 'Intermédiaire', NULL, '773', 'Éditions Juridiques', 'Français', '9789692161352', NULL);
INSERT INTO `livres` VALUES ('52', 'Le Contentieux Administratif', 'Marie Simon', 'Procédures contentieuses.', '52.99', '7', '/LawApp/assets/images/livres/default-droit-administratif.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-contentieux-administratif.pdf', 'PDF', NULL, '2024', 'Débutant', NULL, '749', 'Dalloz', 'Français', '9789011929277', NULL);
INSERT INTO `livres` VALUES ('53', 'Les Services Publics', 'Pierre Dubois', 'Organisation et fonctionnement.', '46.99', '7', '/LawApp/assets/images/livres/default-droit-administratif.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/les-services-publics.pdf', 'PDF', NULL, '2024', 'Avancé', NULL, '217', 'LexisNexis', 'Français', '9787565684189', NULL);
INSERT INTO `livres` VALUES ('54', 'La Responsabilité Administrative', 'Sophie Leroy', 'Étude de la responsabilité.', '48.99', '7', '/LawApp/assets/images/livres/default-droit-administratif.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/la-responsabilité-administrative.pdf', 'PDF', NULL, '2024', 'Avancé', NULL, '683', 'LexisNexis', 'Français', '9788265156497', NULL);
INSERT INTO `livres` VALUES ('55', 'La Fiscalité des Entreprises', 'Jean-Paul Martin', 'Guide fiscal des entreprises.', '57.99', '8', '/LawApp/assets/images/livres/default-droit-fiscal.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/la-fiscalité-des-entreprises.pdf', 'PDF', NULL, '2020', 'Intermédiaire', NULL, '384', 'Éditions Juridiques', 'Français', '9781088542794', NULL);
INSERT INTO `livres` VALUES ('56', 'L\'Impôt sur le Revenu', 'Marie Roussel', 'Comprendre l\'IR.', '44.99', '8', '/LawApp/assets/images/livres/default-droit-fiscal.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/l\'impôt-sur-le-revenu.pdf', 'PDF', NULL, '2021', 'Intermédiaire', NULL, '520', 'LGDJ', 'Français', '9787936766320', NULL);
INSERT INTO `livres` VALUES ('57', 'La TVA', 'Pierre Moreau', 'Tout sur la TVA.', '46.99', '8', '/LawApp/assets/images/livres/default-droit-fiscal.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/la-tva.pdf', 'PDF', NULL, '2023', 'Avancé', NULL, '209', 'Dalloz', 'Français', '9787606638047', NULL);
INSERT INTO `livres` VALUES ('58', 'Le Contrôle Fiscal', 'Sophie Dupont', 'Procédures de contrôle.', '49.99', '8', '/LawApp/assets/images/livres/default-droit-fiscal.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/le-contrôle-fiscal.pdf', 'PDF', NULL, '2021', 'Avancé', NULL, '333', 'Éditions Juridiques', 'Français', '9782165285569', NULL);
INSERT INTO `livres` VALUES ('59', 'La Fiscalité Internationale', 'Thomas Bernard', 'Aspects internationaux.', '54.99', '8', '/LawApp/assets/images/livres/default-droit-fiscal.jpg', 'publie', '2025-05-14 12:12:29', '2025-05-14 12:12:29', '/LawApp/assets/documents/la-fiscalité-internationale.pdf', 'PDF', NULL, '2020', 'Avancé', NULL, '436', 'Dalloz', 'Français', '9787909341810', NULL);

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cours` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre_affichage` int(11) DEFAULT 0,
  `statut` varchar(50) DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_cours` (`id_cours`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `modules` VALUES ('1', '1', 'Les Sources du Droit Civil', 'Introduction aux diffÚrentes sources du droit civil : lois, jurisprudence, doctrine', '1', 'publie', '2025-05-13 23:36:55', '2025-05-13 23:36:55', '0');
INSERT INTO `modules` VALUES ('2', '1', 'Les Personnes en Droit Civil', '╔tude des personnes physiques et morales, de la personnalitÚ juridique et de la capacitÚ', '2', 'publie', '2025-05-13 23:37:18', '2025-05-13 23:37:18', '0');
INSERT INTO `modules` VALUES ('3', '3', 'Les Fondamentaux', 'Description du module Les Fondamentaux', '0', 'brouillon', '2025-05-14 12:20:04', '2025-05-14 12:20:04', '1');
INSERT INTO `modules` VALUES ('4', '4', 'Les Fondamentaux', 'Description du module Les Fondamentaux', '0', 'brouillon', '2025-05-14 12:20:46', '2025-05-14 12:20:46', '1');
INSERT INTO `modules` VALUES ('5', '5', 'Les Fondamentaux', 'Description du module Les Fondamentaux', '0', 'brouillon', '2025-05-14 12:21:26', '2025-05-14 12:21:26', '1');
INSERT INTO `modules` VALUES ('6', '5', 'Les Obligations', 'Description du module Les Obligations', '0', 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', '2');
INSERT INTO `modules` VALUES ('7', '6', 'Principes Généraux', 'Description du module Principes Généraux', '0', 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', '1');
INSERT INTO `modules` VALUES ('8', '6', 'Les Peines', 'Description du module Les Peines', '0', 'brouillon', '2025-05-14 12:21:29', '2025-05-14 12:21:29', '2');
INSERT INTO `modules` VALUES ('9', '7', 'Les Différentes Formes de Sociétés', 'Module complet sur Les Différentes Formes de Sociétés', '0', 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', '1');
INSERT INTO `modules` VALUES ('10', '7', 'Gestion des Sociétés', 'Module complet sur Gestion des Sociétés', '0', 'brouillon', '2025-05-14 12:24:55', '2025-05-14 12:24:55', '2');
INSERT INTO `modules` VALUES ('11', '8', 'Le Contrat de Travail', 'Module complet sur Le Contrat de Travail', '0', 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', '1');
INSERT INTO `modules` VALUES ('12', '8', 'Rupture du Contrat', 'Module complet sur Rupture du Contrat', '0', 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', '2');
INSERT INTO `modules` VALUES ('13', '9', 'Impôt sur les Sociétés', 'Module complet sur Impôt sur les Sociétés', '0', 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', '1');
INSERT INTO `modules` VALUES ('14', '9', 'TVA', 'Module complet sur TVA', '0', 'brouillon', '2025-05-14 12:24:56', '2025-05-14 12:24:56', '2');
INSERT INTO `modules` VALUES ('15', '10', 'Contrats Internationaux', 'Module complet sur Contrats Internationaux', '0', 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', '1');
INSERT INTO `modules` VALUES ('16', '10', 'Douanes et Changes', 'Module complet sur Douanes et Changes', '0', 'brouillon', '2025-05-14 12:24:57', '2025-05-14 12:24:57', '2');

DROP TABLE IF EXISTS `podcasts`;
CREATE TABLE `podcasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `url_audio` varchar(255) NOT NULL,
  `duree` varchar(10) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `id_createur` int(11) DEFAULT NULL,
  `statut` enum('brouillon','publie') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `audio_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `nombre_ecoutes` int(11) DEFAULT 0,
  `format` varchar(10) DEFAULT 'MP3',
  `taille_fichier` int(11) DEFAULT 0,
  `est_premium` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_categorie` (`id_categorie`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `podcasts_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories_podcasts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `podcasts_ibfk_2` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `podcasts` VALUES ('1', 'Les NouveautÚs du Droit Civil 2025', 'Discussion sur les changements rÚcents en droit civil', 'https://example.com/podcast1.mp3', '35:00', '1', '1', 'publie', '2025-05-14 00:06:50', '2025-05-14 00:06:50', NULL, NULL, '0', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('2', 'Analyse : L\'Affaire Martin vs Dupont', '╔tude dÚtaillÚe d\'un cas juridique marquant', 'https://example.com/podcast2.mp3', '45:00', '2', '1', 'publie', '2025-05-14 00:06:50', '2025-05-14 00:06:50', NULL, NULL, '0', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('3', 'Interview : Me Sophie Durant', 'Entretien avec une avocate spÚcialisÚe en droit des affaires', 'https://example.com/podcast3.mp3', '60:00', '3', '1', 'publie', '2025-05-14 00:06:50', '2025-05-14 00:06:50', NULL, NULL, '0', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('4', 'Réforme du Code Civil : Ce qui change', 'Les principales modifications apportées par la réforme du Code Civil', '', '25', '4', NULL, 'publie', '2025-05-14 12:22:53', '2025-05-14 12:22:53', '/LawApp/assets/audio/réforme-du-code-civil-:-ce-qui-change.mp3', '/LawApp/assets/images/podcasts/réforme-du-code-civil-:-ce-qui-change.jpg', '284', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('5', 'Nouveautés en Droit du Travail', 'Les dernières évolutions du droit du travail', '', '30', '4', NULL, 'publie', '2025-05-14 12:22:54', '2025-05-14 12:22:54', '/LawApp/assets/audio/nouveautés-en-droit-du-travail.mp3', '/LawApp/assets/images/podcasts/nouveautés-en-droit-du-travail.jpg', '928', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('6', 'L\'actualité du Droit des Sociétés', 'Les changements récents en droit des sociétés', '', '20', '4', NULL, 'publie', '2025-05-14 12:22:54', '2025-05-14 12:22:54', '/LawApp/assets/audio/l\'actualité-du-droit-des-sociétés.mp3', '/LawApp/assets/images/podcasts/l\'actualité-du-droit-des-sociétés.jpg', '225', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('7', 'Analyse d\'un Cas de Licenciement', 'Étude détaillée d\'un cas de licenciement abusif', '', '35', '5', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/analyse-d\'un-cas-de-licenciement.mp3', '/LawApp/assets/images/podcasts/analyse-d\'un-cas-de-licenciement.jpg', '383', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('8', 'Résolution d\'un Litige Commercial', 'Comment résoudre un conflit entre entreprises', '', '40', '5', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/résolution-d\'un-litige-commercial.mp3', '/LawApp/assets/images/podcasts/résolution-d\'un-litige-commercial.jpg', '346', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('9', 'Cas Pratique en Droit de la Famille', 'Analyse d\'une procédure de divorce complexe', '', '30', '5', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/cas-pratique-en-droit-de-la-famille.mp3', '/LawApp/assets/images/podcasts/cas-pratique-en-droit-de-la-famille.jpg', '165', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('10', 'Interview d\'un Juge aux Affaires Familiales', 'Un juge partage son expérience quotidienne', '', '45', '3', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/interview-d\'un-juge-aux-affaires-familiales.mp3', '/LawApp/assets/images/podcasts/interview-d\'un-juge-aux-affaires-familiales.jpg', '552', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('11', 'Rencontre avec un Avocat Pénaliste', 'Discussion sur les défis du droit pénal', '', '50', '3', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/rencontre-avec-un-avocat-pénaliste.mp3', '/LawApp/assets/images/podcasts/rencontre-avec-un-avocat-pénaliste.jpg', '874', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('12', 'Parole à un Notaire', 'Un notaire explique les successions', '', '40', '3', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/parole-à-un-notaire.mp3', '/LawApp/assets/images/podcasts/parole-à-un-notaire.jpg', '271', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('13', 'Comment Créer son Entreprise', 'Guide pratique pour les entrepreneurs', '', '35', '6', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/comment-créer-son-entreprise.mp3', '/LawApp/assets/images/podcasts/comment-créer-son-entreprise.jpg', '815', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('14', 'Les Pièges à Éviter en Droit Immobilier', 'Conseils pour vos transactions immobilières', '', '30', '6', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/les-pièges-à-Éviter-en-droit-immobilier.mp3', '/LawApp/assets/images/podcasts/les-pièges-à-Éviter-en-droit-immobilier.jpg', '729', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('15', 'Protéger ses Droits d\'Auteur', 'Guide de la propriété intellectuelle', '', '25', '6', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/protéger-ses-droits-d\'auteur.mp3', '/LawApp/assets/images/podcasts/protéger-ses-droits-d\'auteur.jpg', '93', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('16', 'La Justice Prédictive', 'Débat sur l\'utilisation de l\'IA en droit', '', '55', '7', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/la-justice-prédictive.mp3', '/LawApp/assets/images/podcasts/la-justice-prédictive.jpg', '188', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('17', 'Réforme de la Justice', 'Discussion sur la modernisation de la justice', '', '45', '7', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/réforme-de-la-justice.mp3', '/LawApp/assets/images/podcasts/réforme-de-la-justice.jpg', '168', 'MP3', '0', '0', NULL);
INSERT INTO `podcasts` VALUES ('18', 'L\'Avenir de la Profession d\'Avocat', 'Débat sur l\'évolution du métier', '', '50', '7', NULL, 'publie', '2025-05-14 12:22:55', '2025-05-14 12:22:55', '/LawApp/assets/audio/l\'avenir-de-la-profession-d\'avocat.mp3', '/LawApp/assets/images/podcasts/l\'avenir-de-la-profession-d\'avocat.jpg', '164', 'MP3', '0', '0', NULL);

DROP TABLE IF EXISTS `progression_utilisateurs`;
CREATE TABLE `progression_utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_cours` int(11) DEFAULT NULL,
  `id_module` int(11) DEFAULT NULL,
  `id_lecon` int(11) DEFAULT NULL,
  `statut` enum('non_commence','en_cours','termine') DEFAULT 'non_commence',
  `progression` int(11) DEFAULT 0,
  `date_debut` datetime DEFAULT current_timestamp(),
  `date_derniere_activite` datetime DEFAULT current_timestamp(),
  `date_completion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_progression` (`id_utilisateur`,`id_cours`,`id_module`,`id_lecon`),
  KEY `id_cours` (`id_cours`),
  KEY `id_module` (`id_module`),
  KEY `id_lecon` (`id_lecon`),
  CONSTRAINT `progression_utilisateurs_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progression_utilisateurs_ibfk_2` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progression_utilisateurs_ibfk_3` FOREIGN KEY (`id_module`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progression_utilisateurs_ibfk_4` FOREIGN KEY (`id_lecon`) REFERENCES `lecons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `progression_utilisateurs` VALUES ('1', '2', '1', '1', '1', 'en_cours', '0', '2025-05-14 11:52:57', '2025-05-14 11:53:14', NULL);

DROP TABLE IF EXISTS `quiz`;
CREATE TABLE `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lecon` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duree_limite` int(11) DEFAULT NULL,
  `nombre_questions` int(11) DEFAULT 0,
  `score_minimum` int(11) DEFAULT 70,
  `statut` enum('brouillon','publie') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_lecon` (`id_lecon`),
  CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`id_lecon`) REFERENCES `lecons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quiz` VALUES ('1', '1', 'Quiz de test - Introduction au Droit', 'Un quiz pour tester vos connaissances en droit', '30', '10', '70', 'brouillon', '2025-05-14 01:41:43', '2025-05-14 01:41:43');

DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_quiz` int(11) NOT NULL,
  `question` text NOT NULL,
  `type` enum('qcm','vrai_faux','texte') DEFAULT 'qcm',
  `points` int(11) DEFAULT 1,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_quiz` (`id_quiz`),
  CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_reponses`;
CREATE TABLE `quiz_reponses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_question` int(11) NOT NULL,
  `reponse` text NOT NULL,
  `est_correcte` tinyint(1) DEFAULT 0,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_question` (`id_question`),
  CONSTRAINT `quiz_reponses_ibfk_1` FOREIGN KEY (`id_question`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_reponses_utilisateurs`;
CREATE TABLE `quiz_reponses_utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_resultat` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_reponse` int(11) DEFAULT NULL,
  `reponse_texte` text DEFAULT NULL COMMENT 'Pour les questions à réponse courte',
  `est_correcte` tinyint(1) NOT NULL DEFAULT 0,
  `points_obtenus` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_resultat` (`id_resultat`),
  KEY `id_question` (`id_question`),
  KEY `id_reponse` (`id_reponse`),
  CONSTRAINT `quiz_reponses_utilisateurs_ibfk_1` FOREIGN KEY (`id_resultat`) REFERENCES `quiz_resultats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_reponses_utilisateurs_ibfk_2` FOREIGN KEY (`id_question`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_reponses_utilisateurs_ibfk_3` FOREIGN KEY (`id_reponse`) REFERENCES `quiz_reponses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `quiz_resultats`;
CREATE TABLE `quiz_resultats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_quiz` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `temps_passe` int(11) DEFAULT NULL COMMENT 'Temps passé en secondes',
  `date_debut` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_fin` timestamp NULL DEFAULT NULL,
  `statut` enum('en_cours','termine','abandonne') NOT NULL DEFAULT 'en_cours',
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_quiz` (`id_quiz`),
  CONSTRAINT `quiz_resultats_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_resultats_ibfk_2` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `resultats_quiz`;
CREATE TABLE `resultats_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_quiz` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `temps_pris` int(11) DEFAULT NULL,
  `date_debut` datetime DEFAULT current_timestamp(),
  `date_fin` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_quiz` (`id_quiz`),
  CONSTRAINT `resultats_quiz_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resultats_quiz_ibfk_2` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user_badges`;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_badge` int(11) NOT NULL,
  `date_obtention` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_badge` (`id_utilisateur`,`id_badge`),
  KEY `id_badge` (`id_badge`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`id_badge`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `user_favoris`;
CREATE TABLE `user_favoris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favori` (`id_utilisateur`,`id_cours`),
  KEY `id_cours` (`id_cours`),
  CONSTRAINT `user_favoris_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_favoris_ibfk_2` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `user_progression`;
CREATE TABLE `user_progression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `id_lecon` int(11) DEFAULT NULL,
  `progression` int(11) DEFAULT 0,
  `statut` enum('non_commence','en_cours','complete') DEFAULT 'non_commence',
  `derniere_activite` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_progression` (`id_utilisateur`,`id_cours`,`id_lecon`),
  KEY `id_cours` (`id_cours`),
  KEY `id_lecon` (`id_lecon`),
  CONSTRAINT `user_progression_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_progression_ibfk_2` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_progression_ibfk_3` FOREIGN KEY (`id_lecon`) REFERENCES `lecons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `user_quiz_resultats`;
CREATE TABLE `user_quiz_resultats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) NOT NULL,
  `id_quiz` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `id_reponse` int(11) DEFAULT NULL,
  `reponse_texte` text DEFAULT NULL,
  `est_correcte` tinyint(1) DEFAULT NULL,
  `points_obtenus` int(11) DEFAULT 0,
  `date_reponse` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_quiz` (`id_quiz`),
  KEY `id_question` (`id_question`),
  KEY `id_reponse` (`id_reponse`),
  CONSTRAINT `user_quiz_resultats_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_quiz_resultats_ibfk_2` FOREIGN KEY (`id_quiz`) REFERENCES `quiz` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_quiz_resultats_ibfk_3` FOREIGN KEY (`id_question`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_quiz_resultats_ibfk_4` FOREIGN KEY (`id_reponse`) REFERENCES `quiz_reponses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('utilisateur','admin') DEFAULT 'utilisateur',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  `type_compte` enum('etudiant','professeur','admin') DEFAULT 'etudiant',
  `statut` enum('actif','inactif') DEFAULT 'actif',
  `photo_profil` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `receive_email_notifications` tinyint(1) DEFAULT 1,
  `ui_theme` varchar(10) DEFAULT 'light',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `utilisateurs` VALUES ('1', 'Admin', 'System', 'admin@lawapp.com', '.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-05-13 23:55:52', '2025-05-13 23:55:52', '2025-05-14 11:45:17', NULL, 'etudiant', 'actif', NULL, NULL, NULL, '1', 'light');
INSERT INTO `utilisateurs` VALUES ('2', 'mosei', 'EPHRAIME', 'LawApp@4gmail.com', '$2y$10$mcjLcf/KLX/19oXpd/d/LeSuePQ.xKRfc55.7TNyimd9BTMP.hvA6', 'utilisateur', '2025-05-14 11:47:20', '2025-05-14 11:53:14', '2025-05-14 11:47:20', '2025-05-14 11:53:14', 'etudiant', 'actif', NULL, NULL, NULL, '1', 'light');

DROP TABLE IF EXISTS `videos`;
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `url_video` varchar(255) NOT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `duree` varchar(10) DEFAULT NULL,
  `niveau` enum('debutant','intermediaire','avance') DEFAULT 'intermediaire',
  `statut` enum('brouillon','publie') DEFAULT 'brouillon',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_createur` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_categorie` (`id_categorie`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories_videos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `videos_ibfk_2` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `videos` VALUES ('1', 'Introduction au Droit Civil', 'Une introduction complÞte aux concepts de base du droit civil', 'https://example.com/video1.mp4', '1', '45:00', 'debutant', 'publie', '2025-05-13 23:58:38', '2025-05-13 23:58:38', '1');

