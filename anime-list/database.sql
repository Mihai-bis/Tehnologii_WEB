-- AnimeList Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS animelist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE animelist;

-- Users table (fara verificare email, forgot password, remember me)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.jpg',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Genres table
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Anime table
CREATE TABLE anime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    synopsis TEXT,
    cover_image VARCHAR(255) DEFAULT 'default-cover.jpg',
    banner_image VARCHAR(255) DEFAULT 'default-banner.jpg',
    status ENUM('ongoing', 'completed', 'upcoming') DEFAULT 'upcoming',
    type ENUM('tv', 'movie', 'ova', 'ona', 'special') DEFAULT 'tv',
    episodes_count INT DEFAULT 0,
    rating DECIMAL(3,1) DEFAULT 0.0,
    release_year INT DEFAULT NULL,
    studio VARCHAR(100) DEFAULT NULL,
    duration VARCHAR(20) DEFAULT NULL,
    aired_from DATE DEFAULT NULL,
    aired_to DATE DEFAULT NULL,
    views INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Anime-Genres relationship
CREATE TABLE anime_genres (
    anime_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (anime_id, genre_id),
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Episodes table
CREATE TABLE episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anime_id INT NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    duration VARCHAR(20) DEFAULT NULL,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE,
    UNIQUE KEY unique_episode (anime_id, episode_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, anime_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 10),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (anime_id) REFERENCES anime(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, anime_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Watch History table
CREATE TABLE watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    episode_id INT NOT NULL,
    progress INT DEFAULT 0,
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_history (user_id, episode_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default genres
INSERT INTO genres (name, slug) VALUES
('Action', 'action'),
('Adventure', 'adventure'),
('Comedy', 'comedy'),
('Drama', 'drama'),
('Fantasy', 'fantasy'),
('Horror', 'horror'),
('Mahou Shoujo', 'mahou-shoujo'),
('Mecha', 'mecha'),
('Music', 'music'),
('Mystery', 'mystery'),
('Psychological', 'psychological'),
('Romance', 'romance'),
('Sci-Fi', 'sci-fi'),
('Slice of Life', 'slice-of-life'),
('Sports', 'sports'),
('Supernatural', 'supernatural'),
('Thriller', 'thriller');

-- Insert default admin user (password: admin123)
-- Utilizator default admin (parola: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@animelist.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample anime data
INSERT INTO anime (title, slug, description, synopsis, status, type, episodes_count, rating, release_year, studio, duration, aired_from, aired_to, views) VALUES
('Attack on Titan', 'attack-on-titan', 'Humanity fights for survival against giant humanoid Titans.', 'Several hundred years ago, humans were nearly exterminated by Titans. Titans are typically several stories tall, seem to have no intelligence, devour human beings and, worst of all, seem to do it for the pleasure rather than as a food source. A small percentage of humanity survived by walling themselves in a city protected by extremely high walls, even taller than the biggest Titans.', 'completed', 'tv', 87, 9.1, 2013, 'Wit Studio / MAPPA', '24 min', '2013-04-07', '2023-11-05', 15420),
('Demon Slayer', 'demon-slayer', 'A young boy becomes a demon slayer after his family is slaughtered.', 'Since ancient times, rumors have abounded of man-eating demons lurking in the woods. Because of this, the local townsfolk never venture outside at night. Legend has it that a Demon Slayer also roams the night, hunting down these bloodthirsty demons.', 'completed', 'tv', 55, 8.7, 2019, 'ufotable', '24 min', '2019-04-06', '2024-06-30', 12850),
('Jujutsu Kaisen', 'jujutsu-kaisen', 'A boy swallows a cursed object and becomes a sorcerer.', 'Idly indulging in baseless paranormal activities with the Occult Club, high schooler Yuuji Itadori spends his days at either the clubroom or the hospital, where he visits his bedridden grandfather.', 'ongoing', 'tv', 47, 8.6, 2020, 'MAPPA', '24 min', '2020-10-03', NULL, 11200),
('One Piece', 'one-piece', 'A pirate captain searches for the ultimate treasure.', 'Gol D. Roger was known as the Pirate King, the strongest and most infamous being to have sailed the Grand Line. The capture and execution of Roger by the World Government brought a change throughout the world.', 'ongoing', 'tv', 1120, 8.9, 1999, 'Toei Animation', '24 min', '1999-10-20', NULL, 25600),
('My Hero Academia', 'my-hero-academia', 'In a world where people have superpowers, one boy dreams of becoming a hero.', 'The appearance of quirks newly discovered and the rise of superheroes has been rising for years. People are not created equal, and for Izuku Midoriya, he was one of those people who were born without a quirk.', 'completed', 'tv', 159, 7.9, 2016, 'Bones', '24 min', '2016-04-03', '2025-06-30', 9870),
('Chainsaw Man', 'chainsaw-man', 'A young man merges with a devil to become a hybrid.', 'Denji is robbed of a normal teenage life, left with nothing but his deadbeat fathers overwhelming debt. His only companion is his pet, the chainsaw devil Pochita.', 'ongoing', 'tv', 12, 8.5, 2022, 'MAPPA', '24 min', '2022-10-12', NULL, 8900),
('Spy x Family', 'spy-x-family', 'A spy creates a fake family for his mission.', 'Agent Twilight, the greatest spy for the nation of Westalis, has to for a family in order to execute a mission.', 'ongoing', 'tv', 37, 8.5, 2022, 'Wit Studio / CloverWorks', '24 min', '2022-04-09', NULL, 7600),
('Naruto', 'naruto', 'A young ninja seeks recognition and dreams of becoming Hokage.', 'Moments prior to Naruto Uzumakis birth, a huge demon known as the Kyuubi, the Nine-Tailed Fox, attacked Konohagakure, the Hidden Leaf Village, and wreaked havoc.', 'completed', 'tv', 720, 8.3, 2002, 'Pierrot', '24 min', '2002-10-03', '2017-03-23', 22100),
('Death Note', 'death-note', 'A student discovers a supernatural notebook that allows him to kill anyone.', 'A shinigami, as a god of death, can kill any person provided they see their victims face and write their victims name in a notebook called a Death Note.', 'completed', 'tv', 37, 9.0, 2006, 'Madhouse', '23 min', '2006-10-04', '2007-06-27', 18200),
('Fullmetal Alchemist: Brotherhood', 'fullmetal-alchemist-brotherhood', 'Two brothers search for the Philosophers Stone to restore their bodies.', 'After a horrific alchemy experiment goes wrong in the Elric household, brothers Edward and Alphonse are left in a catastrophic new reality.', 'completed', 'tv', 64, 9.1, 2009, 'Bones', '24 min', '2009-04-05', '2010-07-04', 16500);

-- Insert anime-genre relationships
INSERT INTO anime_genres (anime_id, genre_id) VALUES
(1, 1), (1, 2), (1, 10), (1, 12), -- Attack on Titan
(2, 1), (2, 2), (2, 5), (2, 16), -- Demon Slayer
(3, 1), (3, 5), (3, 16), (3, 17), -- Jujutsu Kaisen
(4, 1), (4, 2), (4, 5), (4, 12), -- One Piece
(5, 1), (5, 3), (5, 13), -- My Hero Academia
(6, 1), (6, 3), (6, 5), (6, 16), -- Chainsaw Man
(7, 1), (7, 3), (7, 11), (7, 12), -- Spy x Family
(8, 1), (8, 2), (8, 5), (8, 12), -- Naruto
(9, 7), (9, 10), (9, 11), (9, 17), -- Death Note
(10, 1), (10, 2), (10, 5), (10, 13), (10, 16); -- FMA Brotherhood

-- Insert sample episodes for Attack on Titan
INSERT INTO episodes (anime_id, episode_number, title, video_url, thumbnail, duration) VALUES
(1, 1, 'To You, in 2000 Years: The Fall of Shiganshina, Part 1', 'https://example.com/video/aot-1', 'aot-ep1.jpg', '24:35'),
(1, 2, 'That Day: The Fall of Shiganshina, Part 2', 'https://example.com/video/aot-2', 'aot-ep2.jpg', '24:12'),
(1, 3, 'A Dim Light Amid Despair: Humanitys Comeback, Part 1', 'https://example.com/video/aot-3', 'aot-ep3.jpg', '24:08'),
(1, 4, 'The Night of the Closing Ceremony: Humanitys Comeback, Part 2', 'https://example.com/video/aot-4', 'aot-ep4.jpg', '24:20');

-- Insert sample episodes for Demon Slayer
INSERT INTO episodes (anime_id, episode_number, title, video_url, thumbnail, duration) VALUES
(2, 1, 'Cruelty', 'https://example.com/video/ds-1', 'ds-ep1.jpg', '23:45'),
(2, 2, 'Trainer Sakonji Urokodaki', 'https://example.com/video/ds-2', 'ds-ep2.jpg', '23:50'),
(2, 3, 'Sabito and Makomo', 'https://example.com/video/ds-3', 'ds-ep3.jpg', '23:38'),
(2, 4, 'Final Selection', 'https://example.com/video/ds-4', 'ds-ep4.jpg', '23:55');