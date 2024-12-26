-- Add more hotels in Moroccan cities
INSERT INTO hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES
-- Marrakech Hotels
('Riad Royal Marrakech', 'Médina, Marrakech', 'Un riad authentique au cœur de la médina avec piscine et spa traditionnel.', 'contact@riadroyal.ma', '+212 524 456789', 'www.riadroyal.ma'),
('Palm Plaza Marrakech', 'Zone Agdal, Marrakech', 'Hôtel de luxe avec vue sur l''Atlas, plusieurs restaurants et un spa.', 'info@palmplaza.ma', '+212 524 567890', 'www.palmplaza.ma'),
('Atlas Medina Marrakech', 'Hivernage, Marrakech', 'Un mélange parfait entre modernité et tradition marocaine.', 'reservation@atlasmedina.ma', '+212 524 678901', 'www.atlasmedina.ma'),

-- Tanger Hotels
('Hilton Garden Tanger', 'City Center, Tanger', 'Vue panoramique sur le détroit de Gibraltar et chambres modernes.', 'info@hgtanger.com', '+212 539 234567', 'www.hiltongardeninn.com'),
('Marina Bay Tanger', 'Port de Tanger, Tanger', 'Hôtel en front de mer avec accès direct à la marina.', 'contact@marinabay.ma', '+212 539 345678', 'www.marinabay.ma'),
('Farah Tanger', 'Boulevard Plage, Tanger', 'Élégant hôtel avec vue sur la mer et restaurants gastronomiques.', 'reservation@farahtanger.ma', '+212 539 456789', 'www.farahtanger.ma'),

-- Rabat Hotels
('Tour Hassan Palace', 'Centre-ville, Rabat', 'Palace historique avec jardins luxuriants et service personnalisé.', 'contact@tourhassan.ma', '+212 537 234567', 'www.tourhassan.ma'),
('Diwan Rabat', 'Hassan, Rabat', 'Hôtel contemporain près des sites historiques.', 'info@diwanrabat.ma', '+212 537 345678', 'www.diwanrabat.ma'),
('La Villa Mandarine', 'Souissi, Rabat', 'Boutique hôtel dans un jardin d''orangers avec piscine.', 'reservation@villamandarine.ma', '+212 537 456789', 'www.villamandarine.ma'),

-- Casablanca Hotels
('Kenzi Tower', 'Twin Center, Casablanca', 'Gratte-ciel luxueux avec vue panoramique sur l''océan.', 'info@kenzitower.ma', '+212 522 234567', 'www.kenzitower.ma'),
('Sofitel Casablanca', 'Corniche, Casablanca', 'Élégance française et hospitalité marocaine face à l''océan.', 'contact@sofitelcasa.ma', '+212 522 345678', 'www.sofitelcasa.ma'),
('Hyatt Regency Casa', 'Place des Nations Unies, Casablanca', 'Luxe contemporain au cœur du quartier des affaires.', 'reservation@hyattcasa.ma', '+212 522 456789', 'www.hyattregency.com');

-- Add rooms for each new hotel
INSERT INTO chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits) 
SELECT 
    h.id_hotel,
    type_chambre,
    CASE 
        WHEN type_chambre = 'Standard' THEN prix * 0.9
        WHEN type_chambre = 'Deluxe' THEN prix * 1.1
        WHEN type_chambre = 'Suite' THEN prix * 1.3
        ELSE prix
    END as prix,
    1,
    CASE 
        WHEN type_chambre = 'Standard' THEN 1
        WHEN type_chambre = 'Deluxe' THEN 2
        WHEN type_chambre = 'Suite' THEN 3
        ELSE 1
    END as nombre_lits
FROM 
    hotels h,
    (SELECT 'Standard' as type_chambre, 800 as prix
     UNION SELECT 'Deluxe', 1200
     UNION SELECT 'Suite', 2000) as room_types
WHERE 
    h.id_hotel > 5;

-- Add images for new hotels
INSERT INTO hotel_images (id_hotel, image_url, is_primary) VALUES
-- Marrakech Hotels
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Riad Royal Marrakech'), 'https://images.unsplash.com/photo-1580493113011-ad79f792a7c2?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Riad Royal Marrakech'), 'https://images.unsplash.com/photo-1590080669911-3e70f43cc230?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Palm Plaza Marrakech'), 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Palm Plaza Marrakech'), 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Atlas Medina Marrakech'), 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Atlas Medina Marrakech'), 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800', 0),

-- Tanger Hotels
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Hilton Garden Tanger'), 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Hilton Garden Tanger'), 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Marina Bay Tanger'), 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Marina Bay Tanger'), 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Farah Tanger'), 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Farah Tanger'), 'https://images.unsplash.com/photo-1578991624414-276ef23a534f?w=800', 0),

-- Rabat Hotels
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Tour Hassan Palace'), 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Tour Hassan Palace'), 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Diwan Rabat'), 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Diwan Rabat'), 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'La Villa Mandarine'), 'https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'La Villa Mandarine'), 'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=800', 0),

-- Casablanca Hotels
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Kenzi Tower'), 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Kenzi Tower'), 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Sofitel Casablanca'), 'https://images.unsplash.com/photo-1518733057094-95b53143d2a7?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Sofitel Casablanca'), 'https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?w=800', 0),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Hyatt Regency Casa'), 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800', 1),
((SELECT id_hotel FROM hotels WHERE nom_hotel = 'Hyatt Regency Casa'), 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800', 0);
