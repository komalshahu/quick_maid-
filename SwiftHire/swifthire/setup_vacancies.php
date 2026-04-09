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
    ['Executive Private Chef', 'The Palm Residence', 'Dubai', 'Palm Jumeirah', 'Cooking', 'AED 8,000 - 12,000', 'Looking for a professional chef specialized in Mediterranean and Arabic cuisine for a private villa.'],
    ['Luxury Home Cleaning Specialist', 'Elite Estates UK', 'United Kingdom', 'London', 'Cleaning', '£2,200 - 2,800', 'Experienced cleaner needed for high-end residential properties in Central London.'],
    ['Professional Nanny / Babysitter', 'Singapore Family Care', 'Singapore', 'Orchard Road', 'Babysitting', 'SGD 3,500 - 4,500', 'Full-time nanny required for 2 children. Must have educational background or relevant experience.'],
    ['All-Rounder House Help', 'Muscat Domestic Hub', 'Oman', 'Muscat', 'All-Rounder', 'OMR 350 - 500', 'Live-in house help needed for a large family. Must be comfortable with cooking and cleaning.'],
    ['Housekeeper (Senior Care Focused)', 'Toronto Home Support', 'Canada', 'Toronto', 'Cleaning', 'CAD 3,000 - 4,000', 'Bilingual housekeeper needed for elderly couple. Primary tasks include cleaning and meal prep.'],
    ['Bakery Assistant & Cook', 'Melbourne Eats', 'Australia', 'Melbourne', 'Cooking', 'AUD 4,500 - 5,500', 'Assistant cook needed for a busy local bakery specialized in home-made pies.'],
    ['VIP Personal Assistant / Maid', 'Riyadh Royal Services', 'Saudi Arabia', 'Riyadh', 'All-Rounder', 'SAR 4,500 - 6,000', 'Seeking highly professional maid for VIP household. Arabic speaking preferred.'],
    ['Infant Care Specialist', 'Doha Modern Nannies', 'Qatar', 'Doha', 'Babysitting', 'QAR 5,000 - 7,000', 'Qualified babysitter for newborn care. 24/7 support role (shifts available).']
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
