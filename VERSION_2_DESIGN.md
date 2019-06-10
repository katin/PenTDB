# PenTDB - Version 2 Design Notes

### Language
Python


### Database Tables

#### Table: Tests
Stores templates of tests of all kinds, with commands, processing cmds, and usage notes

```sql
CREATE TABLE `test` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `typical_port` int(10) unsigned NOT NULL DEFAULT '0',
  `test_type` varchar(16) NOT NULL DEFAULT '',
  `status_type` varchar(16) NOT NULL DEFAULT '',
  `service` varchar(127) NOT NULL DEFAULT '',
  `title` varchar(127) NOT NULL DEFAULT '',
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `usage_notes` longtext,
  PRIMARY KEY (`tid`),
  KEY `main` (`typical_port`,`service`),
  KEY `auxiliary` (`test_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
#### Table: Sets




#### Table: Hosts




#### Table: Sessions




#### Table: Vulns




#### Table: Login_points




#### Table: Credentials




#### Table: References


