<?php

use Propel\Generator\Manager\MigrationManager;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1477653891.
 * Generated on 2016-10-28 11:24:51 
 */
class PropelMigration_1477653891
{
    public $comment = '';

    public function preUp(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postUp(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    public function preDown(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postDown(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'fresh_fridge' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `family`
(
    `family_id` INTEGER NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`family_id`)
) ENGINE=InnoDB;

CREATE TABLE `user`
(
    `user_id` INTEGER NOT NULL AUTO_INCREMENT,
    `family_id` INTEGER NOT NULL,
    `access_token` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`user_id`),
    UNIQUE INDEX `user_u_c296eb` (`access_token`),
    INDEX `user_fi_0a86bd` (`family_id`),
    CONSTRAINT `user_fk_0a86bd`
        FOREIGN KEY (`family_id`)
        REFERENCES `family` (`family_id`)
) ENGINE=InnoDB;

CREATE TABLE `item`
(
    `item_id` INTEGER NOT NULL AUTO_INCREMENT,
    `family_id` INTEGER NOT NULL,
    PRIMARY KEY (`item_id`),
    INDEX `item_fi_0a86bd` (`family_id`),
    CONSTRAINT `item_fk_0a86bd`
        FOREIGN KEY (`family_id`)
        REFERENCES `family` (`family_id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'fresh_fridge' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `family`;

DROP TABLE IF EXISTS `user`;

DROP TABLE IF EXISTS `item`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}