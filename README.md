# dontu

SQL to update DB

```SQL
CREATE TABLE `history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cpu_win` decimal(10,2) DEFAULT '0.00',
  `cpu_ubt` decimal(10,2) DEFAULT '0.00',
  `mem_win` decimal(10,2) DEFAULT '0.00',
  `mem_ubt` decimal(10,2) DEFAULT '0.00',
  `dsk_win` decimal(10,2) DEFAULT '0.00',
  `dsk_ubt` decimal(10,2) DEFAULT '0.00',
  `read_date` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
```