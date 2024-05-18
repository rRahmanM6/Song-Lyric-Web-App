CREATE TABLE IF NOT EXISTS  `UserSongs`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `song_label` VARCHAR(255) NOT NULL,
    `created`    timestamp default current_timestamp,
    `modified`   timestamp default current_timestamp on update current_timestamp,
    FOREIGN KEY (`user_id`) REFERENCES Users(`id`),
    FOREIGN KEY (`song_label`) REFERENCES SONGS(`label`)
    )