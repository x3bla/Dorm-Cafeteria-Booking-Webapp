-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2025 at 12:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_meals_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergies`
--

CREATE TABLE `allergies` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `allergy` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students_has_meal_request`
--

CREATE TABLE `students_has_meal_request` (
  `id` int(11) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `meal_request_date` date NOT NULL,
  `breakfast` tinyint(1) DEFAULT 0,
  `lunch` tinyint(1) DEFAULT 0,
  `dinner` tinyint(1) DEFAULT 0,
  `disabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weekly_menu`
--

CREATE TABLE `weekly_menu` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `meal_time` enum('breakfast','lunch','dinner') NOT NULL,
  `meal_type` varchar(8) NOT NULL,
  `dish` varchar(255) NOT NULL,
  `calories` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `allergen_shrimp` tinyint(1) DEFAULT 0,
  `allergen_crab` tinyint(1) DEFAULT 0,
  `allergen_buckwheat` tinyint(1) DEFAULT 0,
  `allergen_soba` tinyint(1) DEFAULT 0,
  `allergen_egg` tinyint(1) DEFAULT 0,
  `allergen_dairy` tinyint(1) DEFAULT 0,
  `allergen_peanuts` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `weekly_menu`
--

INSERT INTO `weekly_menu` (`id`, `date`, `day_of_week`, `meal_time`, `meal_type`, `dish`, `calories`, `created_at`, `updated_at`, `allergen_shrimp`, `allergen_crab`, `allergen_buckwheat`, `allergen_soba`, `allergen_egg`, `allergen_dairy`, `allergen_peanuts`, `is_available`) VALUES
(73, '2025-01-27', 'Monday', 'breakfast', 'japanese', '味噌汁\nごはん', 390, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(74, '2025-01-27', 'Monday', 'breakfast', 'western', 'パン\nオニオンスープ', 201, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(75, '2025-01-27', 'Monday', 'breakfast', 'plate_si', 'ベーコン', 80, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 1, 1, 0, 1),
(76, '2025-01-27', 'Monday', 'breakfast', 'plate_si', '蒸し野菜', 8, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(77, '2025-01-27', 'Monday', 'breakfast', 'bowl_sid', '大根フレーク煮', 29, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(78, '2025-01-27', 'Monday', 'breakfast', 'bowl_sid', 'ミニコロッケ', 88, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(79, '2025-01-27', 'Monday', 'breakfast', 'bowl_sid', 'フルーツヨーグルト', 37, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 1, 0, 1),
(80, '2025-01-27', 'Monday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(81, '2025-01-27', 'Monday', 'lunch', 'lunch_si', '麦ごはん\n味噌汁\n豚肉のバーベキューソース\nしめじのおろし酢和え', 846, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(82, '2025-01-27', 'Monday', 'lunch', 'don_set', '味噌汁\n親子丼\nじゃがいものカレー煮', 895, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(83, '2025-01-27', 'Monday', 'lunch', 'noodle_s', '醤油ラーメン\n豚串カツ', 506, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(84, '2025-01-27', 'Monday', 'dinner', 'dinner_s', 'ごはん\n中華風コーンスープ\nドイツ風ブラウンシチュー\nハムサラダ', 952, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(85, '2025-01-27', 'Monday', 'dinner', 'don_or_n', '中華風コーンスープ\nキムチビビンバ\nキャベツのじゃこオイルソース', 782, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 1, 1, 1, 0, 1, 1, 0, 1),
(86, '2025-01-27', 'Monday', 'dinner', 'dessert', 'みかんゼリー', 43, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(87, '2025-01-28', 'Tuesday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 381, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(88, '2025-01-28', 'Tuesday', 'breakfast', 'western', 'パン\nパンプキンクリームスープ', 249, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(89, '2025-01-28', 'Tuesday', 'breakfast', 'plate_si', 'ミニオムレツ', 37, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(90, '2025-01-28', 'Tuesday', 'breakfast', 'plate_si', 'ミニ野菜サラダ', 3, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(91, '2025-01-28', 'Tuesday', 'breakfast', 'bowl_sid', '鶏と里芋の味噌煮', 72, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(92, '2025-01-28', 'Tuesday', 'breakfast', 'bowl_sid', 'ごぼうサラダ', 64, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(93, '2025-01-28', 'Tuesday', 'breakfast', 'bowl_sid', 'コーンコロッケ', 145, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(94, '2025-01-28', 'Tuesday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(95, '2025-01-28', 'Tuesday', 'lunch', 'lunch_si', 'ごはん\n味噌汁\n鯖のおろしあん\n肉詰いなりの煮物', 900, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(96, '2025-01-28', 'Tuesday', 'lunch', 'don_set', '味噌汁\n肉丼\n法蓮草とコーンのバター炒め', 842, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(97, '2025-01-28', 'Tuesday', 'lunch', 'noodle_s', 'ちくわ天うどん\nウィンナーと玉ねぎの甘酢炒め', 531, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(98, '2025-01-28', 'Tuesday', 'dinner', 'dinner_s', 'ごはん\nわかめスープ\n鶏肉の黒胡椒揚げ\n高野豆腐のそぼろかけ', 822, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(99, '2025-01-28', 'Tuesday', 'dinner', 'don_or_n', 'きつねうどん\n鶏肉のバジル風味焼き', 505, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(100, '2025-01-28', 'Tuesday', 'dinner', 'dessert', 'パインゼリー', 44, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(101, '2025-01-29', 'Wednesday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 383, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(102, '2025-01-29', 'Wednesday', 'breakfast', 'western', 'パン\n小松菜スープ', 196, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(103, '2025-01-29', 'Wednesday', 'breakfast', 'plate_si', 'ウインナー', 125, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(104, '2025-01-29', 'Wednesday', 'breakfast', 'plate_si', 'もやし炒め', 113, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(105, '2025-01-29', 'Wednesday', 'breakfast', 'bowl_sid', '切干大根の煮付', 66, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(106, '2025-01-29', 'Wednesday', 'breakfast', 'bowl_sid', 'たこ焼き', 51, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(107, '2025-01-29', 'Wednesday', 'breakfast', 'bowl_sid', 'マカロニサラダ', 55, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(108, '2025-01-29', 'Wednesday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(109, '2025-01-29', 'Wednesday', 'lunch', 'lunch_si', '麦ごはん\n山菜汁\n鯖のフリッター茸あん\n牛すじ大根', 628, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(110, '2025-01-29', 'Wednesday', 'lunch', 'don_set', '山菜汁\n麻婆天津飯\n春巻', 1058, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 1, 1, 1, 0, 1, 1, 0, 1),
(111, '2025-01-29', 'Wednesday', 'lunch', 'noodle_s', 'ジャージャー麺\nキャベツと豚肉のチャンプルー', 595, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(112, '2025-01-29', 'Wednesday', 'dinner', 'dinner_s', 'ごはん\nコーンクリームスープ\n牛ステーキ\nほうれん草サラダ', 1302, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(113, '2025-01-29', 'Wednesday', 'dinner', 'don_or_n', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(114, '2025-01-29', 'Wednesday', 'dinner', 'dessert', '自家製プリン', 66, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 1, 0, 1),
(115, '2025-01-30', 'Thursday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 381, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(116, '2025-01-30', 'Thursday', 'breakfast', 'western', 'パン\nえのきコンソメスープ', 194, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(117, '2025-01-30', 'Thursday', 'breakfast', 'plate_si', '鯖の塩焼き', 60, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(118, '2025-01-30', 'Thursday', 'breakfast', 'plate_si', 'ミニ野菜サラダ', 3, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(119, '2025-01-30', 'Thursday', 'breakfast', 'bowl_sid', 'かぼちゃ挽きフライ', 167, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(120, '2025-01-30', 'Thursday', 'breakfast', 'bowl_sid', '笹かま', 27, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(121, '2025-01-30', 'Thursday', 'breakfast', 'bowl_sid', '乳酸菌飲料', 63, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 1, 0, 1),
(122, '2025-01-30', 'Thursday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(123, '2025-01-30', 'Thursday', 'lunch', 'lunch_si', 'ごはん\nすまし汁\n鶏の胡麻照焼き\n厚揚げの生姜風味煮', 975, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(124, '2025-01-30', 'Thursday', 'lunch', 'don_set', 'すまし汁\n豚肉と茄子の辛味噌炒め丼\n鶏つくねの照り煮', 935, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(125, '2025-01-30', 'Thursday', 'lunch', 'noodle_s', '焼きそば\nベーコンサラダ', 654, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(126, '2025-01-30', 'Thursday', 'dinner', 'dinner_s', 'ごはん\n味噌汁\n白身魚の明太マヨチーズ焼き\n大豆のコンソメ煮', 1886, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(127, '2025-01-30', 'Thursday', 'dinner', 'don_or_n', '名古屋赤味噌ラーメン\nアジフライ', 530, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(128, '2025-01-30', 'Thursday', 'dinner', 'dessert', 'ヨーグルト', 64, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 1, 0, 1),
(129, '2025-01-31', 'Friday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 392, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(130, '2025-01-31', 'Friday', 'breakfast', 'western', 'パン\nミネストローネ', 221, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(131, '2025-01-31', 'Friday', 'breakfast', 'plate_si', 'チキンナゲット', 131, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(132, '2025-01-31', 'Friday', 'breakfast', 'plate_si', 'フライドポテト', 72, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(133, '2025-01-31', 'Friday', 'breakfast', 'bowl_sid', '里芋のしぐれ煮', 63, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(134, '2025-01-31', 'Friday', 'breakfast', 'bowl_sid', 'ミニたい焼き', 94, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(135, '2025-01-31', 'Friday', 'breakfast', 'bowl_sid', '生卵', 71, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 1, 0, 0, 1),
(136, '2025-01-31', 'Friday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(137, '2025-01-31', 'Friday', 'lunch', 'lunch_si', '麦ごはん\nポテトコンソメスープ\n白身さかなの味噌マヨネーズ焼き\nチーズスクランブルエッグ', 753, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(138, '2025-01-31', 'Friday', 'lunch', 'don_set', 'ポテトコンソメスープ\nカニクリームくろっけカレー\nキャベツと蒲鉾の香味梅和え', 918, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 1, 1, 1, 0, 1, 1, 0, 1),
(139, '2025-01-31', 'Friday', 'lunch', 'noodle_s', 'かき玉うどん\n海老海鮮餃子', 508, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 1, 0, 1, 0, 1, 1, 0, 1),
(140, '2025-01-31', 'Friday', 'dinner', 'dinner_s', 'ごはん\n味噌汁\n豚肉のスタミナ炒め\n鶏肉と大豆の五目煮', 942, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(141, '2025-01-31', 'Friday', 'dinner', 'don_or_n', '味噌味\n北海丼\nカニ団子のチリソース', 690, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 1, 1, 0, 1, 1, 0, 1),
(142, '2025-01-31', 'Friday', 'dinner', 'dessert', '白桃缶', 44, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(143, '2025-02-01', 'Saturday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 381, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(144, '2025-02-01', 'Saturday', 'breakfast', 'western', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(145, '2025-02-01', 'Saturday', 'breakfast', 'plate_si', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(146, '2025-02-01', 'Saturday', 'breakfast', 'plate_si', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(147, '2025-02-01', 'Saturday', 'breakfast', 'bowl_sid', 'タラフライ', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(148, '2025-02-01', 'Saturday', 'breakfast', 'bowl_sid', 'ポテトサラダ', 43, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 1, 0, 0, 1),
(149, '2025-02-01', 'Saturday', 'breakfast', 'bowl_sid', 'ひじき煮', 25, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(150, '2025-02-01', 'Saturday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(151, '2025-02-01', 'Saturday', 'lunch', 'lunch_si', 'ごはん\n味噌汁\n鯖の黒酢あんかけ\n鶏肉の塩だれ焼き', 925, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(152, '2025-02-01', 'Saturday', 'lunch', 'don_set', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(153, '2025-02-01', 'Saturday', 'lunch', 'noodle_s', '担々麺\nじゃがいもの三色炒め', 804, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 1, 1),
(154, '2025-02-01', 'Saturday', 'dinner', 'dinner_s', 'ごはん\n味噌汁\nチキンカツ\n青菜の胡麻和え', 794, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(155, '2025-02-01', 'Saturday', 'dinner', 'don_or_n', '味噌汁\n豚肉のうなたれ丼\nツナとオニオンの辛子醤油和え', 921, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 0, 0, 1),
(156, '2025-02-01', 'Saturday', 'dinner', 'dessert', 'プリン', 84, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 1, 1, 0, 1),
(157, '2025-02-02', 'Sunday', 'breakfast', 'japanese', 'ごはん\n味噌汁', 395, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(158, '2025-02-02', 'Sunday', 'breakfast', 'western', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(159, '2025-02-02', 'Sunday', 'breakfast', 'plate_si', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(160, '2025-02-02', 'Sunday', 'breakfast', 'plate_si', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(161, '2025-02-02', 'Sunday', 'breakfast', 'bowl_sid', 'イカドーナツフライ', 60, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(162, '2025-02-02', 'Sunday', 'breakfast', 'bowl_sid', 'ミニ焼売', 57, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 1, 0, 1),
(163, '2025-02-02', 'Sunday', 'breakfast', 'bowl_sid', '生卵', 71, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 1, 0, 0, 1),
(164, '2025-02-02', 'Sunday', 'breakfast', 'bowl_sid', '納豆', 82, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 0, 0, 0, 1),
(165, '2025-02-02', 'Sunday', 'lunch', 'lunch_si', '菓子パン\nおにぎり\nデザート\n牛乳', 735, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(166, '2025-02-02', 'Sunday', 'lunch', 'don_set', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(167, '2025-02-02', 'Sunday', 'lunch', 'noodle_s', '', 0, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 0, 0, 1),
(168, '2025-02-02', 'Sunday', 'dinner', 'dinner_s', 'ごはん\n味噌汁\n豚肉の梅醤油焼き\n白菜とベーコンの旨煮', 851, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 1, 0, 1, 1, 0, 1),
(169, '2025-02-02', 'Sunday', 'dinner', 'don_or_n', 'なめこおろしうどん\nエビグラタンフライ', 548, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 1, 0, 1, 0, 1, 1, 0, 1),
(170, '2025-02-02', 'Sunday', 'dinner', 'dessert', 'ジョア', 63, '2025-01-31 05:04:50', '2025-01-31 05:04:50', 0, 0, 0, 0, 0, 1, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allergies`
--
ALTER TABLE `allergies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students_has_meal_request`
--
ALTER TABLE `students_has_meal_request`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`student_no`,`meal_request_date`);

--
-- Indexes for table `weekly_menu`
--
ALTER TABLE `weekly_menu`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allergies`
--
ALTER TABLE `allergies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students_has_meal_request`
--
ALTER TABLE `students_has_meal_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `weekly_menu`
--
ALTER TABLE `weekly_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allergies`
--
ALTER TABLE `allergies`
  ADD CONSTRAINT `allergies_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
