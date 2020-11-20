CREATE TABLE IF NOT EXISTS `rbs_credit_orders` (
    `ID` int(50) NOT NULL AUTO_INCREMENT,
    `DATE_CREATE` varchar(50) NOT NULL,
    `DATE_UPDATE` varchar(50) NOT NULL,
    `USER_INFO` varchar(1000) NOT NULL,
    `PAYMENT_SUM` varchar(200) NOT NULL,
    `BANK_SUM` varchar(200) NOT NULL,
    `BANK_ORDER_ID` varchar(200) NOT NULL,
    `BANK_ORDER_STATUS` varchar(200) NOT NULL,
    `CMS_ORDER_ID` varchar(200) NOT NULL,
    `CMS_PAYMENT_ID` varchar(200) NOT NULL,
    `CMS_ORDER_STATUS` varchar(200) NOT NULL,
     PRIMARY KEY (`ID`)
) Engine=InnoDB;
