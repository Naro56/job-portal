-- Create database
CREATE DATABASE IF NOT EXISTS findwork;
USE findwork;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('jobseeker', 'recruiter') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('full-time', 'internship', 'part-time') NOT NULL,
    location VARCHAR(100) NOT NULL,
    salary VARCHAR(50) NOT NULL,
    experience_required VARCHAR(50) NOT NULL,
    company_id INT NOT NULL,
    is_closed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES users(id)
);

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('applied', 'selected', 'rejected') DEFAULT 'applied',
    applied_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resume_path VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    additional_info TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
);

-- -- Insert dummy users
-- -- Password for both users is 'password123' (hashed)
INSERT INTO users (name, email, password, role) VALUES
('John Doe', 'jobseeker@example.com', '123456', 'jobseeker'),
('Tech Corp', 'recruiter@example.com', '123456', 'recruiter');

-- -- Insert dummy jobs
INSERT INTO jobs (title, description, type, location, salary, experience_required, company_id) VALUES
('Senior PHP Developer', 'Looking for an experienced PHP developer...', 'full-time', 'Mumbai', '₹15L - ₹20L', '3-5 years', 2),
('Web Development Intern', 'Internship opportunity for web development...', 'internship', 'Remote', '₹25K/month', '0-1 year', 2);




-- -- Create database
-- CREATE DATABASE IF NOT EXISTS findwork;
-- USE findwork;

-- -- Users table
-- CREATE TABLE IF NOT EXISTS users (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     name VARCHAR(100) NOT NULL,
--     email VARCHAR(100) UNIQUE NOT NULL,
--     password VARCHAR(255) NOT NULL,
--     role ENUM('jobseeker', 'recruiter') NOT NULL,
--     company_name VARCHAR(255) DEFAULT NULL,
--     company_website VARCHAR(255) DEFAULT NULL,
--     company_description TEXT DEFAULT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Jobs table
-- CREATE TABLE IF NOT EXISTS jobs (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     title VARCHAR(200) NOT NULL,
--     description TEXT NOT NULL,
--     type ENUM('full-time', 'internship', 'part-time') NOT NULL,
--     location VARCHAR(100) NOT NULL,
--     salary VARCHAR(50) NOT NULL,
--     experience_required VARCHAR(50) NOT NULL,
--     company_id INT NOT NULL,
--     is_closed BOOLEAN DEFAULT FALSE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (company_id) REFERENCES users(id)
-- );

-- -- Applications table
-- CREATE TABLE IF NOT EXISTS applications (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     user_id INT NOT NULL,
--     job_id INT NOT NULL,
--     status ENUM('applied', 'selected', 'rejected') DEFAULT 'applied',
--     applied_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     resume_path VARCHAR(255) NOT NULL,
--     phone VARCHAR(20),
--     additional_info TEXT,
--     FOREIGN KEY (user_id) REFERENCES users(id),
--     FOREIGN KEY (job_id) REFERENCES jobs(id)
-- );

-- -- Insert dummy users
-- -- Password for both users is 'password123' (hashed)
-- INSERT INTO users (name, email, password, role, company_name, company_website, company_description) VALUES
-- ('John Doe', 'jobseeker@example.com', '123456', 'jobseeker', NULL, NULL, NULL),
-- ('Tech Corp', 'recruiter@example.com', '123456', 'recruiter', 'Tech Corp Pvt Ltd', 'https://techcorp.example.com', 'A leading tech company hiring top talent.');

-- -- Insert dummy jobs
-- INSERT INTO jobs (title, description, type, location, salary, experience_required, company_id) VALUES
-- ('Senior PHP Developer', 'Looking for an experienced PHP developer...', 'full-time', 'Mumbai', '₹15L - ₹20L', '3-5 years', 2),
-- ('Web Development Intern', 'Internship opportunity for web development...', 'internship', 'Remote', '₹25K/month', '0-1 year', 2);
