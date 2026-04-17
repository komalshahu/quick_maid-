<?php
require 'db.php';

// 1. Drop existing table to update schema
$conn->query("DROP TABLE IF EXISTS vacancies");

// 2. Create Vacancies Table with new columns
$sql = "
CREATE TABLE vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(150) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    category ENUM('Cleaning', 'Cooking', 'Babysitting', 'All-Rounder') NOT NULL,
    salary_range VARCHAR(50) NOT NULL,
    working_time VARCHAR(100) NOT NULL,
    working_days VARCHAR(100) NOT NULL,
    work_types TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'vacancies' created successfully.\n";
} else {
    die("Error creating table: " . $conn->error . "\n");
}

// 3. Seed Mock Data
$seed_data = [
    ['Full-Time Maid', 'Mumbai Metro Services', 'India', 'Mumbai', 'Cleaning', '₹ 15,000 - 20,000', '8:00 AM - 7:00 PM', 'Mon - Sat', 'Cloth cleaning, dinner plates cleaning, floor sweeping, toilet cleaning, dusting', 'Experienced maid needed for an apartment in South Mumbai. Complete home cleaning and basic grocery shopping.'],
    ['Live-in Cook (North Indian)', 'Delhi Homemakers', 'India', 'New Delhi', 'Cooking', '₹ 18,000 - 25,000', 'Live-in / Flexible', '7 Days/Week', 'Cooking lunch and dinner, cutting vegetables, washing dishes, kitchen cleaning', 'Looking for an expert cook well-versed in vegetarian North Indian cuisine for a family of four.'],
    ['Reliable Nanny', 'Bangalore Care', 'India', 'Bangalore', 'Babysitting', '₹ 20,000 - 28,000', '9:00 AM - 6:00 PM', 'Mon - Sat', 'Baby care, feeding children, light ironing, playing with kids, washing baby clothes', 'Full-time nanny needed for a 3-year-old child. Responsible for meals, drop-off to playschool, and light housekeeping.'],
    ['All-Rounder House Help', 'Chennai Domestic Hub', 'India', 'Chennai', 'All-Rounder', '₹ 18,000 - 22,000', 'Live-in / Flexible', '6 Days/Week', 'Cloth cleaning, dinner plates washing, sweeping, mopping, basic cooking prep', 'Live-in house help needed for a large family. Must be comfortable with cooking and cleaning.'],
    ['Part-time Cleaner', 'Pune Elite Cleaners', 'India', 'Pune', 'Cleaning', '₹ 8,000 - 12,000', '9:00 AM - 1:00 PM', 'Mon - Sat', 'Floor mopping, sweeping, toilet cleaning, dusting, dinner plates cleaning', 'Part-time clean needed for a 2BHK flat.'],
    ['Housekeeper (Senior Care Focused)', 'Kolkata Home Support', 'India', 'Kolkata', 'Cleaning', '₹ 15,000 - 18,000', '9:00 AM - 6:00 PM', 'Mon - Fri', 'Cloth washing, dinner plates, maintaining hygiene, room cleaning', 'Housekeeper needed for elderly couple. Primary tasks include cleaning and meal prep.'],
    ['Professional Chef (South Indian)', 'Hyderabad Eats', 'India', 'Hyderabad', 'Cooking', '₹ 20,000 - 30,000', '7:00 AM - 3:00 PM', 'Tue - Sun', 'Cooking breakfast and lunch, washing vessels, kitchen maintenance', 'Cook needed for a family. Specialized in South Indian cuisine.'],
    ['VIP Personal Assistant / Maid', 'Ahmedabad Royal Services', 'India', 'Ahmedabad', 'All-Rounder', '₹ 25,000 - 35,000', 'Live-in / Flexible', '6 Days/Week', 'Deep cleaning, cloth washing and ironing, cooking, managing household inventory', 'Seeking highly professional maid for VIP household.'],
    ['Infant Care Specialist', 'Jaipur Modern Nannies', 'India', 'Jaipur', 'Babysitting', '₹ 18,000 - 25,000', 'Shift Basis', 'Flexible', 'Infant feeding, changing diapers, baby clothes cleaning, sterilizing bottles', 'Qualified babysitter for newborn care. Shifts available.'],
    ['Morning Maid', 'Surat Cleaning Co', 'India', 'Surat', 'Cleaning', '₹ 7,000 - 10,000', '7:00 AM - 11:00 AM', 'Mon - Sat', 'Cloth cleaning, dinner plates, sweeping and mopping, basic dusting', 'Morning maid required for basic household chores.'],
    ['Household Manager', 'Goa Elite Helpers', 'India', 'Goa', 'All-Rounder', '₹ 22,000 - 30,000', '9:00 AM - 6:00 PM', 'Mon - Sat', 'Managing household staff, cooking prep, deep cleaning, organizing', 'Experienced household manager for a private villa in Goa.'],
    ['Daycare Assistant', 'Noida Little Steps', 'India', 'Noida', 'Babysitting', '₹ 15,000 - 18,000', '8:00 AM - 4:00 PM', 'Mon - Fri', 'Playing with toddlers, feeding, washing toys, sanitizing area', 'Assistant needed for a small home daycare.'],
    ['Commercial Cleaner', 'Gurgaon Corporate Clean', 'India', 'Gurgaon', 'Cleaning', '₹ 12,000 - 16,000', '6:00 PM - 2:00 AM', 'Mon - Sat', 'Office cleaning, floor buffing, trash removal, sanitizing desks', 'Evening shift cleaner for corporate office spaces.'],
    ['Dietary Cook', 'Chandigarh Health Meals', 'India', 'Chandigarh', 'Cooking', '₹ 25,000 - 32,000', '7:00 AM - 3:00 PM', 'Mon - Sat', 'Preparing keto and vegan meals, kitchen cleaning, grocery planning', 'Specialized cook required for preparing dietary specific meals.'],
    ['Elderly Companion & Helper', 'Lucknow Senior Care', 'India', 'Lucknow', 'All-Rounder', '₹ 14,000 - 18,000', '10:00 AM - 6:00 PM', 'Mon - Sat', 'Administering medicine, walking assistance, light cooking, room cleaning', 'Looking for a compassionate helper for an elderly person.'],
    ['Deep Cleaning Specialist', 'Indore Sparkle Homes', 'India', 'Indore', 'Cleaning', '₹ 16,000 - 20,000', 'Flexible Shifts', 'Flexible', 'Deep scrubbing, window washing, carpet cleaning, stain removal', 'Professional deep cleaner for seasonal home cleaning contracts.'],
    ['Weekend Babysitter', 'Bhopal Weekend Nannies', 'India', 'Bhopal', 'Babysitting', '₹ 5,000 - 8,000', 'Weekend Shifts', 'Sat - Sun', 'Childcare, light snacks prep, park visits, toys cleaning', 'Part-time weekend babysitter for a single child.']
];

$stmt = $conn->prepare("INSERT INTO vacancies (job_title, company_name, country, location, category, salary_range, working_time, working_days, work_types, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($seed_data as $job) {
    $stmt->bind_param("ssssssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5], $job[6], $job[7], $job[8], $job[9]);
    $stmt->execute();
}

echo "Successfully seeded " . count($seed_data) . " vacancies.\n";

$stmt->close();
$conn->close();
?>
