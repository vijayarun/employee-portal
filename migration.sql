-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` bigint(20) NOT NULL,
  `employee_key` varchar(36) DEFAULT NULL,
  `employee_code` varchar(45) DEFAULT NULL,
  `employee_name` varchar(245) DEFAULT NULL,
  `experience` int(11) DEFAULT '0' COMMENT 'in months',
  `salary` double(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `employee_personal`
--

CREATE TABLE `employee_personal` (
  `employee_personal_id` bigint(20) NOT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `email` varchar(254) DEFAULT NULL,
  `mobile_number` varchar(10) DEFAULT NULL,
  `gender` enum('MALE','FEMALE') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `import`
--

CREATE TABLE `import` (
  `import_id` bigint(20) NOT NULL,
  `import_key` varchar(36) DEFAULT NULL,
  `import_file_name` varchar(245) DEFAULT NULL,
  `import_file_path` varchar(245) DEFAULT NULL,
  `import_status` tinyint(1) DEFAULT '1' COMMENT 'TYPE 1: PENDING\nTYPE 2: INITIALIZED\nTYPE 3: PROCESSED',
  `import_log_json` longtext,
  `created_at` datetime DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- Indexes for table `employee_personal`
--
ALTER TABLE `employee_personal`
  ADD PRIMARY KEY (`employee_personal_id`),
  ADD KEY `fk_employee_personal_employee_idx` (`employee_id`);

--
-- Indexes for table `import`
--
ALTER TABLE `import`
  ADD PRIMARY KEY (`import_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_personal`
--
ALTER TABLE `employee_personal`
  MODIFY `employee_personal_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `import`
--
ALTER TABLE `import`
  MODIFY `import_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_personal`
--
ALTER TABLE `employee_personal`
  ADD CONSTRAINT `fk_employee_personal_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;