IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'exp_blocks_blockdefinition' AND table_schema = DATABASE()) THEN
CREATE TABLE `exp_blocks_blockdefinition` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `shortname` tinytext NOT NULL,
  `name` text NOT NULL,
  `instructions` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'exp_blocks_atomdefinition' AND table_schema = DATABASE()) THEN
CREATE TABLE `exp_blocks_atomdefinition` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `blockdefinition_id` bigint(20) NOT NULL,
  `shortname` tinytext NOT NULL,
  `name` text NOT NULL,
  `instructions` text NOT NULL,
  `order` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `settings` text,
  PRIMARY KEY (`id`),
  KEY `fk_blocks_atomdefinition_blockdefinition` (`blockdefinition_id`),
  CONSTRAINT `fk_blocks_atomdefinition_blockdefinition` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'exp_blocks_block' AND table_schema = DATABASE()) THEN
CREATE TABLE `exp_blocks_block` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `blockdefinition_id` bigint(20) NOT NULL,
  `site_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `field_id` int(6) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_blocks_blockdefinition_block` (`blockdefinition_id`),
  CONSTRAINT `fk_blocks_blockdefinition_block` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`),
  KEY `ix_blocks_block_siteid_entryid_fieldid` (`site_id`,`entry_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'exp_blocks_atom' AND table_schema = DATABASE()) THEN
CREATE TABLE `exp_blocks_atom` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `block_id` bigint(20) NOT NULL,
  `atomdefinition_id` bigint(20) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_blocks_atom_blockid_atomdefinitionid` (`block_id`,`atomdefinition_id`),
  KEY `fk_blocks_atom_block` (`atomdefinition_id`),
  CONSTRAINT `fk_blocks_atom_block` FOREIGN KEY (`block_id`) REFERENCES `exp_blocks_block` (`id`),
  CONSTRAINT `fk_blocks_atom_atomdefinition` FOREIGN KEY (`atomdefinition_id`) REFERENCES `exp_blocks_atomdefinition` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;

IF NOT EXISTS (SELECT * FROM information_schema.tables WHERE table_name = 'exp_blocks_blockfieldusage' AND table_schema = DATABASE()) THEN
CREATE TABLE `exp_blocks_blockfieldusage` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `field_id` int(6) NOT NULL,
  `blockdefinition_id` bigint(20) NOT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_blocks_blockfieldusage_fieldid_blockdefinitionid` (`field_id`,`blockdefinition_id`),
  CONSTRAINT `fk_blocks_blockfieldusage_blockdefinition` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
END IF;
