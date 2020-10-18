CREATE TABLE IF NOT EXISTS `Userid` (
        `id` INT NOT NULL AUTO_INCREMENT
        ,`username` VARCHAR(100) NOT NULL
        ,`password` VARCHAR(60) NOT NULL
        ,`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ,PRIMARY KEY (`id`)
        ,UNIQUE (`username`)
        )
