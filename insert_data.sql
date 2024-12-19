USE hotel_reservation;

-- Insert sample hotels
INSERT INTO hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES
-- Rabat
('Sofitel Rabat Jardin des Roses', 'BP 450 Souissi, Rabat', 'Hôtel de luxe avec jardin andalou, restaurants gastronomiques et spa.', 'contact@sofitel-rabat.com', '+212 537-675656', 'www.sofitel-rabat.com'),
('Hotel Diwan Rabat', 'Place de l''Unité Africaine, Rabat', 'Situé au cœur de la ville, proche des monuments historiques.', 'contact@diwanrabat.com', '+212 537-262727', 'www.diwanrabat.com'),
('Riad Dar El Kebira', 'medina, Rabat', 'Riad traditionnel avec architecture authentique.', 'info@darelkebira.com', '+212 537-724906', 'www.riaddarelkebira.com'),

-- Casablanca
('Four Seasons Casablanca', 'Boulevard de la Corniche, Casablanca', 'Hôtel moderne avec vue sur l''océan.', 'info@fscasablanca.com', '+212 529-073700', 'www.fourseasons.com/casablanca'),
('Hyatt Regency Casablanca', 'Place des Nations Unies, Casablanca', 'Au cœur du quartier d''affaires.', 'casablanca.regency@hyatt.com', '+212 522-431234', 'www.hyatt.com'),
('Movenpick Hotel Casablanca', 'Rond-point Hassan II, Casablanca', 'Vue panoramique sur la ville.', 'hotel.casablanca@movenpick.com', '+212 522-520520', 'www.movenpick.com'),

-- Marrakech
('La Mamounia', 'Avenue Bab Jdid, Marrakech', 'Palace historique avec jardins luxuriants.', 'info@mamounia.com', '+212 524-388600', 'www.mamounia.com'),
('Royal Mansour Marrakech', 'Rue Abou Abbas El Sebti, Marrakech', 'Riads privés de luxe.', 'contact@royalmansour.ma', '+212 529-808080', 'www.royalmansour.com'),
('Four Seasons Resort Marrakech', 'Avenue de la Menara, Marrakech', 'Oasis moderne avec spa.', 'info@fsmarrakech.com', '+212 524-359200', 'www.fourseasons.com/marrakech'),

-- Fès
('Riad Fès', 'Derb Ben Slimane, Fès', 'Riad de luxe dans la médina.', 'contact@riadfes.com', '+212 535-947610', 'www.riadfes.com'),
('Palais Faraj Suites & Spa', 'Bab Ziat, Fès', 'Vue panoramique sur la médina.', 'info@palaisfaraj.com', '+212 535-635356', 'www.palaisfaraj.com'),
('Hotel Sahrai', 'Bab Lghoul, Fès', 'Design contemporain avec vue sur la médina.', 'info@hotelsahrai.com', '+212 535-940332', 'www.hotelsahrai.com'),

-- Tanger
('El Minzah Hotel', 'Rue de la Liberté, Tanger', 'Hôtel historique au style colonial.', 'reservation@elminzah.com', '+212 539-333444', 'www.elminzah.com'),
('Mövenpick Hotel & Casino Malabata', 'Route de Malabata, Tanger', 'Vue sur le détroit de Gibraltar.', 'hotel.tanger@movenpick.com', '+212 539-329300', 'www.movenpick.com'),
('Royal Tulip City Center', 'Place du Maghreb Arabe, Tanger', 'Au cœur du centre-ville.', 'info@royaltuliptanger.com', '+212 539-309500', 'www.royaltuliptanger.com'),

-- Agadir
('Sofitel Agadir Royal Bay Resort', 'Baie des Palmiers, Agadir', 'Resort de luxe en bord de mer.', 'H5707@sofitel.com', '+212 528-849999', 'www.sofitel-agadir.com'),
('Hyatt Place Taghazout Bay', 'Taghazout Bay, Agadir', 'Vue sur l''océan et les montagnes.', 'agadir.place@hyatt.com', '+212 528-876767', 'www.hyatt.com'),
('Atlas Amadil Beach', 'Boulevard 20 Août, Agadir', 'Accès direct à la plage.', 'contact@amadilbeach.com', '+212 528-847020', 'www.amadilbeach.com');

-- Insert rooms for each hotel (3 room types for each hotel)
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits)
SELECT 
    h.id_hotel,
    t.type_chambre,
    CASE 
        WHEN h.id_hotel <= 3 THEN -- Rabat hotels (more expensive)
            CASE 
                WHEN t.type_chambre = 'simple' THEN 1200
                WHEN t.type_chambre = 'double' THEN 1800
                ELSE 2500
            END
        WHEN h.id_hotel <= 6 THEN -- Casablanca hotels
            CASE 
                WHEN t.type_chambre = 'simple' THEN 1000
                WHEN t.type_chambre = 'double' THEN 1500
                ELSE 2200
            END
        WHEN h.id_hotel <= 9 THEN -- Marrakech hotels
            CASE 
                WHEN t.type_chambre = 'simple' THEN 1100
                WHEN t.type_chambre = 'double' THEN 1600
                ELSE 2300
            END
        WHEN h.id_hotel <= 12 THEN -- Fès hotels
            CASE 
                WHEN t.type_chambre = 'simple' THEN 900
                WHEN t.type_chambre = 'double' THEN 1400
                ELSE 2000
            END
        WHEN h.id_hotel <= 15 THEN -- Tanger hotels
            CASE 
                WHEN t.type_chambre = 'simple' THEN 800
                WHEN t.type_chambre = 'double' THEN 1300
                ELSE 1900
            END
        ELSE -- Agadir hotels
            CASE 
                WHEN t.type_chambre = 'simple' THEN 950
                WHEN t.type_chambre = 'double' THEN 1450
                ELSE 2100
            END
    END as prix,
    1 as disponibilite,
    CASE 
        WHEN t.type_chambre = 'simple' THEN 1
        ELSE 2
    END as nombre_lits
FROM hotels h
CROSS JOIN (
    SELECT 'simple' as type_chambre
    UNION SELECT 'double'
    UNION SELECT 'suite'
) t;

-- Insert sample admin (if not exists)
INSERT IGNORE INTO admin (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- Password: password123

-- Insert sample client (if not exists)
INSERT IGNORE INTO clients (nom, email, password, adresse, telephone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Test Street', '+212 6123-45678'); -- Password: password123
