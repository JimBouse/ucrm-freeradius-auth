CREATE TABLE `queue_name` (
  `wanIP` varchar(100) NOT NULL,
  `www_port` int(11) NOT NULL,
  `mac` varchar(17) NOT NULL,
  `queue` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `hostname` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='This table contains a reference to the queue name created by the Mikrotik router upon DHCP lease.';


ALTER TABLE `queue_name`
  ADD PRIMARY KEY (`mac`);
