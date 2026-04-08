CREATE TABLE job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female', 'others') NOT NULL,
    areacode VARCHAR(10),
    phone VARCHAR(20) NOT NULL,
    age INT NOT NULL,
    startdate DATE NOT NULL,
    address VARCHAR(255) NOT NULL,
    address2 VARCHAR(255),
    message TEXT NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
