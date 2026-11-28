-- SHULETECH: M-Pesa (Daraja STK push) payment requests. Run once on each server (local + live).
CREATE TABLE IF NOT EXISTS `mpesa_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `student_session_id` int(11) DEFAULT NULL,
  `merchant_request_id` varchar(100) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `account_ref` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `result_code` varchar(10) DEFAULT NULL,
  `result_desc` varchar(255) DEFAULT NULL,
  `mpesa_receipt` varchar(40) DEFAULT NULL,
  `params` longtext,
  `invoice_json` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkout_request_id` (`checkout_request_id`),
  KEY `student_id` (`student_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
