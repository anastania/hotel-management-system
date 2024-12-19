-- Insert sample hotels
INSERT INTO hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES
('Hôtel Royal Mansour', 'Rue Abou Abbas El Sebti, 40000 Marrakech', 'Un palace luxueux au cœur de Marrakech offrant une expérience unique avec ses riads privés et son service personnalisé.', 'contact@royalmansour.ma', '+212 5242-38080', 'www.royalmansour.com'),
('La Mamounia', 'Avenue Bab Jdid, 40040 Marrakech', 'Un hôtel historique alliant luxe traditionnel marocain et confort moderne avec des jardins somptueux.', 'info@mamounia.com', '+212 5243-88600', 'www.mamounia.com'),
('Four Seasons Casablanca', '1 Boulevard de la Corniche, Casablanca', 'Un resort moderne en bord de mer offrant une vue imprenable sur l''océan Atlantique.', 'reservations.cas@fourseasons.com', '+212 5299-73000', 'www.fourseasons.com/casablanca'),
('Sofitel Rabat Jardin des Roses', 'BP 450 Souissi, Rabat', 'Un hôtel élégant niché dans un jardin andalou, au cœur de la capitale administrative.', 'H6813@sofitel.com', '+212 5376-75656', 'www.sofitel-rabat-jardindesroses.com'),
('Mazagan Beach Resort', 'El Jadida, 24000', 'Un resort balnéaire de luxe avec golf, casino et multiples restaurants face à l''océan.', 'contact@mazaganbeachresort.com', '+212 5233-88080', 'www.mazaganbeachresort.com');

-- Insert sample rooms for each hotel
-- Hotel 1: Royal Mansour
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) VALUES
(1, 'simple', 2500, 1, 1),
(1, 'double', 3500, 1, 2),
(1, 'suite', 5000, 1, 2);

-- Hotel 2: La Mamounia
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) VALUES
(2, 'simple', 2000, 1, 1),
(2, 'double', 3000, 1, 2),
(2, 'suite', 4500, 1, 2);

-- Hotel 3: Four Seasons
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) VALUES
(3, 'simple', 1800, 1, 1),
(3, 'double', 2800, 1, 2),
(3, 'suite', 4000, 1, 2);

-- Hotel 4: Sofitel
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) VALUES
(4, 'simple', 1500, 1, 1),
(4, 'double', 2500, 1, 2),
(4, 'suite', 3500, 1, 2);

-- Hotel 5: Mazagan
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) VALUES
(5, 'simple', 1700, 1, 1),
(5, 'double', 2700, 1, 2),
(5, 'suite', 3800, 1, 2);

-- Insert sample client
INSERT INTO clients (nom, email, password, adresse, telephone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Test Street', '+212 6123-45678');
