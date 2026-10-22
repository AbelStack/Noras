<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Bookmark;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseOffering;
use App\Models\Department;
use App\Models\GeneratedNote;
use App\Models\GeneratedQuiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\StudyTask;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ComprehensiveDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Comprehensive Demo Seeding...');

        // 1. Ensure Campuses & Departments exist
        $this->call(CampusAndDepartmentSeeder::class);

        $dessieCampus = University::where('code', 'DESSIE')->first();
        $kombolchaCampus = University::where('code', 'KOCHA')->first();
        $titaCampus = University::where('code', 'TITA')->first();

        $seDept = Department::where('university_id', $kombolchaCampus->id)->where('code', 'SE')->first();
        $csDept = Department::where('university_id', $kombolchaCampus->id)->where('code', 'CS')->first();
        $itDept = Department::where('university_id', $kombolchaCampus->id)->where('code', 'IT')->first();
        $eceDept = Department::where('university_id', $kombolchaCampus->id)->where('code', 'ECE')->first();
        $meDept = Department::where('university_id', $kombolchaCampus->id)->where('code', 'ME')->first();
        $acfnDept = Department::where('university_id', $dessieCampus->id)->where('code', 'ACFN')->first();
        $mgmtDept = Department::where('university_id', $dessieCampus->id)->where('code', 'MGMT')->first();
        $nursingDept = Department::where('university_id', $titaCampus->id)->where('name', 'like', '%Nursing%')->first()
            ?? Department::where('university_id', $titaCampus->id)->first();

        // 2. Seed Users
        // Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@noras.test'],
            [
                'name' => 'Admin User',
                'password' => 'admin123',
                'student_id' => 'ADMIN001',
                'role' => 'admin',
                'account_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Student Abel Tewodros (Must preserve password @bel1996!)
        $studentAbel = User::updateOrCreate(
            ['email' => 'abeltewodros83@gmail.com'],
            [
                'name' => 'Abel Tewodros',
                'password' => '@bel1996',
                'student_id' => 'WU123456',
                'role' => 'student',
                'account_status' => 'approved',
                'approved_at' => now(),
                'university_id' => $kombolchaCampus->id,
                'department_id' => $seDept->id,
            ]
        );

        // Additional demo students
        $studentSara = User::updateOrCreate(
            ['email' => 'sara.k@noras.test'],
            [
                'name' => 'Sara Kebede',
                'password' => 'password123',
                'student_id' => 'WU123457',
                'role' => 'student',
                'account_status' => 'approved',
                'approved_at' => now(),
                'university_id' => $kombolchaCampus->id,
                'department_id' => $csDept->id,
            ]
        );

        $studentDawit = User::updateOrCreate(
            ['email' => 'dawit.m@noras.test'],
            [
                'name' => 'Dawit Mengistu',
                'password' => 'password123',
                'student_id' => 'WU123458',
                'role' => 'student',
                'account_status' => 'approved',
                'approved_at' => now(),
                'university_id' => $dessieCampus->id,
                'department_id' => $acfnDept->id,
            ]
        );

        $studentHelen = User::updateOrCreate(
            ['email' => 'helen.t@noras.test'],
            [
                'name' => 'Helen Tadesse',
                'password' => 'password123',
                'student_id' => 'WU123459',
                'role' => 'student',
                'account_status' => 'approved',
                'approved_at' => now(),
                'university_id' => $titaCampus->id,
                'department_id' => $nursingDept->id,
            ]
        );

        // Pending student for admin review table
        $studentYared = User::updateOrCreate(
            ['email' => 'yared.a@noras.test'],
            [
                'name' => 'Yared Alemu',
                'password' => 'password123',
                'student_id' => 'WU123460',
                'role' => 'student',
                'account_status' => 'pending',
                'university_id' => $kombolchaCampus->id,
                'department_id' => $eceDept->id,
            ]
        );

        $this->command->info('Users seeded: ' . User::count());

        // 3. Seed Courses
        $coursesData = [
            // Core Computing & Software Engineering
            ['code' => 'CS101', 'title' => 'Introduction to Programming & Problem Solving', 'description' => 'Fundamental programming concepts using Python, algorithmic thinking, control structures, functions, and file I/O.', 'credit_hours' => 3],
            ['code' => 'CS201', 'title' => 'Data Structures and Algorithms', 'description' => 'Analysis of algorithms, stacks, queues, linked lists, trees, graphs, sorting, searching, and complexity theory.', 'credit_hours' => 4],
            ['code' => 'CS301', 'title' => 'Database Management Systems', 'description' => 'Relational data model, SQL, normalization (1NF-BCNF), transaction management, indexing, and NoSQL databases.', 'credit_hours' => 3],
            ['code' => 'CS302', 'title' => 'Operating Systems & System Programming', 'description' => 'Processes, concurrency, threads, synchronization, memory management, virtual memory, and file systems.', 'credit_hours' => 3],
            ['code' => 'CS401', 'title' => 'Artificial Intelligence & Machine Learning', 'description' => 'Supervised and unsupervised learning, neural networks, heuristic search, natural language processing, and ethics in AI.', 'credit_hours' => 4],
            ['code' => 'SE101', 'title' => 'Software Engineering Principles', 'description' => 'Software lifecycle models (Waterfall, Agile, Scrum), requirements engineering, UML modeling, and version control.', 'credit_hours' => 3],
            ['code' => 'SE201', 'title' => 'Object-Oriented Design & Patterns', 'description' => 'OOP principles (encapsulation, inheritance, polymorphism), SOLID design principles, and Gang of Four design patterns.', 'credit_hours' => 3],
            ['code' => 'SE301', 'title' => 'Software Architecture & Quality', 'description' => 'Architectural patterns (Layered, Microservices, Event-Driven), software metrics, quality attributes, and refactoring.', 'credit_hours' => 3],
            ['code' => 'SE302', 'title' => 'Modern Web Application Technologies', 'description' => 'Client-server architecture, RESTful API design, modern front-end frameworks (Vue.js/React), and back-end integration.', 'credit_hours' => 4],
            ['code' => 'SE401', 'title' => 'Mobile Application Development', 'description' => 'Native and cross-platform mobile development, UI design guidelines, offline caching, and device hardware integration.', 'credit_hours' => 3],
            ['code' => 'SE402', 'title' => 'Software Testing & Quality Assurance', 'description' => 'Unit testing, integration testing, automated end-to-end testing, test-driven development (TDD), and CI/CD pipelines.', 'credit_hours' => 3],
            ['code' => 'SE501', 'title' => 'Distributed Systems & Cloud Computing', 'description' => 'Distributed consensus, CAP theorem, microservice orchestration, Docker containerization, Kubernetes, and cloud platforms.', 'credit_hours' => 4],

            // Networking & IT
            ['code' => 'IT101', 'title' => 'Fundamentals of Information Technology', 'description' => 'Hardware components, operating system basics, computer networks overview, and information security awareness.', 'credit_hours' => 3],
            ['code' => 'IT202', 'title' => 'Computer Networks & Internet Protocols', 'description' => 'OSI and TCP/IP stack, IP addressing, subnetting, routing protocols (OSPF, BGP), DNS, HTTP/HTTPS, and network security.', 'credit_hours' => 4],
            ['code' => 'IT305', 'title' => 'Cybersecurity & Cryptography', 'description' => 'Symmetric and asymmetric encryption, digital signatures, hashing algorithms, network defense, penetration testing, and ethical hacking.', 'credit_hours' => 3],

            // Engineering Disciplines
            ['code' => 'ECE201', 'title' => 'Electrical Circuit Analysis & Electronics', 'description' => 'Kirchhoff’s laws, AC/DC circuit analysis, operational amplifiers, diodes, transistors, and circuit simulation.', 'credit_hours' => 4],
            ['code' => 'ECE302', 'title' => 'Embedded Systems & Microcontrollers', 'description' => 'Microcontroller architecture (ARM/AVR), GPIO, interrupts, timers, ADC, serial communication (UART, I2C, SPI), and RTOS.', 'credit_hours' => 4],
            ['code' => 'ME101', 'title' => 'Engineering Mechanics & Statics', 'description' => 'Force systems, equilibrium of rigid bodies, distributed forces, centroids, trusses, frames, and friction.', 'credit_hours' => 3],
            ['code' => 'ME202', 'title' => 'Thermodynamics & Heat Transfer', 'description' => 'First and second laws of thermodynamics, closed and open systems, ideal gas laws, conduction, convection, and radiation.', 'credit_hours' => 4],

            // Common Sciences & Mathematics
            ['code' => 'MATH101', 'title' => 'Applied Mathematics I: Calculus', 'description' => 'Functions, limits, continuity, derivatives, differentiation techniques, curve sketching, and applications of extrema.', 'credit_hours' => 4],
            ['code' => 'MATH201', 'title' => 'Discrete Mathematics & Graph Theory', 'description' => 'Propositional and predicate logic, set theory, proof methods, relations, graph theory, trees, and combinatorics.', 'credit_hours' => 3],
            ['code' => 'STAT201', 'title' => 'Probability & Statistics for Engineers', 'description' => 'Descriptive statistics, probability distributions, random variables, hypothesis testing, confidence intervals, and regression.', 'credit_hours' => 3],

            // Business & Health
            ['code' => 'MGMT101', 'title' => 'Entrepreneurship & Business Management', 'description' => 'Principles of organizational management, startup creation, business plan development, financial planning, and leadership.', 'credit_hours' => 3],
            ['code' => 'ACFN201', 'title' => 'Principles of Accounting & Finance', 'description' => 'Double-entry bookkeeping, financial statements, balance sheets, income statements, cash flow, and cost accounting.', 'credit_hours' => 3],
            ['code' => 'NURS201', 'title' => 'Human Anatomy & Physiology I', 'description' => 'Cell biology, tissues, skeletal, muscular, nervous, endocrine, and cardiovascular organ systems in humans.', 'credit_hours' => 4],
        ];

        $courses = [];
        foreach ($coursesData as $c) {
            $courses[$c['code']] = Course::updateOrCreate(
                ['code' => $c['code']],
                [
                    'title' => $c['title'],
                    'description' => $c['description'],
                    'credit_hours' => $c['credit_hours'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Courses seeded: ' . count($courses));

        // 4. Seed Course Offerings
        $currentYear = 2026;

        // Software Engineering Offerings (Full Years 1 through 5, Semesters 1 and 2)
        $seOfferingsMap = [
            // Year 1
            ['year' => 1, 'sem' => 1, 'courses' => ['MATH101', 'CS101', 'IT101']],
            ['year' => 1, 'sem' => 2, 'courses' => ['MATH201', 'SE101']],
            // Year 2
            ['year' => 2, 'sem' => 1, 'courses' => ['CS201', 'SE201', 'ECE201']],
            ['year' => 2, 'sem' => 2, 'courses' => ['IT202', 'STAT201']],
            // Year 3
            ['year' => 3, 'sem' => 1, 'courses' => ['CS301', 'SE301']],
            ['year' => 3, 'sem' => 2, 'courses' => ['CS302', 'SE302']],
            // Year 4
            ['year' => 4, 'sem' => 1, 'courses' => ['SE401', 'IT305']],
            ['year' => 4, 'sem' => 2, 'courses' => ['SE402', 'CS401']],
            // Year 5
            ['year' => 5, 'sem' => 1, 'courses' => ['SE501', 'ECE302']],
            ['year' => 5, 'sem' => 2, 'courses' => ['MGMT101']],
        ];

        $createdOfferings = [];
        foreach ($seOfferingsMap as $group) {
            foreach ($group['courses'] as $code) {
                if (isset($courses[$code])) {
                    $offering = CourseOffering::updateOrCreate(
                        [
                            'course_id' => $courses[$code]->id,
                            'department_id' => $seDept->id,
                            'academic_year' => $currentYear,
                            'study_year' => $group['year'],
                            'semester' => $group['sem'],
                        ],
                        [
                            'is_active' => true,
                        ]
                    );
                    $createdOfferings[$code . '_SE_Y' . $group['year'] . 'S' . $group['sem']] = $offering;
                }
            }
        }

        // Computer Science Offerings (Years 1-4)
        $csOfferingsMap = [
            ['year' => 1, 'sem' => 1, 'courses' => ['MATH101', 'CS101']],
            ['year' => 1, 'sem' => 2, 'courses' => ['MATH201', 'SE101']],
            ['year' => 2, 'sem' => 1, 'courses' => ['CS201', 'STAT201']],
            ['year' => 2, 'sem' => 2, 'courses' => ['IT202', 'CS301']],
            ['year' => 3, 'sem' => 1, 'courses' => ['CS302', 'SE302']],
            ['year' => 3, 'sem' => 2, 'courses' => ['IT305', 'CS401']],
            ['year' => 4, 'sem' => 1, 'courses' => ['SE501', 'MGMT101']],
            ['year' => 4, 'sem' => 2, 'courses' => ['SE402']],
        ];

        foreach ($csOfferingsMap as $group) {
            foreach ($group['courses'] as $code) {
                if (isset($courses[$code]) && $csDept) {
                    CourseOffering::updateOrCreate(
                        [
                            'course_id' => $courses[$code]->id,
                            'department_id' => $csDept->id,
                            'academic_year' => $currentYear,
                            'study_year' => $group['year'],
                            'semester' => $group['sem'],
                        ],
                        [
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // Accounting Offerings
        if ($acfnDept) {
            CourseOffering::updateOrCreate(
                [
                    'course_id' => $courses['ACFN201']->id,
                    'department_id' => $acfnDept->id,
                    'academic_year' => $currentYear,
                    'study_year' => 1,
                    'semester' => 1,
                ],
                ['is_active' => true]
            );
            CourseOffering::updateOrCreate(
                [
                    'course_id' => $courses['MGMT101']->id,
                    'department_id' => $acfnDept->id,
                    'academic_year' => $currentYear,
                    'study_year' => 1,
                    'semester' => 2,
                ],
                ['is_active' => true]
            );
        }

        // Nursing Offerings
        if ($nursingDept) {
            CourseOffering::updateOrCreate(
                [
                    'course_id' => $courses['NURS201']->id,
                    'department_id' => $nursingDept->id,
                    'academic_year' => $currentYear,
                    'study_year' => 1,
                    'semester' => 1,
                ],
                ['is_active' => true]
            );
        }

        $this->command->info('Course Offerings seeded: ' . CourseOffering::count());

        // 5. Seed Course Materials (with valid dummy local storage files)
        $dummyPdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 53 >>\nstream\nBT /F1 24 Tf 100 700 Td (Noras Learning Document) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \n0000000280 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n384\n%%EOF";

        $materialsList = [
            [
                'offering_key' => 'CS201_SE_Y2S1',
                'title' => 'Lecture 01 - Asymptotic Analysis & Arrays',
                'description' => 'Comprehensive introduction to Big-O notation, time and space complexity, array operations, and dynamic array resizing strategies.',
                'filename' => 'CS201_Lecture01_Arrays_BigO.pdf',
                'mime' => 'application/pdf',
                'size' => 1845200,
            ],
            [
                'offering_key' => 'CS201_SE_Y2S1',
                'title' => 'Lecture 02 - Linked Lists & Stack Applications',
                'description' => 'Single vs doubly linked lists, pointer manipulation, LIFO principle, stack evaluation of postfix expressions, and recursion simulation.',
                'filename' => 'CS201_Lecture02_LinkedLists_Stacks.pdf',
                'mime' => 'application/pdf',
                'size' => 2410000,
            ],
            [
                'offering_key' => 'CS301_SE_Y3S1',
                'title' => 'Lecture 01 - Relational Schema Design & Normalization',
                'description' => 'Entity-Relationship modeling, relational algebra, functional dependencies, 1NF, 2NF, 3NF, and Boyce-Codd Normal Form with practical business examples.',
                'filename' => 'CS301_Lecture01_Relational_Model_Normalization.pdf',
                'mime' => 'application/pdf',
                'size' => 3120500,
            ],
            [
                'offering_key' => 'CS301_SE_Y3S1',
                'title' => 'Lecture 02 - Advanced SQL & Indexing Strategies',
                'description' => 'Complex subqueries, window functions, B-Tree index mechanics, query execution plans, and transaction isolation levels (ACID).',
                'filename' => 'CS301_Lecture02_SQL_Indexing_Transactions.pdf',
                'mime' => 'application/pdf',
                'size' => 2840000,
            ],
            [
                'offering_key' => 'SE302_SE_Y3S2',
                'title' => 'Lecture 01 - Modern REST APIs & Client Architecture',
                'description' => 'REST principles, idempotent HTTP verbs, status codes, JWT authentication, Vue.js reactive component trees, and state management.',
                'filename' => 'SE302_Lecture01_REST_APIs_Vue.pdf',
                'mime' => 'application/pdf',
                'size' => 2150000,
            ],
            [
                'offering_key' => 'SE201_SE_Y2S1',
                'title' => 'Lecture 01 - SOLID Design Principles & Clean Code',
                'description' => 'Single Responsibility, Open-Closed, Liskov Substitution, Interface Segregation, and Dependency Inversion explained with refactoring examples.',
                'filename' => 'SE201_Lecture01_SOLID_Principles.pdf',
                'mime' => 'application/pdf',
                'size' => 1980000,
            ],
            [
                'offering_key' => 'CS101_SE_Y1S1',
                'title' => 'Lecture 01 - Python Programming Fundamentals',
                'description' => 'Variables, primitive types, control flow, functions, list comprehensions, dictionary operations, and clean programming standards.',
                'filename' => 'CS101_Lecture01_Python_Basics.pdf',
                'mime' => 'application/pdf',
                'size' => 1650000,
            ],
            [
                'offering_key' => 'SE101_SE_Y1S2',
                'title' => 'Lecture 01 - Software Development Lifecycle Models',
                'description' => 'Comparative analysis of Waterfall, Spiral, and Agile frameworks. Scrum ceremonies, sprint planning, and backlog refinement.',
                'filename' => 'SE101_Lecture01_SDLC_Agile.pdf',
                'mime' => 'application/pdf',
                'size' => 2340000,
            ],
            [
                'offering_key' => 'IT202_SE_Y2S2',
                'title' => 'Lecture 01 - TCP/IP Protocol Suite & Network Architecture',
                'description' => 'Detailed examination of the OSI 7-layer model vs TCP/IP 4-layer stack, packet encapsulation, IP addressing, and routing fundamentals.',
                'filename' => 'IT202_Lecture01_TCPIP_Protocols.pdf',
                'mime' => 'application/pdf',
                'size' => 3200000,
            ],
            [
                'offering_key' => 'MATH101_SE_Y1S1',
                'title' => 'Handout 01 - Differential Calculus & Optimization',
                'description' => 'Rules of differentiation, chain rule, implicit differentiation, related rates, and practical engineering optimization problems.',
                'filename' => 'MATH101_Handout01_Calculus_Optimization.pdf',
                'mime' => 'application/pdf',
                'size' => 1450000,
            ],
        ];

        $createdMaterials = [];

        foreach ($materialsList as $item) {
            $offering = $createdOfferings[$item['offering_key']] ?? null;
            if (!$offering) {
                continue;
            }

            $storagePath = 'materials/demo_' . md5($item['filename']) . '.pdf';
            // Store dummy file on local disk so downloads and views work
            try {
                Storage::disk('local')->put($storagePath, $dummyPdfContent);
            } catch (\Exception $e) {
                // local disk write fallback
            }

            $material = CourseMaterial::updateOrCreate(
                [
                    'course_offering_id' => $offering->id,
                    'original_filename' => $item['filename'],
                ],
                [
                    'uploaded_by' => $admin->id,
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'stored_filename' => basename($storagePath),
                    'file_path' => $storagePath,
                    'file_size' => $item['size'],
                    'mime_type' => $item['mime'],
                    'status' => 'published',
                    'version' => 1,
                ]
            );

            $createdMaterials[$item['filename']] = $material;
        }

        $this->command->info('Course Materials seeded: ' . CourseMaterial::count());

        // 6. Seed High Quality Generated Notes
        $notesData = [
            'CS201_Lecture01_Arrays_BigO.pdf' => [
                'content' => <<<MARKDOWN
# CS201: Asymptotic Analysis & Array Data Structures

## 1. Executive Summary & Learning Objectives
This comprehensive study guide covers the foundational principles of algorithm efficiency analysis and array memory architectures. By mastering these concepts, software engineers can design scalable, resource-efficient programs.

- **Primary Goal:** Master Big-O, Big-Omega (\(\Omega\)), and Big-Theta (\(\Theta\)) asymptotic notation.
- **Data Structure Focus:** Static arrays, contiguous memory allocation, pointer arithmetic, and dynamic resizing algorithms.
- **Complexity Targets:** O(1) random access vs O(N) linear insertions and deletions.

---

## 2. Asymptotic Complexity Framework
Asymptotic analysis evaluates how the execution time and memory footprint of an algorithm scale as the input size \(N \to \infty\).

| Notation | Formal Mathematical Meaning | Intuitive Description |
| :--- | :--- | :--- |
| **\(O(g(n))\)** (Big-O) | \(f(n) \le c \cdot g(n)\) for \(n \ge n_0\) | **Upper Bound** (Worst-case guaranteed performance) |
| **\(\Omega(g(n))\)** (Big-Omega) | \(f(n) \ge c \cdot g(n)\) for \(n \ge n_0\) | **Lower Bound** (Best-case scenario) |
| **\(\Theta(g(n))\)** (Big-Theta) | \(c_1 \cdot g(n) \le f(n) \le c_2 \cdot g(n)\) | **Tight Bound** (Exact asymptotic behavior) |

### Key Complexity Hierarchy (Fastest to Slowest)
1. \(O(1)\) - Constant Time (Array index lookup, Hash table lookup average)
2. \(O(\log N)\) - Logarithmic Time (Binary search on sorted collections)
3. \(O(N)\) - Linear Time (Single pass search or traversal)
4. \(O(N \log N)\) - Linearithmic Time (Merge Sort, Quick Sort average)
5. \(O(N^2)\) - Quadratic Time (Nested loops, Bubble Sort)
6. \(O(2^N)\) - Exponential Time (Recursive subsets)

---

## 3. Arrays in Physical Memory
An array is a collection of elements stored in **contiguous memory locations**.

### The Addressing Formula:
Given base memory address \(B\), element index \(i\), and element size \(S\) bytes:
$$\text{Address}(A[i]) = B + (i \times S)$$

Because this arithmetic calculation requires only one multiplication and one addition, accessing any element by index is strictly **\(O(1)\)**.

```python
# Demonstrating Dynamic Array Geometric Growth
class DynamicArray:
    def __init__(self):
        self._capacity = 4
        self._size = 0
        self._data = [None] * self._capacity

    def append(self, element):
        if self._size == self._capacity:
            self._resize(2 * self._capacity) # Geometric doubling
        self._data[self._size] = element
        self._size += 1

    def _resize(self, new_capacity):
        new_data = [None] * new_capacity
        for i in range(self._size):
            new_data[i] = self._data[i]
        self._data = new_data
        self._capacity = new_capacity
```

### Amortized Analysis of Dynamic Array Resizing
Although resizing from size \(N\) to \(2N\) costs \(O(N)\) copying time, it occurs so infrequently that the **amortized cost per append operation is strictly \(O(1)\)**.

---

## 4. Key Takeaways & Exam Checklist
- Arrays excel when random access by index is frequent.
- Insertions and deletions at the beginning or middle require shifting elements (\(O(N)\)).
- Geometric doubling (multiplying by 2) maintains \(O(1)\) amortized cost, whereas linear increments (+10) degrade to \(O(N^2)\).
MARKDOWN
            ],
            'CS301_Lecture01_Relational_Model_Normalization.pdf' => [
                'content' => <<<MARKDOWN
# CS301: Relational Schema Design & Database Normalization

## 1. Overview & Architectural Importance
Database normalization is the systematic technique of structuring a relational database in accordance with a series of normal forms in order to reduce data redundancy and improve data integrity.

### Primary Objectives:
- Eliminate insertion, update, and deletion anomalies.
- Guarantee functional dependencies are accurately represented.
- Minimize disk space waste and synchronize multi-user updates.

---

## 2. Anomalies in Unnormalized Tables

| Anomaly Type | Concrete Scenario | Consequence |
| :--- | :--- | :--- |
| **Insertion Anomaly** | Cannot add a new department until a student or course is enrolled. | Data cannot be recorded independently. |
| **Deletion Anomaly** | Deleting the last enrolled student accidentally deletes the department profile. | Unintended loss of critical institutional data. |
| **Update Anomaly** | Student changes their major, but only 2 of 5 records are updated. | Inconsistent state and conflicting queries. |

---

## 3. The Progressive Normal Forms

### First Normal Form (1NF)
- Each column must contain only **atomic (indivisible) values**.
- No repeating groups or arrays within a single attribute.
- Each row must be uniquely identifiable via a Primary Key.

### Second Normal Form (2NF)
- Must satisfy 1NF.
- **No partial functional dependencies:** Every non-prime attribute must depend on the *entire* candidate key, not just a subset of a composite key.

### Third Normal Form (3NF)
- Must satisfy 2NF.
- **No transitive dependencies:** Non-prime attributes must depend directly on the primary key, rather than through another non-prime attribute (\(X \to Y \to Z\)).

### Boyce-Codd Normal Form (BCNF)
- Stricter variant of 3NF: For every non-trivial functional dependency \(X \to Y\), **\(X\) must be a superkey**.

```sql
-- Normalized Schema Example
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

---

## 4. Summary & Best Practices
- Normalize to 3NF/BCNF for transactional OLTP systems.
- Consider controlled denormalization only in analytical data warehouses (OLAP) to accelerate complex reporting queries.
MARKDOWN
            ],
            'SE302_Lecture01_REST_APIs_Vue.pdf' => [
                'content' => <<<MARKDOWN
# SE302: Modern Web Application Engineering with REST & Vue.js

## 1. Architecture Overview
Modern web applications separate the concerns of data management and user presentation. The backend provides a stateless JSON API, while the frontend provides a reactive Single Page Application (SPA).

---

## 2. RESTful Design Principles (Representational State Transfer)
- **Stateless Communication:** Each HTTP request contains all information needed to process it; session state lives on the client (via JWT).
- **Uniform Interface:** Standard HTTP verbs map directly to CRUD operations:
  - `GET /api/courses` -> Retrieve resources (Safe & Idempotent)
  - `POST /api/courses` -> Create new resource (Non-idempotent)
  - `PUT /api/courses/{id}` -> Full replacement of resource (Idempotent)
  - `PATCH /api/courses/{id}` -> Partial update (Non-idempotent)
  - `DELETE /api/courses/{id}` -> Remove resource (Idempotent)

---

## 3. Vue 3 Composition API & Reactivity
Vue 3 uses JavaScript `Proxy` objects to intercept property access and automatically trigger reactive DOM re-renders when data changes.

```javascript
import { ref, computed, onMounted } from 'vue'

export default {
  setup() {
    const courses = ref([])
    const searchQuery = ref('')

    const filteredCourses = computed(() => {
      return courses.value.filter(c => 
        c.title.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    })

    const fetchCourses = async () => {
      const res = await fetch('/api/courses')
      courses.value = await res.json()
    }

    onMounted(fetchCourses)

    return { searchQuery, filteredCourses }
  }
}
```

---

## 4. Key Review Questions
1. Why is `GET` safe while `POST` is not? Safe methods do not alter server state.
2. What makes an HTTP method idempotent? Executing it multiple times produces the identical side-effects as a single execution.
MARKDOWN
            ],
            'CS101_Lecture01_Python_Basics.pdf' => [
                'content' => <<<MARKDOWN
# CS101: Introduction to Python Programming & Problem Solving

## 1. Fundamentals of Python
Python is a high-level, dynamically typed, interpreted language renowned for its expressive syntax and readability.

### Core Data Structures
- **Lists:** Ordered, mutable sequence `[1, 2, 3]`
- **Tuples:** Ordered, immutable sequence `(1, 2, 3)`
- **Sets:** Unordered collection of unique items `{1, 2, 3}`
- **Dictionaries:** Key-value mappings `{"id": 1, "name": "Abel"}`

```python
# Clean Pythonic List Comprehension
numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
evens_squared = [x**2 for x in numbers if x % 2 == 0]
print(evens_squared)  # Output: [4, 16, 36, 64, 100]
```

## 2. Functions & Scope
Functions in Python are first-class citizens. They can be passed as arguments, returned from other functions, and stored in variables.
MARKDOWN
            ],
        ];

        $createdNotes = [];
        foreach ($notesData as $filename => $data) {
            $material = $createdMaterials[$filename] ?? null;
            if (!$material) {
                continue;
            }

            $note = GeneratedNote::updateOrCreate(
                [
                    'course_material_id' => $material->id,
                    'material_version' => $material->version,
                ],
                [
                    'generated_by' => $admin->id,
                    'content' => $data['content'],
                    'ai_provider' => 'groq',
                    'ai_model' => 'llama-3.3-70b-versatile',
                    'source_char_count' => strlen($data['content']) * 2,
                    'notes_char_count' => strlen($data['content']),
                    'generation_time_ms' => rand(1800, 3200),
                ]
            );

            $createdNotes[$filename] = $note;
        }

        $this->command->info('Generated Notes seeded: ' . GeneratedNote::count());

        // 7. Seed Quizzes, Questions, and Options
        $quizzesData = [
            'CS201_Lecture01_Arrays_BigO.pdf' => [
                'questions' => [
                    [
                        'text' => 'What is the time complexity to access an element in a static array given its index?',
                        'explanation' => 'Array index lookup is O(1) constant time because physical memory is contiguous, and the address is calculated in a single arithmetic step: Base + (Index * Element_Size).',
                        'options' => [
                            ['text' => 'O(1) - Constant Time', 'correct' => true],
                            ['text' => 'O(log N) - Logarithmic Time', 'correct' => false],
                            ['text' => 'O(N) - Linear Time', 'correct' => false],
                            ['text' => 'O(N^2) - Quadratic Time', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Which asymptotic notation represents the tight mathematical bound of an algorithm\'s growth rate?',
                        'explanation' => 'Big-Theta (Θ) denotes a tight bound, meaning the function is bounded both from above and below by constant multiples of g(n).',
                        'options' => [
                            ['text' => 'Big-O (O)', 'correct' => false],
                            ['text' => 'Big-Omega (Ω)', 'correct' => false],
                            ['text' => 'Big-Theta (Θ)', 'correct' => true],
                            ['text' => 'Little-o (o)', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'When a dynamic array doubles its capacity upon becoming full, what is the amortized cost per append operation?',
                        'explanation' => 'Geometric doubling ensures that resizing costs are spread evenly across all appends, yielding an amortized time of O(1).',
                        'options' => [
                            ['text' => 'O(1) amortized', 'correct' => true],
                            ['text' => 'O(N) amortized', 'correct' => false],
                            ['text' => 'O(log N) amortized', 'correct' => false],
                            ['text' => 'O(N^2) amortized', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'What is the worst-case time complexity of inserting an item at index 0 of an array of size N?',
                        'explanation' => 'Inserting at the beginning requires shifting all N existing elements one position to the right, which takes O(N) operations.',
                        'options' => [
                            ['text' => 'O(1)', 'correct' => false],
                            ['text' => 'O(log N)', 'correct' => false],
                            ['text' => 'O(N)', 'correct' => true],
                            ['text' => 'O(N log N)', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Which of the following complexity classes grows fastest as N becomes very large?',
                        'explanation' => 'Exponential complexity O(2^N) grows vastly faster than polynomial or linearithmic complexities.',
                        'options' => [
                            ['text' => 'O(N log N)', 'correct' => false],
                            ['text' => 'O(N^3)', 'correct' => false],
                            ['text' => 'O(2^N)', 'correct' => true],
                            ['text' => 'O(N^2)', 'correct' => false],
                        ]
                    ],
                ]
            ],
            'CS301_Lecture01_Relational_Model_Normalization.pdf' => [
                'questions' => [
                    [
                        'text' => 'What requirement must a relational table satisfy to be in First Normal Form (1NF)?',
                        'explanation' => '1NF mandates that all column values must be atomic (indivisible) and there cannot be repeating groups or arrays.',
                        'options' => [
                            ['text' => 'Every attribute value must be atomic and unique rows identifiable by a primary key', 'correct' => true],
                            ['text' => 'All foreign keys must reference existing rows in related tables', 'correct' => false],
                            ['text' => 'No non-prime attribute may depend on another non-prime attribute', 'correct' => false],
                            ['text' => 'All tables must contain at least one index', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'A relation is in Second Normal Form (2NF) if it is in 1NF and contains no:',
                        'explanation' => '2NF eliminates partial functional dependencies, meaning no non-prime attribute can depend on only part of a composite candidate key.',
                        'options' => [
                            ['text' => 'Transitive dependencies', 'correct' => false],
                            ['text' => 'Partial functional dependencies on a composite key', 'correct' => true],
                            ['text' => 'Foreign key constraints', 'correct' => false],
                            ['text' => 'Null values in optional fields', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'Which normal form specifically addresses and eliminates transitive dependencies?',
                        'explanation' => 'Third Normal Form (3NF) requires that non-key columns depend only on the primary key, eliminating transitive dependencies of the form X -> Y -> Z.',
                        'options' => [
                            ['text' => 'First Normal Form (1NF)', 'correct' => false],
                            ['text' => 'Second Normal Form (2NF)', 'correct' => false],
                            ['text' => 'Third Normal Form (3NF)', 'correct' => true],
                            ['text' => 'Domain-Key Normal Form (DKNF)', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'What is an update anomaly?',
                        'explanation' => 'An update anomaly occurs when data redundancy causes inconsistent updates across multiple duplicate records of the same real-world entity.',
                        'options' => [
                            ['text' => 'A server timeout when running a heavy migration query', 'correct' => false],
                            ['text' => 'Inconsistency resulting from updating data in some rows while redundant duplicate rows remain unaltered', 'correct' => true],
                            ['text' => 'Attempting to insert a duplicate primary key', 'correct' => false],
                            ['text' => 'Lock contention in multi-threaded database transactions', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'In Boyce-Codd Normal Form (BCNF), for every functional dependency X -> Y, what must X be?',
                        'explanation' => 'BCNF is a strict version of 3NF where the determinant X must strictly be a Superkey for every functional dependency.',
                        'options' => [
                            ['text' => 'A foreign key', 'correct' => false],
                            ['text' => 'A composite key', 'correct' => false],
                            ['text' => 'A superkey', 'correct' => true],
                            ['text' => 'An indexed attribute', 'correct' => false],
                        ]
                    ],
                ]
            ],
            'SE302_Lecture01_REST_APIs_Vue.pdf' => [
                'questions' => [
                    [
                        'text' => 'Which HTTP method is considered both Safe and Idempotent according to the HTTP/1.1 specification?',
                        'explanation' => 'GET only retrieves data without modifying server state (Safe) and can be repeated multiple times with identical effects (Idempotent).',
                        'options' => [
                            ['text' => 'GET', 'correct' => true],
                            ['text' => 'POST', 'correct' => false],
                            ['text' => 'PATCH', 'correct' => false],
                            ['text' => 'CONNECT', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'In Vue 3, which reactivity API function is used to create a reactive reference for primitive values (such as numbers or strings)?',
                        'explanation' => 'ref() wraps primitive values into a reactive object accessible via the .value property.',
                        'options' => [
                            ['text' => 'reactive()', 'correct' => false],
                            ['text' => 'ref()', 'correct' => true],
                            ['text' => 'watchEffect()', 'correct' => false],
                            ['text' => 'computed()', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'What HTTP response status code should be returned after successfully creating a new resource via a POST request?',
                        'explanation' => '201 Created indicates that the request has succeeded and led to the creation of a new resource.',
                        'options' => [
                            ['text' => '200 OK', 'correct' => false],
                            ['text' => '201 Created', 'correct' => true],
                            ['text' => '204 No Content', 'correct' => false],
                            ['text' => '301 Moved Permanently', 'correct' => false],
                        ]
                    ],
                    [
                        'text' => 'What mechanism in Vue 3 enables caching derived values that only recalculate when their dependencies change?',
                        'explanation' => 'computed() properties are cached based on their reactive dependencies and only re-evaluate when a dependency changes.',
                        'options' => [
                            ['text' => 'computed()', 'correct' => true],
                            ['text' => 'methods', 'correct' => false],
                            ['text' => 'watch()', 'correct' => false],
                            ['text' => 'provide/inject', 'correct' => false],
                        ]
                    ],
                ]
            ],
        ];

        $createdQuizzes = [];

        foreach ($quizzesData as $filename => $qData) {
            $material = $createdMaterials[$filename] ?? null;
            if (!$material) {
                continue;
            }

            $quiz = GeneratedQuiz::updateOrCreate(
                [
                    'course_material_id' => $material->id,
                    'material_version' => $material->version,
                ],
                [
                    'generated_by' => $admin->id,
                    'ai_provider' => 'groq',
                    'ai_model' => 'llama-3.3-70b-versatile',
                    'question_count' => count($qData['questions']),
                    'source_char_count' => 12000,
                    'generation_time_ms' => rand(1500, 2800),
                ]
            );

            // Clean up existing questions to prevent duplicates on re-run
            $quiz->questions()->delete();

            foreach ($qData['questions'] as $qIndex => $questionInfo) {
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_order' => $qIndex + 1,
                    'question_text' => $questionInfo['text'],
                    'explanation' => $questionInfo['explanation'],
                ]);

                foreach ($questionInfo['options'] as $oIndex => $optInfo) {
                    QuizOption::create([
                        'question_id' => $question->id,
                        'option_order' => $oIndex + 1,
                        'option_text' => $optInfo['text'],
                        'is_correct' => $optInfo['correct'],
                    ]);
                }
            }

            $createdQuizzes[$filename] = $quiz;
        }

        $this->command->info('Generated Quizzes seeded: ' . GeneratedQuiz::count());
        $this->command->info('Quiz Questions seeded: ' . QuizQuestion::count());

        // 8. Seed Quiz Attempts for Student Abel (to populate Learning Progress)
        if (isset($createdQuizzes['CS201_Lecture01_Arrays_BigO.pdf'])) {
            $q1 = $createdQuizzes['CS201_Lecture01_Arrays_BigO.pdf'];
            QuizAttempt::updateOrCreate(
                [
                    'user_id' => $studentAbel->id,
                    'quiz_id' => $q1->id,
                ],
                [
                    'score' => 5,
                    'total_questions' => 5,
                    'percentage' => 100.00,
                    'answers' => [1 => 1, 2 => 3, 3 => 1, 4 => 3, 5 => 3],
                    'created_at' => now()->subDays(3),
                ]
            );
        }

        if (isset($createdQuizzes['CS301_Lecture01_Relational_Model_Normalization.pdf'])) {
            $q2 = $createdQuizzes['CS301_Lecture01_Relational_Model_Normalization.pdf'];
            QuizAttempt::updateOrCreate(
                [
                    'user_id' => $studentAbel->id,
                    'quiz_id' => $q2->id,
                ],
                [
                    'score' => 4,
                    'total_questions' => 5,
                    'percentage' => 80.00,
                    'answers' => [1 => 1, 2 => 2, 3 => 3, 4 => 2, 5 => 2],
                    'created_at' => now()->subDays(1),
                ]
            );
        }

        $this->command->info('Quiz Attempts seeded: ' . QuizAttempt::count());

        // 9. Seed Bookmarks for Student Abel
        if (isset($createdMaterials['CS201_Lecture01_Arrays_BigO.pdf'])) {
            Bookmark::updateOrCreate([
                'user_id' => $studentAbel->id,
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $createdMaterials['CS201_Lecture01_Arrays_BigO.pdf']->id,
            ]);
        }
        if (isset($createdMaterials['CS301_Lecture01_Relational_Model_Normalization.pdf'])) {
            Bookmark::updateOrCreate([
                'user_id' => $studentAbel->id,
                'bookmarkable_type' => CourseMaterial::class,
                'bookmarkable_id' => $createdMaterials['CS301_Lecture01_Relational_Model_Normalization.pdf']->id,
            ]);
        }
        if (isset($createdNotes['CS201_Lecture01_Arrays_BigO.pdf'])) {
            Bookmark::updateOrCreate([
                'user_id' => $studentAbel->id,
                'bookmarkable_type' => GeneratedNote::class,
                'bookmarkable_id' => $createdNotes['CS201_Lecture01_Arrays_BigO.pdf']->id,
            ]);
        }
        if (isset($createdQuizzes['CS201_Lecture01_Arrays_BigO.pdf'])) {
            Bookmark::updateOrCreate([
                'user_id' => $studentAbel->id,
                'bookmarkable_type' => GeneratedQuiz::class,
                'bookmarkable_id' => $createdQuizzes['CS201_Lecture01_Arrays_BigO.pdf']->id,
            ]);
        }

        $this->command->info('Bookmarks seeded: ' . Bookmark::count());

        // 10. Seed Study Tasks for Student Abel
        $cs201Mat = $createdMaterials['CS201_Lecture01_Arrays_BigO.pdf'] ?? null;
        $cs301Mat = $createdMaterials['CS301_Lecture01_Relational_Model_Normalization.pdf'] ?? null;
        $se302Mat = $createdMaterials['SE302_Lecture01_REST_APIs_Vue.pdf'] ?? null;

        $tasks = [
            [
                'title' => 'Master Big-O Notation & Dynamic Array Resizing',
                'description' => 'Review Lecture 1 slide deck and complete implementation of custom dynamic array in Python.',
                'due_date' => now()->addDays(2),
                'completed' => false,
                'course_material_id' => $cs201Mat?->id,
            ],
            [
                'title' => 'Complete Normalization 3NF Practice Exercise',
                'description' => 'Decompose unnormalized hospital registry table into Boyce-Codd Normal Form schema.',
                'due_date' => now()->addDays(5),
                'completed' => false,
                'course_material_id' => $cs301Mat?->id,
            ],
            [
                'title' => 'Implement Vue 3 Composition API Dashboard Component',
                'description' => 'Build reactive stats grid connected to backend REST endpoint.',
                'due_date' => now()->addDays(7),
                'completed' => false,
                'course_material_id' => $se302Mat?->id,
            ],
            [
                'title' => 'Review Python Basics & List Comprehensions',
                'description' => 'Completed intro review before starting Algorithms syllabus.',
                'due_date' => now()->subDays(2),
                'completed' => true,
                'completed_at' => now()->subDays(2),
            ],
        ];

        foreach ($tasks as $t) {
            StudyTask::updateOrCreate(
                [
                    'user_id' => $studentAbel->id,
                    'title' => $t['title'],
                ],
                [
                    'description' => $t['description'],
                    'due_date' => $t['due_date'],
                    'completed' => $t['completed'],
                    'completed_at' => $t['completed_at'] ?? null,
                    'course_material_id' => $t['course_material_id'] ?? null,
                ]
            );
        }

        $this->command->info('Study Tasks seeded: ' . StudyTask::count());

        // 11. Seed App Notifications
        $cs201MatOfferingId = $cs201Mat?->course_offering_id;
        $materialsActionUrl = $cs201MatOfferingId ? "/offerings/{$cs201MatOfferingId}/materials" : '/departments';
        $cs201Note = $createdNotes['CS201_Lecture01_Arrays_BigO.pdf'] ?? null;
        $cs201Quiz = $createdQuizzes['CS201_Lecture01_Arrays_BigO.pdf'] ?? null;

        $notifications = [
            [
                'user_id' => $studentAbel->id,
                'type' => 'task_reminder',
                'title' => 'Study Task Due Soon: Big-O Notation Practice',
                'message' => 'Your study task "Master Big-O Notation & Dynamic Array Resizing" is due in 2 days.',
                'action_url' => '/tasks',
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'quiz_generated',
                'title' => 'New AI Practice Quiz: Data Structures & Algorithms',
                'message' => 'Adaptive 10-question AI quiz has been generated for CS201. Test your knowledge and boost your score!',
                'action_url' => $cs201Quiz ? "/quizzes/{$cs201Quiz->id}" : '/quizzes',
                'read_at' => null,
                'created_at' => now()->subHours(4),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'note_generated',
                'title' => 'AI Study Note Ready: CS201 Lecture 01 Summary',
                'message' => 'Your AI summary for "Asymptotic Analysis & Arrays" has been generated with key takeaways and formulas.',
                'action_url' => $cs201Note ? "/notes/{$cs201Note->id}" : '/notes',
                'read_at' => null,
                'created_at' => now()->subHours(6),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'material_uploaded',
                'title' => 'New Material Uploaded: CS201 Data Structures',
                'message' => 'Instructor uploaded "Lecture 01 - Asymptotic Analysis & Arrays". AI summary and quiz are now available.',
                'action_url' => $materialsActionUrl,
                'read_at' => now()->subDays(1),
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'streak_milestone',
                'title' => '🔥 3-Day Study Streak Achieved!',
                'message' => 'Great consistency, Abel! You completed 4 study tasks this week. Keep going to reach Level 2 Mastery!',
                'action_url' => '/progress',
                'read_at' => now()->subDays(1),
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'material_uploaded',
                'title' => 'New Material Uploaded: CS301 Database Systems',
                'message' => 'Instructor uploaded "Lecture 01 - Relational Data Model & Normalization (1NF-BCNF)" with downloadable slides.',
                'action_url' => '/departments',
                'read_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'system_welcome',
                'title' => 'Welcome to Noras Learning Platform!',
                'message' => 'Your student account is verified for Wollo University (KIoT - Software Engineering). Explore your courses and AI-powered study tools.',
                'action_url' => '/departments',
                'read_at' => now()->subDays(3),
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $studentAbel->id,
                'type' => 'announcement',
                'title' => 'KIoT Semester 1 Exam Schedule Announcement',
                'message' => 'Midterm examination schedule for Software Engineering Year 2 has been updated. Review your course calendar.',
                'action_url' => '/departments',
                'read_at' => now()->subDays(4),
                'created_at' => now()->subDays(4),
            ],
            [
                'user_id' => $admin->id,
                'type' => 'student_registered',
                'title' => 'New Student Registration Pending Review',
                'message' => 'Student Yared Alemu (Electrical & Computer Engineering) has registered and is awaiting account approval.',
                'action_url' => '/admin/students',
                'read_at' => null,
                'created_at' => now()->subHours(1),
            ],
        ];

        foreach ($notifications as $n) {
            AppNotification::updateOrCreate(
                [
                    'user_id' => $n['user_id'],
                    'title' => $n['title'],
                ],
                [
                    'type' => $n['type'],
                    'message' => $n['message'],
                    'action_url' => $n['action_url'],
                    'read_at' => $n['read_at'],
                ]
            );
        }

        $this->command->info('App Notifications seeded: ' . AppNotification::count());

        $this->command->info("\n=========================================");
        $this->command->info("COMPREHENSIVE SEEDING COMPLETED SUCCESSFULLY!");
        $this->command->info("Universities:      " . University::count());
        $this->command->info("Departments:       " . Department::count());
        $this->command->info("Users:             " . User::count());
        $this->command->info("Courses:           " . Course::count());
        $this->command->info("Course Offerings:  " . CourseOffering::count());
        $this->command->info("Course Materials:  " . CourseMaterial::count());
        $this->command->info("Generated Notes:   " . GeneratedNote::count());
        $this->command->info("Generated Quizzes: " . GeneratedQuiz::count());
        $this->command->info("Quiz Questions:    " . QuizQuestion::count());
        $this->command->info("Quiz Attempts:     " . QuizAttempt::count());
        $this->command->info("Bookmarks:         " . Bookmark::count());
        $this->command->info("Study Tasks:       " . StudyTask::count());
        $this->command->info("App Notifications: " . AppNotification::count());
        $this->command->info("=========================================\n");
    }
}
