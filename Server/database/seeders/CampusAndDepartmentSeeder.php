<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CampusAndDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Campuses exist
        $dessieCampus = University::updateOrCreate(
            ['code' => 'DESSIE'],
            [
                'name' => 'Dessie Campus',
                'is_active' => true,
            ]
        );

        $kombolchaCampus = University::updateOrCreate(
            ['code' => 'KOCHA'],
            [
                'name' => 'Kombolcha Campus',
                'is_active' => true,
            ]
        );

        // 2. Dessie Campus Departments
        $dessieDepartments = [
            // College of Agriculture
            ['college' => 'College of Agriculture', 'name' => 'Agricultural Economics', 'code' => 'AGEC', 'total_years' => 4],
            ['college' => 'College of Agriculture', 'name' => 'Animal Science', 'code' => 'ANSC', 'total_years' => 4],
            ['college' => 'College of Agriculture', 'name' => 'Forestry', 'code' => 'FORE', 'total_years' => 4],
            ['college' => 'College of Agriculture', 'name' => 'Natural Resource Management', 'code' => 'NRM', 'total_years' => 4],
            ['college' => 'College of Agriculture', 'name' => 'Plant Science', 'code' => 'PLSC', 'total_years' => 4],
            ['college' => 'College of Agriculture', 'name' => 'Rural Development and Agricultural Extension', 'code' => 'RDAE', 'total_years' => 4],

            // College of Business and Economics
            ['college' => 'College of Business and Economics', 'name' => 'Accounting and Finance', 'code' => 'ACFN', 'total_years' => 4],
            ['college' => 'College of Business and Economics', 'name' => 'Cooperative Study', 'code' => 'COOP', 'total_years' => 4],
            ['college' => 'College of Business and Economics', 'name' => 'Economics', 'code' => 'ECON', 'total_years' => 4],
            ['college' => 'College of Business and Economics', 'name' => 'Management', 'code' => 'MGMT', 'total_years' => 4],
            ['college' => 'College of Business and Economics', 'name' => 'Marketing Management', 'code' => 'MKTM', 'total_years' => 4],

            // College of Medicine and Health Science
            ['college' => 'College of Medicine and Health Science', 'name' => 'Pharmacy', 'code' => 'PHAR', 'total_years' => 5],
            ['college' => 'College of Medicine and Health Science', 'name' => 'Environmental Health Sciences', 'code' => 'ENHS', 'total_years' => 4],
            ['college' => 'College of Medicine and Health Science', 'name' => 'Medical Laboratory Sciences', 'code' => 'MEDL', 'total_years' => 4],

            // College of Natural Science
            ['college' => 'College of Natural Science', 'name' => 'Chemistry', 'code' => 'CHEM', 'total_years' => 4],
            ['college' => 'College of Natural Science', 'name' => 'Geology', 'code' => 'GEOL', 'total_years' => 4],
            ['college' => 'College of Natural Science', 'name' => 'Mathematics', 'code' => 'MATH', 'total_years' => 4],
            ['college' => 'College of Natural Science', 'name' => 'Physics', 'code' => 'PHYS', 'total_years' => 4],
            ['college' => 'College of Natural Science', 'name' => 'Statistics', 'code' => 'STAT', 'total_years' => 4],

            // Freshman
            ['college' => 'Freshman Program', 'name' => 'Freshman Program', 'code' => 'FRSH', 'total_years' => 1],

            // Institute of Teachers' Education and Behavioural Science
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Curriculum and Instruction', 'code' => 'EDCI', 'total_years' => 4],
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Educational Planning and Management', 'code' => 'EDPM', 'total_years' => 4],
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Psychology', 'code' => 'PSYC', 'total_years' => 4],
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Psychology (Early Childhood Care and Education)', 'code' => 'ECCE', 'total_years' => 4],
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Psychology (Special Needs and Inclusive Education)', 'code' => 'SNIE', 'total_years' => 4],
            ['college' => 'Institute of Teachers\' Education and Behavioural Science', 'name' => 'Teacher\'s Development Program', 'code' => 'TDP', 'total_years' => 4],

            // School of Arts
            ['college' => 'School of Arts', 'name' => 'Music', 'code' => 'MUSC', 'total_years' => 4],
            ['college' => 'School of Arts', 'name' => 'Theatre', 'code' => 'THTR', 'total_years' => 4],

            // School of Bioscience and Biotechnology
            ['college' => 'School of Bioscience and Biotechnology', 'name' => 'Biology', 'code' => 'BIOL', 'total_years' => 4],
            ['college' => 'School of Bioscience and Biotechnology', 'name' => 'Biotechnology', 'code' => 'BIOT', 'total_years' => 4],

            // School of Governance
            ['college' => 'School of Governance', 'name' => 'Civic and Ethical Education', 'code' => 'CVET', 'total_years' => 4],
            ['college' => 'School of Governance', 'name' => 'Peace and Development Studies', 'code' => 'PDEV', 'total_years' => 4],
            ['college' => 'School of Governance', 'name' => 'Political Science and International Relations', 'code' => 'PSIR', 'total_years' => 4],

            // School of Language Studies and Communication
            ['college' => 'School of Language Studies and Communication', 'name' => 'Arabic Language and Literature', 'code' => 'ARAB', 'total_years' => 4],
            ['college' => 'School of Language Studies and Communication', 'name' => 'Journalism and Communication', 'code' => 'JOUR', 'total_years' => 4],
            ['college' => 'School of Language Studies and Communication', 'name' => 'English Language and Literature', 'code' => 'ENG', 'total_years' => 4],
            ['college' => 'School of Language Studies and Communication', 'name' => 'Ethiopian Language(s) and Literature - Afan Oromo', 'code' => 'ELLO', 'total_years' => 4],
            ['college' => 'School of Language Studies and Communication', 'name' => 'Ethiopian Language(s) and Literature - Amharic', 'code' => 'ELLA', 'total_years' => 4],
            ['college' => 'School of Language Studies and Communication', 'name' => 'Ethiopian Language(s) and Literature - Geez Language Unit', 'code' => 'ELLG', 'total_years' => 4],

            // School of Law
            ['college' => 'School of Law', 'name' => 'School of Law', 'code' => 'LAW', 'total_years' => 5],

            // School of Medicine
            ['college' => 'School of Medicine', 'name' => 'Anesthesiology', 'code' => 'ANES', 'total_years' => 4],
            ['college' => 'School of Medicine', 'name' => 'Medicine', 'code' => 'MED', 'total_years' => 7],

            // School of Nursing and Midwifery
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Adult Health Nursing', 'code' => 'AHN', 'total_years' => 4],
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Comprehensive Nursing', 'code' => 'COMPN', 'total_years' => 4],
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Emergency & Critical Care Nursing', 'code' => 'ECCN', 'total_years' => 4],
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Midwifery', 'code' => 'MIDW', 'total_years' => 4],
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Pediatrics Nursing', 'code' => 'PEDN', 'total_years' => 4],
            ['college' => 'School of Nursing and Midwifery', 'name' => 'Psychiatry Nursing', 'code' => 'PSYN', 'total_years' => 4],

            // School of Public Health
            ['college' => 'School of Public Health', 'name' => 'Health Informatics', 'code' => 'HINF', 'total_years' => 4],
            ['college' => 'School of Public Health', 'name' => 'Public Health', 'code' => 'PUBH', 'total_years' => 4],

            // School of Social Science
            ['college' => 'School of Social Science', 'name' => 'Geography', 'code' => 'GEOG', 'total_years' => 4],
            ['college' => 'School of Social Science', 'name' => 'History', 'code' => 'HIST', 'total_years' => 4],
            ['college' => 'School of Social Science', 'name' => 'Social Anthropology', 'code' => 'ANTH', 'total_years' => 4],
            ['college' => 'School of Social Science', 'name' => 'Sociology', 'code' => 'SOC', 'total_years' => 4],
            ['college' => 'School of Social Science', 'name' => 'Tourism and Hotel Management', 'code' => 'THM', 'total_years' => 4],

            // School of Sport Science Academy
            ['college' => 'School of Sport Science Academy', 'name' => 'Sport Science', 'code' => 'SPRT', 'total_years' => 4],

            // School of Veterinary Medicine
            ['college' => 'School of Veterinary Medicine', 'name' => 'Laboratory Technology', 'code' => 'LABT', 'total_years' => 4],
            ['college' => 'School of Veterinary Medicine', 'name' => 'Veterinary Medicine', 'code' => 'VETM', 'total_years' => 6],
        ];

        foreach ($dessieDepartments as $dept) {
            Department::updateOrCreate(
                [
                    'university_id' => $dessieCampus->id,
                    'code' => $dept['code'],
                ],
                [
                    'college' => $dept['college'],
                    'name' => $dept['name'],
                    'total_years' => $dept['total_years'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Kombolcha Campus Departments
        $kombolchaDepartments = [
            // College of Informatics
            ['college' => 'College of Informatics', 'name' => 'Computer Science', 'code' => 'CS', 'total_years' => 4],
            ['college' => 'College of Informatics', 'name' => 'Information Systems', 'code' => 'IS', 'total_years' => 4],
            ['college' => 'College of Informatics', 'name' => 'Information Technology', 'code' => 'IT', 'total_years' => 4],
            ['college' => 'College of Informatics', 'name' => 'Software Engineering', 'code' => 'SE', 'total_years' => 5],

            // Department of Pre-engineering
            ['college' => 'Department of Pre-engineering', 'name' => 'Pre-Engineering', 'code' => 'PREENG', 'total_years' => 1],

            // School of Electrical and Computer Engineering
            ['college' => 'School of Electrical and Computer Engineering', 'name' => 'Electrical and Computer Engineering', 'code' => 'ECE', 'total_years' => 5],

            // School of Chemical and Mechanical
            ['college' => 'School of Chemical and Mechanical', 'name' => 'Chemical Engineering', 'code' => 'CHE', 'total_years' => 5],
            ['college' => 'School of Chemical and Mechanical', 'name' => 'Industrial Engineering', 'code' => 'IE', 'total_years' => 5],
            ['college' => 'School of Chemical and Mechanical', 'name' => 'Mechanical Engineering', 'code' => 'ME', 'total_years' => 5],
            ['college' => 'School of Chemical and Mechanical', 'name' => 'Mechatronics Engineering', 'code' => 'MTRX', 'total_years' => 5],

            // School of Civil and Architecture
            ['college' => 'School of Civil and Architecture', 'name' => 'Architecture Engineering', 'code' => 'ARCH', 'total_years' => 5],
            ['college' => 'School of Civil and Architecture', 'name' => 'Civil Engineering', 'code' => 'CE', 'total_years' => 5],
            ['college' => 'School of Civil and Architecture', 'name' => 'Construction Technology Management', 'code' => 'COTM', 'total_years' => 5],
            ['college' => 'School of Civil and Architecture', 'name' => 'Hydraulic and Water Resources Engineering', 'code' => 'HWRE', 'total_years' => 5],
            ['college' => 'School of Civil and Architecture', 'name' => 'Water Resource & Irrigation Engineering', 'code' => 'WRIE', 'total_years' => 5],

            // School of Textile
            ['college' => 'School of Textile', 'name' => 'Fashion Design', 'code' => 'FASH', 'total_years' => 4],
            ['college' => 'School of Textile', 'name' => 'Garment Engineering', 'code' => 'GRMT', 'total_years' => 5],
            ['college' => 'School of Textile', 'name' => 'Leather Engineering', 'code' => 'LEAT', 'total_years' => 5],
            ['college' => 'School of Textile', 'name' => 'Textile Engineering', 'code' => 'TEXT', 'total_years' => 5],

            // Other Programs in Kombolcha
            ['college' => 'Other Programs in Kombolcha', 'name' => 'AppFactory Academy', 'code' => 'APPF', 'total_years' => 1],
            ['college' => 'Other Programs in Kombolcha', 'name' => 'Techno Association', 'code' => 'TECH', 'total_years' => 2],
            ['college' => 'Other Programs in Kombolcha', 'name' => 'Turkish Language', 'code' => 'TURK', 'total_years' => 4],
        ];

        foreach ($kombolchaDepartments as $dept) {
            Department::updateOrCreate(
                [
                    'university_id' => $kombolchaCampus->id,
                    'code' => $dept['code'],
                ],
                [
                    'college' => $dept['college'],
                    'name' => $dept['name'],
                    'total_years' => $dept['total_years'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info(sprintf(
            'Successfully seeded campuses and %d departments (%d in Dessie, %d in Kombolcha).',
            count($dessieDepartments) + count($kombolchaDepartments),
            count($dessieDepartments),
            count($kombolchaDepartments)
        ));
    }
}
