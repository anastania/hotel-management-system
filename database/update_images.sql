-- Add images table
CREATE TABLE IF NOT EXISTS hotel_images (
    id_image INT PRIMARY KEY AUTO_INCREMENT,
    id_hotel INT NOT NULL,
    image_url TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hotel) REFERENCES hotels(id_hotel) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample hotel images from Unsplash
INSERT INTO hotel_images (id_hotel, image_url, is_primary) VALUES
(1, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200', 1),
(1, 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200', 0),
(1, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1200', 0),
(2, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200', 1),
(2, 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=1200', 0),
(2, 'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=1200', 0),
(3, 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=1200', 1),
(3, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=1200', 0),
(3, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200', 0),
(4, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200', 1),
(4, 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1200', 0),
(4, 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?w=1200', 0),
(5, 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1200', 1),
(5, 'https://images.unsplash.com/photo-1578991624414-276ef23a534f?w=1200', 0),
(5, 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=1200', 0);
