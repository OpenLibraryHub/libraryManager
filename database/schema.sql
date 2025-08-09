-- Library Management System - English schema
-- MySQL 8+ recommended

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS librarians (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  paternal_last_name VARCHAR(50) NOT NULL,
  maternal_last_name VARCHAR(50) NULL,
  middle_name VARCHAR(50) NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  reset_token VARCHAR(64) NULL,
  reset_token_expires DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS classifications (
  classification_id INT PRIMARY KEY,
  description VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS origins (
  origin_id INT PRIMARY KEY,
  donated_by VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS labels (
  label_id INT PRIMARY KEY,
  color VARCHAR(50) NOT NULL,
  description VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rooms (
  room_id INT PRIMARY KEY,
  description VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id_number INT PRIMARY KEY,
  user_key INT NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  phone BIGINT NULL,
  address VARCHAR(100) NULL,
  sanctioned TINYINT(1) NOT NULL DEFAULT 0,
  sanctioned_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(32) NULL,
  title VARCHAR(300) NOT NULL,
  author VARCHAR(512) NULL,
  classification_id INT NULL,
  classification_code VARCHAR(100) NULL,
  copies_total INT NOT NULL DEFAULT 0,
  origin_id INT NULL,
  copies_available INT NOT NULL DEFAULT 0,
  label_id INT NULL,
  library_id BIGINT NULL,
  room_id INT NULL,
  notes VARCHAR(255) NULL,
  KEY idx_books_isbn (isbn),
  KEY idx_books_title (title),
  CONSTRAINT fk_books_classification FOREIGN KEY (classification_id) REFERENCES classifications(classification_id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_books_origin FOREIGN KEY (origin_id) REFERENCES origins(origin_id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_books_label FOREIGN KEY (label_id) REFERENCES labels(label_id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_books_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS loans (
  loan_id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  user_id INT NOT NULL,
  note VARCHAR(255) NULL,
  loaned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  due_at DATETIME NOT NULL,
  returned TINYINT(1) NOT NULL DEFAULT 0,
  returned_at DATETIME NULL,
  KEY idx_loans_user (user_id),
  KEY idx_loans_book (book_id),
  KEY idx_loans_active (returned, due_at),
  CONSTRAINT fk_loans_book FOREIGN KEY (book_id) REFERENCES books(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_loans_user FOREIGN KEY (user_id) REFERENCES users(id_number) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Holds table without FKs to avoid engine/type mismatch errors in existing DBs.
-- You can add FKs later after verifying types.
CREATE TABLE IF NOT EXISTS holds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('queued','fulfilled','canceled') NOT NULL DEFAULT 'queued',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fulfilled_at DATETIME NULL,
  canceled_at DATETIME NULL,
  KEY idx_holds_book (book_id, status, created_at),
  KEY idx_holds_user (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


