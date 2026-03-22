-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 22 mars 2026 à 20:08
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bookdb`
--

-- --------------------------------------------------------

--
-- Structure de la table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `auteur` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(8,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `books`
--

INSERT INTO `books` (`id`, `titre`, `auteur`, `description`, `prix`, `stock`, `image`, `category_id`, `created_at`) VALUES
(1, 'History of the Decline and Fall of the Roman Empire Complete and Unabridged', 'Edward Gibbon', NULL, 23.09, 27, 'https://covers.openlibrary.org/b/id/5978577-M.jpg', 1, '2026-03-14 09:03:36'),
(2, 'Die Blechtrommel', 'Günter Grass', NULL, 9.51, 43, 'https://covers.openlibrary.org/b/id/419922-M.jpg', 1, '2026-03-14 09:03:36'),
(3, 'Sult', 'Knut Hamsun', NULL, 36.75, 40, 'https://covers.openlibrary.org/b/id/10472647-M.jpg', 1, '2026-03-14 09:03:36'),
(4, 'Voskresenīe', 'Лев Толстой', NULL, 18.33, 21, 'https://covers.openlibrary.org/b/id/6309101-M.jpg', 1, '2026-03-14 09:03:36'),
(5, 'Доктор Живаго', 'Boris Leonidovich Pasternak', NULL, 40.54, 27, 'https://covers.openlibrary.org/b/id/1045432-M.jpg', 1, '2026-03-14 09:03:36'),
(6, 'La nausée', 'Jean-Paul Sartre', NULL, 31.90, 15, 'https://covers.openlibrary.org/b/id/9393973-M.jpg', 1, '2026-03-14 09:03:36'),
(7, 'Lolita', 'Vladimir Nabokov', NULL, 36.30, 8, 'https://covers.openlibrary.org/b/id/12984540-M.jpg', 1, '2026-03-14 09:03:36'),
(8, 'Белые ночи', 'Фёдор Михайлович Достоевский', NULL, 25.85, 43, 'https://covers.openlibrary.org/b/id/3293338-M.jpg', 1, '2026-03-14 09:03:36'),
(9, 'El túnel', 'Ernesto Sabato', NULL, 9.13, 37, 'https://covers.openlibrary.org/b/id/5517733-M.jpg', 1, '2026-03-14 09:03:36'),
(10, 'The Godfather', 'Mario Puzo', NULL, 23.93, 24, 'https://covers.openlibrary.org/b/id/6507069-M.jpg', 1, '2026-03-14 09:03:36'),
(11, 'Chemistry', 'Theodore L. Brown', NULL, 36.65, 9, 'https://covers.openlibrary.org/b/id/9407725-M.jpg', 2, '2026-03-14 09:03:39'),
(12, 'The science of getting rich, or, financial success through creative thought', 'Wallace D. Wattles', NULL, 38.37, 32, 'https://covers.openlibrary.org/b/id/854989-M.jpg', 2, '2026-03-14 09:03:39'),
(13, 'Discours de la méthode', 'René Descartes', NULL, 26.69, 30, 'https://covers.openlibrary.org/b/id/8236442-M.jpg', 2, '2026-03-14 09:03:39'),
(14, 'Self-knowledge', 'John Mason', NULL, 23.18, 20, 'https://covers.openlibrary.org/b/id/6137558-M.jpg', 2, '2026-03-14 09:03:39'),
(15, 'Foundation', 'Isaac Asimov', NULL, 26.46, 11, 'https://covers.openlibrary.org/b/id/14612610-M.jpg', 2, '2026-03-14 09:03:39'),
(16, 'The guide of the perplexed of Maimonides', 'Moses Maimonides', NULL, 33.64, 36, 'https://covers.openlibrary.org/b/id/2292521-M.jpg', 2, '2026-03-14 09:03:39'),
(17, 'The Sign of Four', 'Arthur Conan Doyle', NULL, 28.02, 49, 'https://covers.openlibrary.org/b/id/9247987-M.jpg', 2, '2026-03-14 09:03:39'),
(18, 'The Prince and the Pauper', 'Mark Twain', NULL, 16.77, 19, 'https://covers.openlibrary.org/b/id/8221267-M.jpg', 2, '2026-03-14 09:03:39'),
(19, 'Die protestantische Ethik und der Geist des Kapitalismus', 'Max Weber', NULL, 43.20, 9, 'https://covers.openlibrary.org/b/id/5381835-M.jpg', 2, '2026-03-14 09:03:39'),
(20, 'The Last Man', 'Mary Shelley', NULL, 27.41, 26, 'https://covers.openlibrary.org/b/id/882662-M.jpg', 2, '2026-03-14 09:03:39'),
(21, 'Mémoires', 'Giacomo Casanova', NULL, 36.96, 37, 'https://covers.openlibrary.org/b/id/9556873-M.jpg', 3, '2026-03-14 09:03:40'),
(22, 'Perfume', 'Patrick Süskind', NULL, 35.52, 42, 'https://covers.openlibrary.org/b/id/10910286-M.jpg', 3, '2026-03-14 09:03:40'),
(23, 'A Study of History', 'Arnold J. Toynbee', NULL, 41.18, 35, 'https://covers.openlibrary.org/b/id/121255-M.jpg', 3, '2026-03-14 09:03:40'),
(24, 'Life of Pi', 'Yann Martel', NULL, 34.54, 15, 'https://covers.openlibrary.org/b/id/12840573-M.jpg', 3, '2026-03-14 09:03:40'),
(25, 'A Brief History of Time', 'Stephen Hawking', NULL, 16.59, 22, 'https://covers.openlibrary.org/b/id/10432365-M.jpg', 3, '2026-03-14 09:03:40'),
(26, 'Naturalis historia', 'Pliny the Elder', NULL, 40.15, 44, 'https://covers.openlibrary.org/b/id/6543272-M.jpg', 3, '2026-03-14 09:03:40'),
(27, 'Les Contes de ma mère l\'Oye', 'Charles Perrault', NULL, 25.36, 35, 'https://covers.openlibrary.org/b/id/308893-M.jpg', 3, '2026-03-14 09:03:40'),
(28, 'The Silmarillion', 'J.R.R. Tolkien', NULL, 20.28, 17, 'https://covers.openlibrary.org/b/id/14627042-M.jpg', 3, '2026-03-14 09:03:40'),
(29, 'Demian', 'Hermann Hesse', NULL, 33.45, 44, 'https://covers.openlibrary.org/b/id/12569297-M.jpg', 3, '2026-03-14 09:03:40'),
(30, 'Bambi', 'Felix Salten', NULL, 27.28, 7, 'https://covers.openlibrary.org/b/id/7153939-M.jpg', 3, '2026-03-14 09:03:40'),
(31, 'Lilith', 'George MacDonald', NULL, 31.62, 10, 'https://covers.openlibrary.org/b/id/14364546-M.jpg', 4, '2026-03-14 09:03:42'),
(32, 'The Sea Fairies', 'L. Frank Baum', NULL, 25.42, 50, 'https://covers.openlibrary.org/b/id/1814237-M.jpg', 4, '2026-03-14 09:03:42'),
(33, 'The Phoenix and the Carpet', 'Edith Nesbit', NULL, 41.55, 17, 'https://covers.openlibrary.org/b/id/902122-M.jpg', 4, '2026-03-14 09:03:42'),
(34, 'The Enchanted Castle', 'Edith Nesbit', NULL, 28.72, 32, 'https://covers.openlibrary.org/b/id/6644514-M.jpg', 4, '2026-03-14 09:03:42'),
(35, 'Phantastes', 'George MacDonald', NULL, 34.49, 17, 'https://covers.openlibrary.org/b/id/14358814-M.jpg', 4, '2026-03-14 09:03:42'),
(36, 'The Princess and the Goblin', 'George MacDonald', NULL, 39.19, 36, 'https://covers.openlibrary.org/b/id/14363454-M.jpg', 4, '2026-03-14 09:03:42'),
(37, 'Peter Pan', 'J. M. Barrie', NULL, 18.58, 15, 'https://covers.openlibrary.org/b/id/8237052-M.jpg', 4, '2026-03-14 09:03:42'),
(38, 'Dracula', 'Bram Stoker', NULL, 20.23, 29, 'https://covers.openlibrary.org/b/id/12216503-M.jpg', 4, '2026-03-14 09:03:42'),
(39, 'Herland', 'Charlotte Perkins Gilman', NULL, 22.52, 18, 'https://covers.openlibrary.org/b/id/448130-M.jpg', 4, '2026-03-14 09:03:42'),
(40, 'The Vampyre', 'John William Polidori', NULL, 25.56, 20, 'https://covers.openlibrary.org/b/id/4871002-M.jpg', 4, '2026-03-14 09:03:42'),
(41, 'Advances in Computers, Volume 49 (Advances in Computers)', 'Marvin V. Zelkowitz', NULL, 37.99, 10, 'https://covers.openlibrary.org/b/id/1094406-M.jpg', 5, '2026-03-14 09:03:44'),
(42, 'Tax administration', 'United States. General Accounting Office', NULL, 32.74, 31, NULL, 5, '2026-03-14 09:03:44'),
(43, 'The Legend of Sleepy Hollow', 'Washington Irving', NULL, 33.38, 15, 'https://covers.openlibrary.org/b/id/8243083-M.jpg', 5, '2026-03-14 09:03:44'),
(44, '2001', 'Arthur C. Clarke', NULL, 18.02, 44, 'https://covers.openlibrary.org/b/id/11344400-M.jpg', 5, '2026-03-14 09:03:44'),
(45, 'Ἰλιάς', 'Όμηρος', NULL, 11.99, 45, 'https://covers.openlibrary.org/b/id/7083790-M.jpg', 5, '2026-03-14 09:03:44'),
(46, 'The Autobiography of Benjamin Franklin', 'Benjamin Franklin', NULL, 24.56, 7, 'https://covers.openlibrary.org/b/id/5647361-M.jpg', 5, '2026-03-14 09:03:44'),
(47, 'Medea', 'Euripides', NULL, 20.07, 42, 'https://covers.openlibrary.org/b/id/6537700-M.jpg', 5, '2026-03-14 09:03:44'),
(48, 'A Christmas Carol', 'Charles Dickens', NULL, 40.00, 9, 'https://covers.openlibrary.org/b/id/12875748-M.jpg', 5, '2026-03-14 09:03:44'),
(49, 'Careers in Focus', 'Ferguson Publishing Company', NULL, 20.05, 49, NULL, 5, '2026-03-14 09:03:44'),
(50, 'Les Robots', 'Isaac Asimov', NULL, 32.97, 19, 'https://covers.openlibrary.org/b/id/12385229-M.jpg', 5, '2026-03-14 09:03:44'),
(51, 'The Problems of Philosophy', 'Bertrand Russell', NULL, 41.47, 28, 'https://covers.openlibrary.org/b/id/6947403-M.jpg', 6, '2026-03-14 09:03:45'),
(52, 'The Story of Philosophy', 'Will Durant', NULL, 38.35, 10, 'https://covers.openlibrary.org/b/id/405360-M.jpg', 6, '2026-03-14 09:03:45'),
(53, 'The Kybalion', 'Three Initiates', NULL, 31.38, 24, 'https://covers.openlibrary.org/b/id/8801364-M.jpg', 6, '2026-03-14 09:03:45'),
(54, 'Man and Superman', 'George Bernard Shaw', NULL, 18.67, 14, 'https://covers.openlibrary.org/b/id/8288031-M.jpg', 6, '2026-03-14 09:03:45'),
(55, 'Principles of Political Economy', 'John Stuart Mill', NULL, 31.71, 43, 'https://covers.openlibrary.org/b/id/862387-M.jpg', 6, '2026-03-14 09:03:45'),
(56, 'Philosophiae naturalis principia mathematica', 'Sir Isaac Newton', NULL, 26.20, 29, 'https://covers.openlibrary.org/b/id/7122145-M.jpg', 6, '2026-03-14 09:03:45'),
(57, 'Discours de la méthode', 'René Descartes', NULL, 38.13, 21, 'https://covers.openlibrary.org/b/id/8236442-M.jpg', 6, '2026-03-14 09:03:45'),
(58, 'Jenseits von Gut und Böse', 'Friedrich Nietzsche', NULL, 41.56, 17, 'https://covers.openlibrary.org/b/id/8245356-M.jpg', 6, '2026-03-14 09:03:45'),
(59, 'The guide of the perplexed of Maimonides', 'Moses Maimonides', NULL, 14.19, 44, 'https://covers.openlibrary.org/b/id/2292521-M.jpg', 6, '2026-03-14 09:03:45'),
(60, 'Essay concerning human understanding', 'John Locke', NULL, 42.66, 9, 'https://covers.openlibrary.org/b/id/6086503-M.jpg', 6, '2026-03-14 09:03:45'),
(61, 'Dell\'Arte della Guerra', 'Niccolò Machiavelli', NULL, 22.16, 9, 'https://covers.openlibrary.org/b/id/5229470-M.jpg', 7, '2026-03-14 09:03:48'),
(62, 'The Art of Loving', 'Erich Fromm', NULL, 34.38, 26, 'https://covers.openlibrary.org/b/id/4741809-M.jpg', 7, '2026-03-14 09:03:48'),
(63, 'The Art of War', '孙武', NULL, 13.02, 12, 'https://covers.openlibrary.org/b/id/4849549-M.jpg', 7, '2026-03-14 09:03:48'),
(64, 'Ars Amatoria', 'Ovid', NULL, 34.16, 20, 'https://covers.openlibrary.org/b/id/12733518-M.jpg', 7, '2026-03-14 09:03:48'),
(65, 'The art of money getting, or, Golden rules for money getting', 'P. T. Barnum', NULL, 40.67, 22, 'https://covers.openlibrary.org/b/id/756095-M.jpg', 7, '2026-03-14 09:03:48'),
(66, 'Poetics', 'Aristotle', NULL, 28.29, 28, 'https://covers.openlibrary.org/b/id/6528920-M.jpg', 7, '2026-03-14 09:03:48'),
(67, 'The Book of Tea', 'Okakura Kakuzō', NULL, 42.56, 37, 'https://covers.openlibrary.org/b/id/8245415-M.jpg', 7, '2026-03-14 09:03:48'),
(68, 'La Poetica', 'Aristotle', NULL, 12.51, 39, 'https://covers.openlibrary.org/b/id/129771-M.jpg', 7, '2026-03-14 09:03:48'),
(69, 'The Art of Public Speaking', 'Stephen E. Lucas', NULL, 35.48, 36, 'https://covers.openlibrary.org/b/id/4947875-M.jpg', 7, '2026-03-14 09:03:48'),
(70, 'Principles of Anatomy and Physiology', 'Gerard J. Tortora', NULL, 40.18, 28, 'https://covers.openlibrary.org/b/id/3810109-M.jpg', 7, '2026-03-14 09:03:48'),
(71, 'On Cooking', 'Sarah R. Labensky', NULL, 8.94, 40, 'https://covers.openlibrary.org/b/id/92630-M.jpg', 8, '2026-03-14 09:03:50'),
(72, 'Boston Cooking-School cook book', 'Fannie Merritt Farmer', NULL, 39.68, 20, 'https://covers.openlibrary.org/b/id/8246263-M.jpg', 8, '2026-03-14 09:03:50'),
(73, 'The Lost World', 'Arthur Conan Doyle', NULL, 27.55, 22, 'https://covers.openlibrary.org/b/id/8231444-M.jpg', 8, '2026-03-14 09:03:50'),
(74, 'Joy of Cooking', 'Irma S. Rombauer', NULL, 12.25, 19, 'https://covers.openlibrary.org/b/id/475157-M.jpg', 8, '2026-03-14 09:03:50'),
(75, 'Treasure Island', 'Robert Louis Stevenson', NULL, 10.12, 36, 'https://covers.openlibrary.org/b/id/13859660-M.jpg', 8, '2026-03-14 09:03:50'),
(76, 'The Time Machine', 'H. G. Wells', NULL, 34.02, 30, 'https://covers.openlibrary.org/b/id/9009316-M.jpg', 8, '2026-03-14 09:03:50'),
(77, 'The Canterbury Tales', 'Geoffrey Chaucer', NULL, 44.03, 34, 'https://covers.openlibrary.org/b/id/5767180-M.jpg', 8, '2026-03-14 09:03:50'),
(78, 'Marc Chagall', 'Marc Chagall', NULL, 30.54, 36, 'https://covers.openlibrary.org/b/id/723472-M.jpg', 8, '2026-03-14 09:03:50'),
(79, 'The Castle of Otranto', 'Horace Walpole', NULL, 13.31, 10, 'https://covers.openlibrary.org/b/id/6468730-M.jpg', 8, '2026-03-14 09:03:50'),
(80, '[William Wheeler Hubbell, authorized to apply for patents.]', 'United States. Congress. Senate. Committee on Patents', NULL, 16.51, 39, 'https://covers.openlibrary.org/b/id/10200621-M.jpg', 8, '2026-03-14 09:03:50'),
(81, 'Gulliver\'s Travels', 'Jonathan Swift', NULL, 24.55, 18, 'https://covers.openlibrary.org/b/id/12717083-M.jpg', 9, '2026-03-14 09:03:52'),
(82, 'The Travels of Marco Polo', 'Marco Polo', NULL, 28.35, 43, 'https://covers.openlibrary.org/b/id/8237917-M.jpg', 9, '2026-03-14 09:03:52'),
(83, 'Great Expectations', 'Charles Dickens', NULL, 25.18, 36, 'https://covers.openlibrary.org/b/id/13322313-M.jpg', 9, '2026-03-14 09:03:52'),
(84, 'The Horse and His Boy', 'C. S. Lewis', NULL, 37.21, 44, 'https://covers.openlibrary.org/b/id/9184792-M.jpg', 9, '2026-03-14 09:03:52'),
(85, 'Kidnapped', 'Robert Louis Stevenson', NULL, 41.15, 20, 'https://covers.openlibrary.org/b/id/8267822-M.jpg', 9, '2026-03-14 09:03:52'),
(86, 'Travels with Charley', 'John Steinbeck', NULL, 40.76, 41, 'https://covers.openlibrary.org/b/id/8219483-M.jpg', 9, '2026-03-14 09:03:52'),
(87, 'The Stones of Venice', 'John Ruskin', NULL, 32.77, 25, 'https://covers.openlibrary.org/b/id/8239410-M.jpg', 9, '2026-03-14 09:03:52'),
(88, 'A Child\'s Garden of Verses', 'Robert Louis Stevenson', NULL, 39.29, 49, 'https://covers.openlibrary.org/b/id/8777530-M.jpg', 9, '2026-03-14 09:03:52'),
(89, 'The Silver Chair', 'C. S. Lewis', NULL, 11.26, 20, 'https://covers.openlibrary.org/b/id/6950992-M.jpg', 9, '2026-03-14 09:03:52'),
(90, 'Utopia', 'Thomas More', NULL, 19.20, 34, 'https://covers.openlibrary.org/b/id/7222976-M.jpg', 9, '2026-03-14 09:03:52'),
(91, 'Orlando', 'Virginia Woolf', NULL, 42.90, 11, 'https://covers.openlibrary.org/b/id/3240273-M.jpg', 10, '2026-03-14 09:03:54'),
(92, 'The Lost World', 'Arthur Conan Doyle', NULL, 42.52, 17, 'https://covers.openlibrary.org/b/id/8231444-M.jpg', 10, '2026-03-14 09:03:54'),
(93, 'Flush', 'Virginia Woolf', NULL, 12.02, 33, 'https://covers.openlibrary.org/b/id/116161-M.jpg', 10, '2026-03-14 09:03:54'),
(94, 'Incidents in the Life of a Slave Girl', 'Harriet A. Jacobs', NULL, 42.58, 22, 'https://covers.openlibrary.org/b/id/411542-M.jpg', 10, '2026-03-14 09:03:54'),
(95, 'The Yellow Wallpaper', 'Charlotte Perkins Gilman', NULL, 32.61, 8, 'https://covers.openlibrary.org/b/id/652029-M.jpg', 10, '2026-03-14 09:03:54'),
(96, 'Emma', 'Jane Austen', NULL, 32.77, 48, 'https://covers.openlibrary.org/b/id/9278312-M.jpg', 10, '2026-03-14 09:03:54'),
(97, 'A Mind That Found Itself', 'Clifford Whittingham Beers', NULL, 18.42, 24, 'https://covers.openlibrary.org/b/id/10857476-M.jpg', 10, '2026-03-14 09:03:54'),
(98, 'Dubliners', 'James Joyce', NULL, 36.71, 45, 'https://covers.openlibrary.org/b/id/8216412-M.jpg', 10, '2026-03-14 09:03:54'),
(99, 'Great Expectations', 'Charles Dickens', NULL, 12.26, 42, 'https://covers.openlibrary.org/b/id/13322313-M.jpg', 10, '2026-03-14 09:03:54'),
(100, 'Ulysses', 'James Joyce', NULL, 28.32, 18, 'https://covers.openlibrary.org/b/id/13136548-M.jpg', 10, '2026-03-14 09:03:54'),
(101, 'Bundle', 'Jeffrey D. Camm', NULL, 27.18, 41, 'https://covers.openlibrary.org/b/id/13179671-M.jpg', 11, '2026-03-14 09:03:57'),
(102, 'A Modern Utopia', 'H. G. Wells', NULL, 29.67, 28, 'https://covers.openlibrary.org/b/id/8232021-M.jpg', 11, '2026-03-14 09:03:57'),
(103, 'La conquête du pain', 'Peter Kropotkin', NULL, 40.20, 10, 'https://covers.openlibrary.org/b/id/7296134-M.jpg', 11, '2026-03-14 09:03:57'),
(104, 'Économie internationale', 'Paul R. Krugman', NULL, 32.83, 44, 'https://covers.openlibrary.org/b/id/12679935-M.jpg', 11, '2026-03-14 09:03:57'),
(105, 'Ethan Frome', 'Edith Wharton', NULL, 43.64, 50, 'https://covers.openlibrary.org/b/id/8303480-M.jpg', 11, '2026-03-14 09:03:57'),
(106, 'Πολιτικά (Politiká)', 'Aristotle', NULL, 42.23, 36, 'https://covers.openlibrary.org/b/id/1277085-M.jpg', 11, '2026-03-14 09:03:57'),
(107, 'Economics, an introductory analysis', 'Paul Anthony Samuelson', NULL, 19.62, 21, 'https://covers.openlibrary.org/b/id/10416536-M.jpg', 11, '2026-03-14 09:03:57'),
(108, 'Principles of Political Economy', 'John Stuart Mill', NULL, 36.16, 41, 'https://covers.openlibrary.org/b/id/862387-M.jpg', 11, '2026-03-14 09:03:57'),
(109, 'Essays', 'Francis Bacon', NULL, 28.36, 34, 'https://covers.openlibrary.org/b/id/8236351-M.jpg', 11, '2026-03-14 09:03:57'),
(110, 'Hard Times', 'Charles Dickens', NULL, 12.33, 16, 'https://covers.openlibrary.org/b/id/8236916-M.jpg', 11, '2026-03-14 09:03:57'),
(111, 'Educational psychology', 'Anita Woolfolk Hoy', NULL, 8.46, 20, 'https://covers.openlibrary.org/b/id/137962-M.jpg', 12, '2026-03-14 09:03:59'),
(112, '... Trotzdem Ja zum Leben sagen', 'Viktor E. Frankl', NULL, 10.79, 25, 'https://covers.openlibrary.org/b/id/8516506-M.jpg', 12, '2026-03-14 09:03:59'),
(113, 'Die Traumdeutung', 'Sigmund Freud', NULL, 19.55, 26, 'https://covers.openlibrary.org/b/id/234730-M.jpg', 12, '2026-03-14 09:03:59'),
(114, 'The Yellow Wallpaper', 'Charlotte Perkins Gilman', NULL, 19.44, 24, 'https://covers.openlibrary.org/b/id/652029-M.jpg', 12, '2026-03-14 09:03:59'),
(115, 'The Secret Agent', 'Joseph Conrad', NULL, 13.95, 43, 'https://covers.openlibrary.org/b/id/8239401-M.jpg', 12, '2026-03-14 09:03:59'),
(116, 'Hamlet', 'William Shakespeare', NULL, 11.45, 19, 'https://covers.openlibrary.org/b/id/8281954-M.jpg', 12, '2026-03-14 09:03:59'),
(117, 'Οἰδίπους Τύραννος (Oidípous Týrannos)', 'Sophocles', NULL, 11.25, 44, 'https://covers.openlibrary.org/b/id/764695-M.jpg', 12, '2026-03-14 09:03:59'),
(118, 'The Power of Your Subconscious Mind', 'Joseph Murphy', NULL, 8.12, 17, 'https://covers.openlibrary.org/b/id/6553019-M.jpg', 12, '2026-03-14 09:03:59'),
(119, 'The Black Cat', 'Edgar Allan Poe', NULL, 14.24, 41, 'https://covers.openlibrary.org/b/id/11709016-M.jpg', 12, '2026-03-14 09:03:59'),
(120, 'Lord Jim', 'Joseph Conrad', NULL, 22.82, 45, 'https://covers.openlibrary.org/b/id/8236295-M.jpg', 12, '2026-03-14 09:03:59'),
(121, 'Πολιτικά (Politiká)', 'Aristotle', NULL, 41.78, 41, 'https://covers.openlibrary.org/b/id/1277085-M.jpg', 13, '2026-03-14 09:04:02'),
(122, 'Principles of Political Economy', 'John Stuart Mill', NULL, 10.48, 9, 'https://covers.openlibrary.org/b/id/862387-M.jpg', 13, '2026-03-14 09:04:02'),
(123, 'A Vindication of Rights of Woman', 'Mary Wollstonecraft', NULL, 27.70, 30, 'https://covers.openlibrary.org/b/id/675852-M.jpg', 13, '2026-03-14 09:04:02'),
(124, 'Das Kapital', 'Karl Marx', NULL, 31.84, 21, 'https://covers.openlibrary.org/b/id/10995820-M.jpg', 13, '2026-03-14 09:04:02'),
(125, 'The Enduring Vision', 'Paul S. Boyer', NULL, 40.58, 43, 'https://covers.openlibrary.org/b/id/8237922-M.jpg', 13, '2026-03-14 09:04:02'),
(126, 'De la démocratie en Amérique', 'Alexis de Tocqueville', NULL, 16.91, 44, 'https://covers.openlibrary.org/b/id/45979-M.jpg', 13, '2026-03-14 09:04:02'),
(127, 'Mein Kampf', 'Adolf Hitler', NULL, 26.29, 45, 'https://covers.openlibrary.org/b/id/12724015-M.jpg', 13, '2026-03-14 09:04:02'),
(128, 'Du contrat social', 'Jean-Jacques Rousseau', NULL, 24.44, 6, 'https://covers.openlibrary.org/b/id/6581958-M.jpg', 13, '2026-03-14 09:04:02'),
(129, 'Het Achterhuis', 'Anne Frank', NULL, 42.63, 8, 'https://covers.openlibrary.org/b/id/8584021-M.jpg', 13, '2026-03-14 09:04:02'),
(130, 'Poetics', 'Aristotle', NULL, 41.73, 23, 'https://covers.openlibrary.org/b/id/6528920-M.jpg', 13, '2026-03-14 09:04:02'),
(131, 'Heinemann Mathematics', 'Scottish Primary Mathematics Group', NULL, 34.67, 16, 'https://covers.openlibrary.org/b/id/2417832-M.jpg', 14, '2026-03-14 09:04:04'),
(132, 'Philosophiae naturalis principia mathematica', 'Sir Isaac Newton', NULL, 22.20, 44, 'https://covers.openlibrary.org/b/id/7122145-M.jpg', 14, '2026-03-14 09:04:04'),
(133, 'The Iron Heel', 'Jack London', NULL, 14.81, 44, 'https://covers.openlibrary.org/b/id/8243314-M.jpg', 14, '2026-03-14 09:04:04'),
(134, 'Chemistry', 'Theodore L. Brown', NULL, 23.45, 7, 'https://covers.openlibrary.org/b/id/9407725-M.jpg', 14, '2026-03-14 09:04:04'),
(135, 'Sein und Zeit', 'Martin Heidegger', NULL, 41.83, 39, 'https://covers.openlibrary.org/b/id/2208564-M.jpg', 14, '2026-03-14 09:04:04'),
(136, 'Mathematics', 'McGraw-Hill', NULL, 13.74, 32, 'https://covers.openlibrary.org/b/id/1089515-M.jpg', 14, '2026-03-14 09:04:04'),
(137, 'Flatland', 'Edwin Abbott Abbott', NULL, 8.15, 45, 'https://covers.openlibrary.org/b/id/10069547-M.jpg', 14, '2026-03-14 09:04:04'),
(138, 'La Poetica', 'Aristotle', NULL, 29.20, 23, 'https://covers.openlibrary.org/b/id/129771-M.jpg', 14, '2026-03-14 09:04:04'),
(139, 'Elements', 'Euclid', NULL, 15.09, 35, 'https://covers.openlibrary.org/b/id/1736063-M.jpg', 14, '2026-03-14 09:04:04'),
(140, 'Tractatus logico-philosophicus', 'Ludwig Wittgenstein', NULL, 17.28, 10, 'https://covers.openlibrary.org/b/id/5415771-M.jpg', 14, '2026-03-14 09:04:04'),
(141, 'Poetics', 'Aristotle', NULL, 17.30, 12, 'https://covers.openlibrary.org/b/id/6528920-M.jpg', 15, '2026-03-14 09:04:06'),
(142, 'A Child\'s Garden of Verses', 'Robert Louis Stevenson', NULL, 28.04, 28, 'https://covers.openlibrary.org/b/id/8777530-M.jpg', 15, '2026-03-14 09:04:06'),
(143, 'Les fleurs du mal', 'Charles Baudelaire', NULL, 25.30, 50, 'https://covers.openlibrary.org/b/id/8236412-M.jpg', 15, '2026-03-14 09:04:06'),
(144, 'Works [37 plays, 6 poems, sonnets]', 'William Shakespeare', NULL, 28.60, 14, 'https://covers.openlibrary.org/b/id/8779054-M.jpg', 15, '2026-03-14 09:04:06'),
(145, 'Discovering Classical Music', 'Ian Christians', NULL, 37.91, 49, 'https://covers.openlibrary.org/b/id/2693703-M.jpg', 15, '2026-03-14 09:04:06'),
(146, 'The Color Purple', 'Alice Walker', NULL, 27.38, 35, 'https://covers.openlibrary.org/b/id/8564628-M.jpg', 15, '2026-03-14 09:04:06'),
(147, 'Paradise Lost', 'John Milton', NULL, 31.00, 10, 'https://covers.openlibrary.org/b/id/5992814-M.jpg', 15, '2026-03-14 09:04:06'),
(148, 'The Silmarillion', 'J.R.R. Tolkien', NULL, 33.36, 14, 'https://covers.openlibrary.org/b/id/14627042-M.jpg', 15, '2026-03-14 09:04:06'),
(149, 'The Merchant of Venice', 'William Shakespeare', NULL, 23.20, 17, 'https://covers.openlibrary.org/b/id/7182819-M.jpg', 15, '2026-03-14 09:04:06'),
(150, 'A Clockwork Orange', 'Anthony Burgess', NULL, 35.03, 9, 'https://covers.openlibrary.org/b/id/13151224-M.jpg', 15, '2026-03-14 09:04:06');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Roman'),
(2, 'Science'),
(3, 'Histoire'),
(4, 'Fantasy'),
(5, 'Informatique'),
(6, 'Philosophie'),
(7, 'Art'),
(8, 'Cuisine'),
(9, 'Voyage'),
(10, 'Biographie'),
(11, 'Economie'),
(12, 'Psychologie'),
(13, 'Politique'),
(14, 'Mathematiques'),
(15, 'Musique');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `statut` enum('en attente','confirmee','livree','annulee') DEFAULT 'en attente',
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `statut`, `total`, `created_at`) VALUES
(2, 2, 'en attente', 64.36, '2026-03-17 17:26:07');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unit` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantite`, `prix_unit`) VALUES
(3, 2, 148, 1, 33.36),
(4, 2, 147, 1, 31.00);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` datetime DEFAULT current_timestamp(),
  `token` varchar(64) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `reset_code` varchar(10) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `created_at`, `token`, `telephone`, `adresse`, `ville`, `date_naissance`, `reset_code`, `reset_expires`, `reset_expiry`, `reset_token`) VALUES
(2, 'Arfaoui', 'Hamza', 'arfaouihamzaa3@gmail.com', '$2y$10$2KKpuqx1WSzJwOLbjNlOKu5Npk5Mvc/H7nQemSR.1t.3br3oiTIOy', 'client', '2026-03-17 17:12:03', '6848316f341690662ed7a5779448b02127589b73f81c9c4ba6408032e2b0074b', '+21690300893', 'Bir el bey', 'Ben Arous', '2003-10-24', NULL, '2026-03-22 19:56:07', '2026-03-22 19:36:28', 'b21a72ad5be17f1841540392c5adce6b22d5a8c533a0a6394c12a4cfdebb8869');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
