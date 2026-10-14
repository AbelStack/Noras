<?php

namespace Database\Seeders;

use App\Models\University;
use App\Models\Department;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin uploader
        $admin = User::firstOrCreate(
            ['email' => 'admin@noras.test'],
            [
                'name' => 'Admin User',
                'password' => 'password123',
                'student_id' => 'ADMIN001',
                'role' => 'admin',
                'account_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Create Wollo University
        $university = University::create([
            'name' => 'Wollo University',
            'code' => 'WU',
            'is_active' => true,
        ]);

        // Create Departments
        $departments = [
            ['name' => 'Computer Science', 'code' => 'CS'],
            ['name' => 'Software Engineering', 'code' => 'SE'],
            ['name' => 'Information Systems', 'code' => 'IS'],
        ];

        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[] = Department::create([
                'university_id' => $university->id,
                'name' => $dept['name'],
                'code' => $dept['code'],
                'is_active' => true,
            ]);
        }

        // Create Courses
        $courses = [
            ['code' => 'CS101', 'title' => 'Introduction to Programming', 'description' => 'Learn programming basics with Python', 'credit_hours' => 3],
            ['code' => 'CS201', 'title' => 'Data Structures and Algorithms', 'description' => 'Core data structures and algorithm design', 'credit_hours' => 4],
            ['code' => 'CS301', 'title' => 'Database Systems', 'description' => 'Relational database design and SQL', 'credit_hours' => 3],
            ['code' => 'SE101', 'title' => 'Software Engineering Principles', 'description' => 'Software development lifecycle and methodologies', 'credit_hours' => 3],
            ['code' => 'SE201', 'title' => 'Web Development', 'description' => 'Modern web technologies and frameworks', 'credit_hours' => 4],
        ];

        $courseModels = [];
        foreach ($courses as $course) {
            $courseModels[] = Course::create($course + ['is_active' => true]);
        }

        // Create Course Offerings for current academic year
        $currentYear = 2026;
        foreach ($deptModels as $dept) {
            // Year 1, Semester 1
            $offering1 = CourseOffering::create([
                'course_id' => $courseModels[0]->id, // CS101
                'department_id' => $dept->id,
                'academic_year' => $currentYear,
                'study_year' => 1,
                'semester' => 1,
                'is_active' => true,
            ]);

            // Year 1, Semester 2
            $offering2 = CourseOffering::create([
                'course_id' => $courseModels[3]->id, // SE101
                'department_id' => $dept->id,
                'academic_year' => $currentYear,
                'study_year' => 1,
                'semester' => 2,
                'is_active' => true,
            ]);

            // Year 2, Semester 1
            $offering3 = CourseOffering::create([
                'course_id' => $courseModels[1]->id, // CS201
                'department_id' => $dept->id,
                'academic_year' => $currentYear,
                'study_year' => 2,
                'semester' => 1,
                'is_active' => true,
            ]);

            // Create sample materials for first offering
            CourseMaterial::create([
                'course_offering_id' => $offering1->id,
                'uploaded_by' => $admin->id,
                'title' => 'Course Syllabus',
                'description' => 'Course outline, objectives, and grading policy',
                'original_filename' => 'syllabus.pdf',
                'stored_filename' => 'syllabus_' . uniqid() . '.pdf',
                'file_path' => 'materials/syllabus_' . uniqid() . '.pdf',
                'file_size' => 256000,
                'mime_type' => 'application/pdf',
                'status' => 'published',
                'version' => 1,
            ]);

            CourseMaterial::create([
                'course_offering_id' => $offering1->id,
                'uploaded_by' => $admin->id,
                'title' => 'Lecture Notes - Week 1',
                'description' => 'Introduction to programming concepts',
                'original_filename' => 'week1_notes.pdf',
                'stored_filename' => 'week1_' . uniqid() . '.pdf',
                'file_path' => 'materials/week1_' . uniqid() . '.pdf',
                'file_size' => 512000,
                'mime_type' => 'application/pdf',
                'status' => 'published',
                'version' => 1,
            ]);

            CourseMaterial::create([
                'course_offering_id' => $offering2->id,
                'uploaded_by' => $admin->id,
                'title' => 'Software Engineering Textbook',
                'description' => 'Complete textbook for the course',
                'original_filename' => 'se_textbook.pdf',
                'stored_filename' => 'textbook_' . uniqid() . '.pdf',
                'file_path' => 'materials/textbook_' . uniqid() . '.pdf',
                'file_size' => 2048000,
                'mime_type' => 'application/pdf',
                'status' => 'published',
                'version' => 1,
            ]);
        }

        $this->command->info('Catalog seeded successfully!');
        $this->command->info('Universities: ' . University::count());
        $this->command->info('Departments: ' . Department::count());
        $this->command->info('Courses: ' . Course::count());
        $this->command->info('Course Offerings: ' . CourseOffering::count());
        $this->command->info('Materials: ' . CourseMaterial::count());
    }
}
