-- ============================================
-- NOVA Marketplace - Database Schema
-- Premium Tech Marketplace
-- ============================================

DROP DATABASE IF EXISTS nova_db;
CREATE DATABASE nova_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nova_db;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 500.00,
    photo VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Categories Table
-- ============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255)
) ENGINE=InnoDB;

-- ============================================
-- Articles Table (Products listed by users)
-- ============================================
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    categorie_id INT DEFAULT NULL,
    auteur_id INT NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Stock Table (Separate from articles per PDF)
-- ============================================
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL UNIQUE,
    quantite INT DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Cart Table
-- ============================================
CREATE TABLE panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    article_id INT NOT NULL,
    quantite INT DEFAULT 1,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Invoices Table (Factures)
-- ============================================
CREATE TABLE factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10,2) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Invoice Details Table
-- ============================================
CREATE TABLE facture_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Comments / Reviews Table (Bonus)
-- ============================================
CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    note INT NOT NULL CHECK(note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Favorites Table (Bonus)
-- ============================================
CREATE TABLE favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favori (user_id, article_id)
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Admin user (password: password)
INSERT INTO users (username, email, password, balance, role) VALUES
('admin', 'admin@nova.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 10000.00, 'admin'),
('alice_tech', 'alice@nova.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2500.00, 'user'),
('bob_audio', 'bob@nova.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1800.00, 'user');

-- Categories
INSERT INTO categories (nom, description, image) VALUES
('Audio', 'Casques, enceintes et écouteurs premium', 'audio.jpg'),
('Gaming', 'Équipement gaming haute performance', 'gaming.jpg'),
('Smart Home', 'Objets connectés pour la maison', 'smarthome.jpg'),
('Accessoires', 'Accessoires tech et lifestyle', 'accessoires.jpg'),
('Photo & Vidéo', 'Caméras, drones et équipement créatif', 'photo.jpg'),
('Wearables', 'Montres et objets portables connectés', 'wearables.jpg');

-- Articles (listed by different users)
INSERT INTO articles (nom, description, prix, image, categorie_id, auteur_id) VALUES
('Nova Pro Headphones', 'Casque sans fil à réduction de bruit active. Son spatial immersif avec drivers 40mm en titane. Autonomie 30h. Design ultra-confortable avec coussinets en mousse à mémoire de forme.', 349.99, 'headphones-pro.jpg', 1, 1),
('Pulse Speaker', 'Enceinte Bluetooth portable avec son 360°. Résistante à l''eau IPX7, basses profondes et LED ambiantes synchronisées à la musique. 24h d''autonomie.', 199.99, 'pulse-speaker.jpg', 1, 2),
('AirBuds Ultra', 'Écouteurs true wireless avec ANC adaptatif. Codec LDAC, 8h d''écoute + 32h avec le boîtier. Résistance IPX5 pour le sport.', 179.99, 'airbuds-ultra.jpg', 1, 1),
('Nexus Controller', 'Manette de jeu sans fil avec retour haptique avancé. Compatible PC, Console et Mobile. Triggers adaptatifs et gyroscope intégré.', 79.99, 'nexus-controller.jpg', 2, 3),
('Titan Keyboard RGB', 'Clavier mécanique sans fil avec switches optiques. Éclairage RGB par touche, châssis aluminium anodisé, autonomie 200h.', 159.99, 'titan-keyboard.jpg', 2, 2),
('Orbit Gaming Mouse', 'Souris gaming ultra-légère 58g. Capteur 25K DPI, switches optiques, câble paracord ou mode sans fil 2.4GHz.', 89.99, 'orbit-mouse.jpg', 2, 1),
('Echo Hub', 'Hub domotique central avec écran tactile 10 pouces. Contrôle Zigbee, Z-Wave et WiFi. Assistant vocal intégré.', 249.99, 'echo-hub.jpg', 3, 1),
('Lux Smart Bulb Pack', 'Pack de 4 ampoules connectées E27, 16 millions de couleurs. Compatible tous assistants. Programmation et scènes automatisées.', 69.99, 'smart-bulb.jpg', 3, 3),
('MagCharge Pad', 'Station de charge magnétique 3-en-1 : smartphone, montre, écouteurs. Charge rapide 15W Qi2. Design minimaliste en aluminium.', 99.99, 'magcharge.jpg', 4, 2),
('Carbon Case Pro', 'Coque en fibre de carbone véritable pour smartphone. Ultra-fine 0.6mm, protection MIL-STD 810G, compatible charge sans fil.', 49.99, 'carbon-case.jpg', 4, 1),
('SkyView Drone 4K', 'Drone compact avec caméra 4K stabilisée sur 3 axes. Autonomie 35 min, portée 10 km, détection d''obstacles 360°.', 899.99, 'skyview-drone.jpg', 5, 3),
('LensMaster Webcam', 'Webcam 4K avec autofocus, HDR et micro stéréo intégré. Cadrage automatique par IA. Idéale pour le streaming et les visioconférences.', 149.99, 'lensmaster.jpg', 5, 2),
('Pulse Watch Pro', 'Montre connectée avec suivi santé avancé : ECG, SpO2, température. Écran AMOLED 1.5", 5 jours d''autonomie, GPS intégré.', 299.99, 'pulse-watch.jpg', 6, 1),
('FitBand Ultra', 'Bracelet connecté ultra-léger avec suivi d''activité 24/7. Écran OLED, résistance 5ATM, 14 jours d''autonomie.', 59.99, 'fitband.jpg', 6, 2);

-- Stock
INSERT INTO stock (article_id, quantite) VALUES
(1, 50), (2, 35), (3, 80), (4, 120), (5, 45),
(6, 65), (7, 20), (8, 100), (9, 55), (10, 200),
(11, 15), (12, 40), (13, 30), (14, 90);

-- Some reviews
INSERT INTO commentaires (article_id, user_id, note, commentaire) VALUES
(1, 2, 5, 'Son incroyable, la réduction de bruit est exceptionnelle. Meilleur casque que j''ai eu.'),
(1, 3, 4, 'Très bon casque, confortable pour de longues sessions. Le son spatial est bluffant.'),
(2, 3, 5, 'Qualité sonore impressionnante pour une enceinte portable. Les LED sont un plus sympa.'),
(3, 2, 4, 'Bonne qualité audio et ANC efficace. Le boîtier est un peu gros cependant.'),
(5, 3, 5, 'Clavier magnifique, les switches sont ultra réactifs. Le RGB est bien intégré.'),
(11, 2, 5, 'Images à couper le souffle, vol stable même par vent modéré. La batterie tient bien.'),
(13, 3, 4, 'Montre très complète, l''écran est superbe. L''autonomie pourrait être meilleure.');
