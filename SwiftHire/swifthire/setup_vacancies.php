<?php
require 'db.php';

// 1. Create Vacancies Table
$sql = "
CREATE TABLE IF NOT EXISTS vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(150) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    category ENUM('Cleaning', 'Cooking', 'Babysitting', 'All-Rounder') NOT NULL,
    salary_range VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'vacancies' created successfully.\n";
} else {
    die("Error creating table: " . $conn->error . "\n");
}

// 2. Clear existing (just for seeding)
$conn->query("TRUNCATE TABLE vacancies");

// 3. Seed Mock Data
$seed_data = [
    ['Executive Private Chef', 'The Palm Residence', 'Dubai', 'Palm Jumeirah', 'Cooking', '₹ 1,80,000 - 2,70,000', 'Looking for a professional chef specialized in Mediterranean and Arabic cuisine for a private villa.'],
    ['Luxury Home Cleaning Specialist', 'Elite Estates UK', 'United Kingdom', 'London', 'Cleaning', '₹ 2,35,000 - 3,00,000', 'Experienced cleaner needed for high-end residential properties in Central London.'],
    ['Professional Nanny / Babysitter', 'Singapore Family Care', 'Singapore', 'Orchard Road', 'Babysitting', '₹ 2,15,000 - 2,80,000', 'Full-time nanny required for 2 children. Must have educational background or relevant experience.'],
    ['All-Rounder House Help', 'Muscat Domestic Hub', 'Oman', 'Muscat', 'All-Rounder', '₹ 75,000 - 1,00,000', 'Live-in house help needed for a large family. Must be comfortable with cooking and cleaning.'],
    ['Housekeeper (Senior Care Focused)', 'Toronto Home Support', 'Canada', 'Toronto', 'Cleaning', '₹ 1,80,000 - 2,40,000', 'Bilingual housekeeper needed for elderly couple. Primary tasks include cleaning and meal prep.'],
    ['Bakery Assistant & Cook', 'Melbourne Eats', 'Australia', 'Melbourne', 'Cooking', '₹ 2,40,000 - 3,00,000', 'Assistant cook needed for a busy local bakery specialized in home-made pies.'],
    ['VIP Personal Assistant / Maid', 'Riyadh Royal Services', 'Saudi Arabia', 'Riyadh', 'All-Rounder', '₹ 1,00,000 - 1,35,000', 'Seeking highly professional maid for VIP household. Arabic speaking preferred.'],
    ['Infant Care Specialist', 'Doha Modern Nannies', 'Qatar', 'Doha', 'Babysitting', '₹ 1,15,000 - 1,60,000', 'Qualified babysitter for newborn care. 24/7 support role (shifts available).'],
    ['Full-Time Maid', 'Mumbai Metro Services', 'India', 'Mumbai', 'Cleaning', '₹ 15,000 - 20,000', 'Experienced maid needed for an apartment in South Mumbai. Complete home cleaning and basic grocery shopping.'],
    ['Live-in Cook (North Indian)', 'Delhi Homemakers', 'India', 'New Delhi', 'Cooking', '₹ 18,000 - 25,000', 'Looking for an expert cook well-versed in vegetarian North Indian cuisine for a family of four.'],
    ['Reliable Nanny', 'Bangalore Care', 'India', 'Bangalore', 'Babysitting', '₹ 20,000 - 28,000', 'Full-time nanny needed for a 3-year-old child. Responsible for meals, drop-off to playschool, and light housekeeping.']
];

$stmt = $conn->prepare("INSERT INTO vacancies (job_title, company_name, country, location, category, salary_range, description) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($seed_data as $job) {
    $stmt->bind_param("sssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5], $job[6]);
    $stmt->execute();
}

echo "Successfully seeded " . count($seed_data) . " vacancies.\n";

$stmt->close();
$conn->close();
?>
