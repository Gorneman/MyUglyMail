DROP TABLE IF EXISTS mail_counter;

CREATE TABLE mail_counter (
  e_address VARCHAR(255) NOT NULL PRIMARY KEY,
  in_count INTEGER NOT NULL DEFAULT 0,
  out_count INTEGER NOT NULL DEFAULT 0    
);
