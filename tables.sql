
CREATE TABLE Users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(255) UNIQUE NOT NULL,
                       email VARCHAR(255) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;    -- Necessary as remote errors relating to FK constraints occur otherwise.


CREATE TABLE Topics (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        title VARCHAR(255) NOT NULL,
                        description TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE -- FK constraints
) ENGINE=InnoDB;


CREATE TABLE Votes (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id INT NOT NULL,
                       topic_id INT NOT NULL,
                       vote_type ENUM('up', 'down') NOT NULL,
                       voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                       FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE Comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          user_id INT NOT NULL,
                          topic_id INT NOT NULL,
                          comment TEXT NOT NULL,
                          commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                          FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE
) ENGINE=InnoDB;

