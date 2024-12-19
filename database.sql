-- Create database
CREATE DATABASE IF NOT EXISTS hotel_reservation;
USE hotel_reservation;

-- Create Hotels table
CREATE TABLE IF NOT EXISTS hotels (
    id_hotel INT PRIMARY KEY AUTO_INCREMENT,
    nom_hotel VARCHAR(100) NOT NULL,
    adresse TEXT NOT NULL,
    description TEXT,
    email VARCHAR(100),
    telephone VARCHAR(20),
    site_web VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Chambres table
CREATE TABLE IF NOT EXISTS chambres (
    id_chambre INT PRIMARY KEY AUTO_INCREMENT,
    id_hotel INT NOT NULL,
    type_chambre VARCHAR(50) NOT NULL,
    prix FLOAT NOT NULL,
    disponibilite BOOLEAN DEFAULT TRUE,
    nombre_lits INT NOT NULL,
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Clients table
CREATE TABLE IF NOT EXISTS clients (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Admin table
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    id_chambre INT NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client) ON DELETE CASCADE,
    FOREIGN KEY (id_chambre) REFERENCES chambres(id_chambre) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pour les paiements
CREATE TABLE IF NOT EXISTS payments (
    id_payment INT PRIMARY KEY AUTO_INCREMENT,
    id_reservation INT,
    montant DECIMAL(10,2),
    status VARCHAR(50),
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reservation) REFERENCES reservations(id_reservation)
);

-- Trigger to check room availability before reservation
DELIMITER //
CREATE TRIGGER check_room_availability
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE is_available BOOLEAN;
    
    -- Check if room is already booked for the given dates
    SELECT COUNT(*) = 0 INTO is_available
    FROM reservations
    WHERE id_chambre = NEW.id_chambre
    AND status = 'confirmed'
    AND (
        (NEW.date_arrivee BETWEEN date_arrivee AND date_depart)
        OR (NEW.date_depart BETWEEN date_arrivee AND date_depart)
        OR (date_arrivee BETWEEN NEW.date_arrivee AND NEW.date_depart)
    );
    
    IF NOT is_available THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La chambre n''est pas disponible pour ces dates';
    END IF;
END//

-- Trigger to update room availability after reservation
CREATE TRIGGER update_room_availability
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    UPDATE chambres
    SET disponibilite = FALSE
    WHERE id_chambre = NEW.id_chambre;
END//

-- Trigger to restore room availability after reservation cancellation
CREATE TRIGGER restore_room_availability
AFTER DELETE ON reservations
FOR EACH ROW
BEGIN
    UPDATE chambres
    SET disponibilite = TRUE
    WHERE id_chambre = OLD.id_chambre;
END//

-- Function to check room availability
CREATE FUNCTION check_room_availability_func(
    p_id_chambre INT,
    p_date_arrivee DATE,
    p_date_depart DATE
) RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE is_available BOOLEAN;
    
    SELECT COUNT(*) = 0 INTO is_available
    FROM reservations
    WHERE id_chambre = p_id_chambre
    AND status = 'confirmed'
    AND (
        (p_date_arrivee BETWEEN date_arrivee AND date_depart)
        OR (p_date_depart BETWEEN date_arrivee AND date_depart)
        OR (date_arrivee BETWEEN p_date_arrivee AND p_date_depart)
    );
    
    RETURN is_available;
END//

-- Function to calculate reservation total amount
CREATE FUNCTION calculate_reservation_amount(
    p_id_chambre INT,
    p_date_arrivee DATE,
    p_date_depart DATE
) RETURNS FLOAT
DETERMINISTIC
BEGIN
    DECLARE total_amount FLOAT;
    DECLARE price_per_night FLOAT;
    DECLARE num_nights INT;
    
    -- Get room price
    SELECT prix INTO price_per_night
    FROM chambres
    WHERE id_chambre = p_id_chambre;
    
    -- Calculate number of nights
    SET num_nights = DATEDIFF(p_date_depart, p_date_arrivee);
    
    -- Calculate total amount
    SET total_amount = price_per_night * num_nights;
    
    RETURN total_amount;
END//

-- Procedure to add a reservation
CREATE PROCEDURE add_reservation(
    IN p_id_client INT,
    IN p_id_chambre INT,
    IN p_date_arrivee DATE,
    IN p_date_depart DATE
)
BEGIN
    IF check_room_availability_func(p_id_chambre, p_date_arrivee, p_date_depart) THEN
        INSERT INTO reservations (id_client, id_chambre, date_arrivee, date_depart)
        VALUES (p_id_client, p_id_chambre, p_date_arrivee, p_date_depart);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La chambre n''est pas disponible pour ces dates';
    END IF;
END//

-- Procedure to cancel a reservation
CREATE PROCEDURE cancel_reservation(
    IN p_id_reservation INT
)
BEGIN
    DELETE FROM reservations
    WHERE id_reservation = p_id_reservation;
END//

-- Procedure to generate occupation report
CREATE PROCEDURE generate_occupation_report(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        h.nom_hotel,
        COUNT(r.id_reservation) as total_reservations,
        SUM(calculate_reservation_amount(r.id_chambre, r.date_arrivee, r.date_depart)) as total_revenue
    FROM hotels h
    LEFT JOIN chambres c ON h.id_hotel = c.id_hotel
    LEFT JOIN reservations r ON c.id_chambre = r.id_chambre
    WHERE r.date_arrivee BETWEEN p_start_date AND p_end_date
    GROUP BY h.id_hotel;
END//

-- Insert sample hotels for major Moroccan cities
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

-- Insert rooms for each hotel
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) 
SELECT 
    h.id_hotel,
    type_chambre,
    CASE 
        WHEN type_chambre = 'simple' THEN prix_base
        WHEN type_chambre = 'double' THEN prix_base * 1.5
        WHEN type_chambre = 'suite' THEN prix_base * 2.5
    END as prix,
    1 as disponibilite,
    CASE 
        WHEN type_chambre = 'simple' THEN 1
        WHEN type_chambre = 'double' THEN 2
        WHEN type_chambre = 'suite' THEN 2
    END as nombre_lits
FROM hotels h
CROSS JOIN (
    SELECT 'simple' as type_chambre, 1000 as prix_base
    UNION SELECT 'double', 1000
    UNION SELECT 'suite', 1000
) types
WHERE h.id_hotel NOT IN (SELECT DISTINCT id_hotel FROM chambres);

DELIMITER ;
