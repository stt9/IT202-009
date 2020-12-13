CREATE TABLE Questions
(
    id        int auto_increment,
    question  TEXT,
    survey_id int,
    user_id   int,
    primary key (id),
    FOREIGN KEY (user_id) REFERENCES Users (id),
    FOREIGN KEY (survey_id) REFERENCES Survey (id)
)