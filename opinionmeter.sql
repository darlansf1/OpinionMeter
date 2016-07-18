-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2015 at 07:56 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12
SET SESSION SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
--
-- Database: 'opmeter'
--
CREATE DATABASE IF NOT EXISTS opmeter DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
--
-- USER: 'om_user'
--
CREATE USER 'opmeter_user'@'localhost' IDENTIFIED BY 'opinion_meter';
GRANT ALL ON opmeter.* TO 'opmeter_user'@'localhost';
USE 'opmeter';
-- --------------------------------------------------------
--
-- Table structure for table 'aux_algorithm'
--
CREATE TABLE IF NOT EXISTS aux_algorithm (
  aux_algorithm varchar(50) NOT NULL,
  PRIMARY KEY (aux_algorithm)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'aux_algorithm'
--
INSERT INTO aux_algorithm (aux_algorithm) VALUES
('mostVoted'),
('random'),
('testMode'),
('transductive'),
('none'),
('PMIBased'),
('lexiconBased'),
('naiveBayes'),
('frequenceBased');
--
-- Table structure for table 'aux_PMI_hits'
--
CREATE TABLE IF NOT EXISTS aux_PMI_hits (
  portuguese_negative int(11) NOT NULL DEFAULT 1,
  english_negative int(11) NOT NULL DEFAULT 1,
  portuguese_positive int(11) NOT NULL DEFAULT 1,
  english_positive int(11) NOT NULL DEFAULT 1
) ;

--
-- Dumping data for table 'aux_PMI_hits'
--
INSERT INTO aux_PMI_hits (portuguese_negative,portuguese_positive,english_negative, english_positive) VALUES (0,0,0,0);

-- --------------------------------------------------------
--
-- Table structure for table 'tbl_chosen_label'
--
CREATE TABLE IF NOT EXISTS tbl_chosen_label (
  label_document int(11) NOT NULL,
  label_tagger int(11) NOT NULL,
  label_label varchar(50) NOT NULL,
  label_rank int(11) NOT NULL COMMENT 'If the label was a suggestion, then its rank is marked as -1',
  PRIMARY KEY (label_document,label_tagger,label_label),
  KEY fk_cLabel_tagger (label_tagger),
  KEY fk_cLabel_label (label_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_word_frequence'
--
CREATE TABLE IF NOT EXISTS tbl_word_frequence (
  wf_process int(11) NOT NULL,
  wf_frequence int(11) NOT NULL DEFAULT 0,
  wf_word varchar(50) NOT NULL,
  PRIMARY KEY (wf_process,wf_word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_document'
--
CREATE TABLE IF NOT EXISTS tbl_document (
  document_id int(11) NOT NULL AUTO_INCREMENT,
  document_process int(11) NOT NULL,
  document_text text CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci NOT NULL,
  document_name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  document_size int(11) NOT NULL,
  PRIMARY KEY (document_id),
  UNIQUE(document_name,document_process) COMMENT 'Cant have repeated document name on the same labelling process',
  KEY idx_doc_lp (document_process)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2953 ;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_label'
--
CREATE TABLE IF NOT EXISTS tbl_label (
  label_label varchar(50) NOT NULL,
  PRIMARY KEY (label_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process (
  process_id int(11) NOT NULL AUTO_INCREMENT,
  process_name varchar(50) NOT NULL,
  process_admin int(11) NOT NULL,
  process_aspect_suggestion_algorithm varchar(50) NOT NULL DEFAULT 'none',
  process_translator tinyint(1) NOT NULL DEFAULT 0,
  process_language varchar(10) NOT NULL DEFAULT 'XX',
  process_training_set text CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (process_id),
  UNIQUE KEY unique_name (process_name,process_admin),
  KEY idx_lp_admin (process_admin)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=29 ;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_used_algorithm'
--
CREATE TABLE IF NOT EXISTS tbl_used_algorithm (
  ua_lp int(11) NOT NULL,
  ua_algorithm varchar(50) NOT NULL,
  PRIMARY KEY (ua_lp, ua_algorithm)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_pmi_phrases'
--
CREATE TABLE IF NOT EXISTS tbl_pmi_phrases (
  phrase varchar(100) NOT NULL,
  phrase_count bigint(16) NOT NULL,
  negative_count int(11) NOT NULL,
  positive_count int(11) NOT NULL,
  pmi_lp int(11) NOT NULL,
  PRIMARY KEY (phrase, pmi_lp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_label_options'
--
CREATE TABLE IF NOT EXISTS tbl_label_options (
  labelOpt_label varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'label_options'
--
INSERT INTO tbl_label_options (labelOpt_label) VALUES
('POSITIVE'),
('NEGATIVE'),
('NEUTRAL');


-- --------------------------------------------------------
--
-- Table structure for table 'tbl_aspect'
--
CREATE TABLE IF NOT EXISTS tbl_aspect (
  aspect_doc int(11) NOT NULL,
  aspect_lp int(11) NOT NULL,
  aspect_aspect varchar(100),
  aspect_polarity varchar(50) NOT NULL,
  aspect_polarity_alg varchar(50) NOT NULL,
  aspect_start int(5) NOT NULL,
  aspect_end int(5),
  aspect_number int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (aspect_number),
  UNIQUE(aspect_doc, aspect_lp, aspect_aspect, aspect_polarity_alg, aspect_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_login_attempts'
--
CREATE TABLE IF NOT EXISTS tbl_login_attempts (
  la_user int(11) NOT NULL,
  la_time varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_user'
--
CREATE TABLE IF NOT EXISTS tbl_user (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  user_name varchar(50) NOT NULL,
  user_email varchar(50) NOT NULL,
  user_password char(128) NOT NULL,
  user_salt char(128) NOT NULL,
  user_role varchar(50) NOT NULL DEFAULT 'tagger',
  PRIMARY KEY (user_id),
  UNIQUE email(user_email)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=21 ;
--
-- Dumping data for table 'tbl_user'
--
INSERT INTO tbl_user (user_id, user_name, user_email, user_password, user_salt, user_role) VALUES
(16, 'admin', 'admin@admin.com', 'a9961b585e76dd66a9663dc532ac1127b77ac5b969b5825fa2fd525f1a5e048274e723cdbbe67a43362cd6639b16dc2f5e5b43feb4e549467a7844704b1e704f', '7b20f82f32f9e0ed599cf7c4e2dbb380d28a838386af6c396d95978a50316fcaaff335b085c281b29e10b8eb159565fb389dbe3a75605e0847cc290fd9726de7', 'processAdmin'),
(17, 'mario', 'mario@mario.com', '2f1446a149d4248e816f8c8db7c6d67a5dd08805250c51d59a9a49caecbb778470c427246124f83ac36bdef16da1a84e50685a62fe0c79765002e46a3a8e5dce', '10e378ecf766e16596d03677b62915139fa949d443c8d3ebd6f3350e8caa182cada9a57c268e5f54b374d659bac392fcde52e9e0c856505a55c8389f9508de21', 'tagger'),
(18, 'pedro', 'pedro@pedro.com', '4763d081a86fa8ef0385f3a751012989c0a301e11d99eddcf166c5922a51f2c2fad217fc0da9ff1d3701af73abf42b7536425aa9672b4a09a40992d392643065', 'a76c48c624f8d820df044cf7aba06ae42fd72b7b6b47810c3c974408f5b04579b3d646209ef59a6872b51452bc27ae094ae2d6f40266ab92dce5a7e9ea813a6f', 'tagger'),
(19, 'maria', 'maria@maria.com', '6828a294d06a98dee3b205b542572535851644ae4842fa321a4874c18e09272303df5df0ee594180e8d2e4b871adab955f91cc84579688732e71e0897fd4e53f', 'aaa1549d75346a6992e77be2d2d9d493eb3cceb656ad9a28a66dfe84709670fd606fdeb254a78b1af024e11afacf4144990f68ce0d314a8291896d63d08e8dd2', 'tagger'),
(20, 'rafael', 'rafael@rafael.com', '04f744526afa4d55a752401819715bb0b2ebec98a2b12a3bbf66400091bdf5a4b34b8fcec5be1694c607e2718ee7e5bf876be22e202ac8003052d9edecce7a86', '86675cb42abeb0cd4978d2f3c7155c6efaec7d11e09077af9c81f71c5e621177fc3313ed95d3470dd73da7fec264c3706ed40160cb0720acfe1f3216717592fe', 'tagger');
--
-- Constraints for dumped tables
--
--
-- Constraints for table 'tbl_chosen_label'
--
ALTER TABLE tbl_chosen_label
  ADD CONSTRAINT fk_cLabel_doc FOREIGN KEY (label_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_cLabel_label FOREIGN KEY (label_label) REFERENCES tbl_label (label_label) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_cLabel_tagger FOREIGN KEY (label_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_document'
--
ALTER TABLE tbl_document
  ADD CONSTRAINT fk_doc_lp FOREIGN KEY (document_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_labeling_process'
--
ALTER TABLE tbl_labeling_process
  ADD CONSTRAINT fk_lp_admin FOREIGN KEY (process_admin) REFERENCES tbl_user (user_id) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lp_aspect_suggestion_algorithm FOREIGN KEY (process_aspect_suggestion_algorithm) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_used_algorithm'
--
ALTER TABLE tbl_used_algorithm
	ADD CONSTRAINT fk_ua_algorithm FOREIGN KEY (ua_algorithm) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE,  
	ADD CONSTRAINT fk_ua_lp FOREIGN KEY (ua_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_pmi_phrases'
--
ALTER TABLE tbl_pmi_phrases
  ADD CONSTRAINT fk_pmi_lp FOREIGN KEY (pmi_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_aspect'
--
ALTER TABLE tbl_aspect
  ADD CONSTRAINT fk_aspect_lp FOREIGN KEY (aspect_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_alg FOREIGN KEY (aspect_polarity_alg) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_doc FOREIGN KEY (aspect_doc) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_word_frequence'
--
ALTER TABLE tbl_word_frequence
  ADD CONSTRAINT fk_wf_process FOREIGN KEY (wf_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
