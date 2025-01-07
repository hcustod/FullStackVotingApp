CREATE TABLE Users (
                       id INTEGER PRIMARY KEY AUTOINCREMENT,
                       username TEXT UNIQUE NOT NULL,
                       email TEXT UNIQUE NOT NULL,
                       password TEXT NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Topics (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        title TEXT NOT NULL,
                        description TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

CREATE TABLE Votes (
                       id INTEGER PRIMARY KEY AUTOINCREMENT,
                       user_id INTEGER NOT NULL,
                       topic_id INTEGER NOT NULL,
                       vote_type TEXT CHECK(vote_type IN ('up', 'down')) NOT NULL,
                       voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                       FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE
);

CREATE TABLE Comments (
                          id INTEGER PRIMARY KEY AUTOINCREMENT,
                          user_id INTEGER NOT NULL,
                          topic_id INTEGER NOT NULL,
                          comment TEXT NOT NULL,
                          commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
                          FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE
);
