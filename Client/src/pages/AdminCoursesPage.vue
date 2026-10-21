<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Courses Management</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage academic courses, catalog codes, and credit hours</p>
          </div>
          <button 
            @click="openAddModal"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-primary-600 text-white text-xs sm:text-sm font-semibold rounded-xl hover:bg-primary-700 active:bg-primary-800 shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Course
          </button>
        </div>
      </div>

      <!-- Filters & Search (Compact Single-Row Bar) -->
      <div class="bg-white p-2.5 sm:px-3.5 rounded-xl border border-gray-200 shadow-2xs flex flex-col xl:flex-row gap-2.5 items-stretch xl:items-center justify-between">
        <div class="relative w-full xl:w-64">
          <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search courses or codes..."
            class="w-full pl-9 pr-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all"
          />
        </div>
        <div class="w-full xl:w-auto flex flex-wrap items-center gap-2">
          <!-- Campus Filter -->
          <div class="flex items-center gap-1.5">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Campus:</label>
            <SearchableSelect
              v-model="filterCampus"
              :options="campuses"
              allOptionLabel="All Campuses"
              allOptionValue="all"
              placeholder="All Campuses"
              searchPlaceholder="Search campus..."
              size="sm"
              widthClass="w-36 sm:w-40"
              dropdownWidthClass="w-56"
              @change="onFilterCampusChange"
            />
          </div>

          <!-- Department Filter -->
          <div class="flex items-center gap-1.5">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Department:</label>
            <SearchableSelect
              v-model="filterDepartment"
              :options="filteredDepartmentsForCoursesFilter"
              allOptionLabel="All Departments"
              allOptionValue="all"
              placeholder="All Departments"
              searchPlaceholder="Search 80+ departments..."
              size="sm"
              widthClass="w-44 sm:w-56"
              dropdownWidthClass="w-64 sm:w-72"
            />
          </div>

          <!-- Status Filter -->
          <div class="flex items-center gap-1.5">
            <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Status:</label>
            <select 
              v-model="filterStatus"
              class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none bg-white transition-all text-gray-700 min-h-[32px]"
            >
              <option value="all">All Courses</option>
              <option value="active">Active Only</option>
              <option value="inactive">Inactive Only</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State (Skeleton Table) -->
      <div v-if="loading" class="py-2">
        <SkeletonLoader type="table" :rows="6" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ error }}</p>
        </div>
        <button @click="fetchCourses" class="text-sm text-red-700 hover:text-red-800 underline font-medium">
          Try again
        </button>
      </div>

      <!-- Course List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredCourses.length === 0" class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center shadow-sm min-h-[440px] flex flex-col items-center justify-center">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No courses found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            {{ searchQuery || filterStatus !== 'all' || filterDepartment !== 'all' ? 'No courses match your filter criteria.' : 'Create academic courses to enable curriculum course offerings.' }}
          </p>
          <button 
            v-if="!searchQuery && filterStatus === 'all' && filterDepartment === 'all'"
            @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add your first course
          </button>
        </div>

        <!-- Table (Large Box) -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[500px] flex flex-col justify-between">
          <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Code</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Course Title</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department & Curriculum</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Credits</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="course in paginatedCourses" :key="course.id" class="hover:bg-gray-50/80 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-primary-700 border border-blue-100">
                      {{ course.code }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-bold text-gray-900">{{ course.title || course.name }}</div>
                    <div v-if="course.description" class="text-xs text-gray-500 mt-0.5 line-clamp-1 max-w-sm">
                      {{ course.description }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      v-if="course.offerings && course.offerings.length > 0"
                      type="button"
                      @click="viewCurriculumDetails(course)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl border border-primary-200 transition-all shadow-2xs cursor-pointer hover:shadow-xs active:scale-95"
                      title="View department & curriculum details"
                    >
                      <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <span>View</span>
                    </button>
                    <div v-else class="flex items-center gap-2">
                      <span class="text-xs text-gray-400 italic">Not assigned</span>
                      <button
                        type="button"
                        @click="openEditModal(course)"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700 underline cursor-pointer"
                      >
                        + Assign
                      </button>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs font-medium text-gray-700">
                      {{ course.credit_hours ?? course.credits ?? 3 }} credit hours
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full"
                      :class="course.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-200'"
                    >
                      {{ course.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <div class="relative inline-block text-left" @click.stop>
                      <button 
                        type="button"
                        @click.stop="toggleActionMenu(course.id)" 
                        class="p-1.5 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 cursor-pointer"
                        title="Actions"
                      >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </button>

                      <!-- 3-Dots Dropdown Menu -->
                      <div 
                        v-if="activeActionMenuId === course.id"
                        @click.stop
                        v-click-outside="() => activeActionMenuId = null"
                        class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-30 divide-y divide-gray-100 overflow-hidden"
                      >
                        <div class="py-1">
                          <button 
                            type="button"
                            @click="viewCurriculumDetails(course)" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>View Curriculum</span>
                          </button>
                          <button 
                            type="button"
                            @click="openEditModal(course)" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Edit Course</span>
                          </button>
                        </div>
                        <div class="py-1">
                          <button 
                            type="button"
                            @click="deleteCourse(course)" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Delete Course</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <DynamicPagination
            v-if="filteredCourses.length > 0"
            v-model:currentPage="currentPage"
            v-model:perPage="perPage"
            :totalPages="totalPages"
            :totalItems="filteredCourses.length"
            itemLabel="courses"
          />
        </div>
      </div>
    </div>

    <!-- Course Modal (Add / Edit) -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div 
            class="relative transform rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100"
            @click.stop
          >
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900">
                  {{ isEditing ? 'Edit Course' : 'Add New Course' }}
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                  Configure course syllabus details and curriculum department assignments.
                </p>
              </div>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveCourse" class="p-6 sm:p-8 space-y-4 sm:space-y-5">
              <!-- Error banner if any -->
              <div v-if="formError" class="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                {{ formError }}
              </div>

              <!-- Row 1: Code and Credit Hours -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Course Code -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Course Code <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model.trim="form.code"
                    type="text" 
                    required
                    maxlength="20"
                    placeholder="e.g. CS101, IT204, or MATH101"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all uppercase"
                  />
                </div>

                <!-- Credit Hours -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Credit Hours <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model.number="form.credit_hours"
                    type="number" 
                    required
                    min="1"
                    max="10"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                  />
                  <p class="text-[11px] text-gray-400 mt-1">Value between 1 and 10 credit units.</p>
                </div>
              </div>

              <!-- Row 2: Course Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Course Title <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="form.title"
                  type="text" 
                  required
                  placeholder="e.g. Introduction to Computer Science"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Description
                </label>
                <textarea 
                  v-model.trim="form.description"
                  rows="3"
                  placeholder="Summary of course content, objectives, or syllabus overview..."
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all resize-none"
                ></textarea>
              </div>

              <!-- Department & Curriculum Assignment -->
              <div class="space-y-3 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- Campus Filter / Selector -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                      Campus <span class="text-gray-400 font-normal">(Filter)</span>
                    </label>
                    <SearchableSelect
                      v-model="modalCampusId"
                      :options="campuses"
                      allOptionLabel="All Campuses"
                      allOptionValue=""
                      placeholder="Filter by Campus"
                      searchPlaceholder="Search campus..."
                      size="md"
                      @change="onModalCampusChange"
                    />
                  </div>

                  <!-- Department Assignment -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                      Assign to Department <span class="text-primary-600 font-normal">(Curriculum)</span>
                    </label>
                    <SearchableSelect
                      v-model="form.department_id"
                      :options="filteredDepartmentsForModal"
                      allOptionLabel="-- No Department --"
                      allOptionValue=""
                      placeholder="Select Department"
                      searchPlaceholder="Search departments..."
                      size="md"
                      @change="onDepartmentSelect"
                    />
                  </div>
                </div>
                <p class="text-[11px] text-gray-400">
                  Select a department to automatically make this course available in that department's student curriculum.
                </p>

                <!-- Study Years & Semester (shown when department selected) -->
                <div v-if="form.department_id" class="p-4 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3.5">
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                      Study Year(s) <span class="text-red-500">*</span>
                    </label>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                      {{ selectedDept?.name }} is a {{ selectedDeptTotalYears }}-year program. Select the study year(s) for this course.
                    </p>
                  </div>

                  <!-- Year Selection Checkboxes -->
                  <div class="flex flex-wrap gap-2">
                    <label 
                      v-for="yr in availableStudyYears" 
                      :key="yr"
                      :class="[
                        'flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer transition-all select-none',
                        form.study_years.includes(yr)
                          ? 'bg-primary-600 text-white border-primary-600 shadow-xs'
                          : 'bg-white text-gray-700 border-gray-300 hover:border-primary-400'
                      ]"
                    >
                      <input 
                        type="checkbox" 
                        :value="yr" 
                        v-model="form.study_years" 
                        class="sr-only"
                      />
                      <span>Year {{ yr }}</span>
                      <svg v-if="form.study_years.includes(yr)" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </label>
                  </div>

                  <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-200">
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Semester</label>
                      <select 
                        v-model="form.semester"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                      >
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="both">Both Semesters</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700 mb-1">Academic Year</label>
                      <input 
                        v-model.number="form.academic_year"
                        type="number"
                        min="2020"
                        max="2035"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Active Status -->
              <div class="flex items-center gap-3 pt-1">
                <input 
                  id="course-active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500"
                />
                <label for="course-active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">
                  Active (available for semester offerings and materials)
                </label>
              </div>

              <!-- Modal Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm"
                >
                  <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ saving ? 'Saving...' : (isEditing ? 'Update Course' : 'Create Course') }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Course Curriculum Details Modal -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showCurriculumModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeCurriculumModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div 
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100"
            @click.stop
          >
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-mono font-bold text-sm">
                  {{ curriculumCourse?.code }}
                </div>
                <div>
                  <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">
                    {{ curriculumCourse?.title || curriculumCourse?.name }}
                  </h3>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500 font-medium">
                      {{ curriculumCourse?.credit_hours ?? curriculumCourse?.credits ?? 3 }} Credit Hours
                    </span>
                    <span class="text-gray-300">&bull;</span>
                    <span 
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="curriculumCourse?.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'"
                    >
                      {{ curriculumCourse?.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
              </div>
              <button @click="closeCurriculumModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Department Curriculum Breakdown</h4>
                  <p class="text-xs text-gray-400 mt-0.5">Departments, academic study years, and semesters where this course is taught</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100">
                  {{ curriculumCourse?.offerings?.length || 0 }} Offerings
                </span>
              </div>

              <!-- Offerings Breakdown Cards -->
              <div v-if="curriculumCourse?.offerings && curriculumCourse.offerings.length > 0" class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 max-h-80 overflow-y-auto">
                <div 
                  v-for="(offering, index) in curriculumCourse.offerings" 
                  :key="offering.id || index"
                  class="p-4 hover:bg-gray-50/75 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-sm text-gray-900">
                        {{ offering.department?.name || 'Department' }}
                      </span>
                      <span v-if="offering.department?.total_years" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ offering.department.total_years }} Yrs Degree
                      </span>
                    </div>
                    <p class="text-xs text-gray-500">
                      Campus: <span class="font-medium text-gray-700">{{ offering.department?.university?.name || 'Campus' }}</span>
                      <span v-if="offering.department?.code" class="text-gray-400"> ({{ offering.department.code }})</span>
                    </p>
                  </div>

                  <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100 shadow-2xs">
                      Year {{ offering.study_year }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100 shadow-2xs">
                      Semester {{ offering.semester }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                      AY {{ offering.academic_year || 2026 }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Empty Offerings State -->
              <div v-else class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-2">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">No Department Curriculum Assigned</p>
                <p class="text-xs text-gray-400 mt-1 mb-4">This course is not yet linked to a specific department study year or semester.</p>
                <button
                  type="button"
                  @click="editFromCurriculumModal"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-xs"
                >
                  Assign to Department
                </button>
              </div>

              <!-- Course description if available -->
              <div v-if="curriculumCourse?.description" class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-100 text-xs text-gray-600">
                <p class="font-semibold text-gray-700 mb-1">Course Description:</p>
                <p class="leading-relaxed">{{ curriculumCourse.description }}</p>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
              <button
                type="button"
                @click="editFromCurriculumModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Curriculum</span>
              </button>
              <button
                type="button"
                @click="closeCurriculumModal"
                class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import SearchableSelect from '../components/SearchableSelect.vue'
import DynamicPagination from '../components/DynamicPagination.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'

const notify = useNotification()

const courses = ref([])
const departments = ref([])
const campuses = ref([])
const loading = ref(true)
const error = ref(null)

const searchQuery = ref('')
const filterStatus = ref('all')
const filterCampus = ref('all')
const filterDepartment = ref('all')
const modalCampusId = ref('')
const currentPage = ref(1)
const perPage = ref(10)

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const activeActionMenuId = ref(null)
const showCurriculumModal = ref(false)
const curriculumCourse = ref(null)

const vClickOutside = {
  mounted(el, binding) {
    el._clickOutsideHandler = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    setTimeout(() => {
      document.addEventListener('click', el._clickOutsideHandler)
    }, 0)
  },
  unmounted(el) {
    if (el._clickOutsideHandler) {
      document.removeEventListener('click', el._clickOutsideHandler)
    }
  }
}

const form = ref({
  code: '',
  title: '',
  description: '',
  credit_hours: 3,
  is_active: true,
  department_id: '',
  study_years: [2],
  semester: '1',
  academic_year: 2026,
})

const selectedDept = computed(() => {
  if (!form.value.department_id) return null
  return departments.value.find(d => d.id === form.value.department_id) || null
})

const selectedDeptTotalYears = computed(() => {
  return selectedDept.value?.total_years || 4
})

const availableStudyYears = computed(() => {
  const total = selectedDeptTotalYears.value
  return Array.from({ length: total }, (_, i) => i + 1)
})

const navigationItems = [
  { path: '/admin/dashboard', label: 'Dashboard', icon: 'DashboardIcon' },
  { path: '/admin/students', label: 'Student Approvals', icon: 'ApprovalIcon' },
  { path: '/admin/notifications', label: 'Notifications', icon: 'ReportsIcon' },
  { path: '/admin/campus', label: 'Campuses', icon: 'ProductsIcon' },
  { path: '/admin/departments', label: 'Departments', icon: 'InventoryIcon' },
  { path: '/admin/courses', label: 'Courses', icon: 'BookIcon' },
  { path: '/admin/materials', label: 'Course Materials', icon: 'SalesIcon' },
  { path: '/admin/settings', label: 'Settings', icon: 'SettingsIcon' },
]

const filteredDepartmentsForCoursesFilter = computed(() => {
  if (filterCampus.value === 'all') {
    return departments.value
  }
  return departments.value.filter(d => d.university_id === filterCampus.value || d.university?.id === filterCampus.value)
})

const onFilterCampusChange = () => {
  if (filterDepartment.value !== 'all') {
    const belongs = filteredDepartmentsForCoursesFilter.value.some(d => d.id === filterDepartment.value)
    if (!belongs) {
      filterDepartment.value = 'all'
    }
  }
}

const filteredDepartmentsForModal = computed(() => {
  if (!modalCampusId.value) {
    return departments.value
  }
  return departments.value.filter(d => d.university_id === modalCampusId.value || d.university?.id === modalCampusId.value)
})

const onModalCampusChange = () => {
  if (form.value.department_id) {
    const belongs = filteredDepartmentsForModal.value.some(d => d.id === form.value.department_id)
    if (!belongs) {
      form.value.department_id = ''
      onDepartmentSelect()
    }
  }
}

const filteredCourses = computed(() => {
  return courses.value.filter(c => {
    const code = c.code || ''
    const title = c.title || c.name || ''
    const matchesSearch = !searchQuery.value ||
      code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      title.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = filterStatus.value === 'all' ||
      (filterStatus.value === 'active' && c.is_active) ||
      (filterStatus.value === 'inactive' && !c.is_active)

    const matchesCampus = filterCampus.value === 'all' ||
      (c.offerings && c.offerings.some(o => {
        const dept = departments.value.find(d => d.id === o.department_id)
        return (o.department?.university_id === filterCampus.value) || (dept?.university_id === filterCampus.value)
      }))

    const matchesDepartment = filterDepartment.value === 'all' ||
      (c.offerings && c.offerings.some(o => o.department_id === filterDepartment.value))

    return matchesSearch && matchesStatus && matchesCampus && matchesDepartment
  })
})

const totalPages = computed(() => {
  return Math.ceil(filteredCourses.value.length / perPage.value) || 1
})

const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredCourses.value.slice(start, start + perPage.value)
})

watch([searchQuery, filterStatus, filterCampus, filterDepartment], () => {
  currentPage.value = 1
})

const fetchCourses = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/courses')
    if (response.data.status === 'success') {
      courses.value = response.data.data.courses || []
    }
  } catch (err) {
    console.error('Failed to fetch courses:', err)
    error.value = 'Failed to load courses'
  } finally {
    loading.value = false
  }
}

const fetchDepartments = async () => {
  try {
    const response = await api.get('/admin/departments')
    if (response.data.status === 'success') {
      departments.value = response.data.data.departments || []
    }
  } catch (err) {
    console.error('Failed to fetch departments:', err)
  }
}

const fetchCampuses = async () => {
  try {
    const response = await api.get('/admin/universities')
    if (response.data.status === 'success') {
      campuses.value = response.data.data.universities || []
    }
  } catch (err) {
    console.error('Failed to fetch campuses:', err)
  }
}

const toggleActionMenu = (courseId) => {
  activeActionMenuId.value = activeActionMenuId.value === courseId ? null : courseId
}

const viewCurriculumDetails = (course) => {
  activeActionMenuId.value = null
  curriculumCourse.value = course
  showCurriculumModal.value = true
}

const closeCurriculumModal = () => {
  showCurriculumModal.value = false
  curriculumCourse.value = null
}

const editFromCurriculumModal = () => {
  const targetCourse = curriculumCourse.value
  closeCurriculumModal()
  if (targetCourse) {
    openEditModal(targetCourse)
  }
}

const onDepartmentSelect = () => {
  if (!form.value.department_id) {
    form.value.study_years = [2]
    return
  }

  const total = selectedDeptTotalYears.value
  if (total >= 2) {
    form.value.study_years = Array.from({ length: total - 1 }, (_, i) => i + 2)
  } else {
    form.value.study_years = [1]
  }
}

const openAddModal = () => {
  activeActionMenuId.value = null
  isEditing.value = false
  editingId.value = null
  modalCampusId.value = ''
  form.value = {
    code: '',
    title: '',
    description: '',
    credit_hours: 3,
    is_active: true,
    department_id: '',
    study_years: [2],
    semester: '1',
    academic_year: 2026,
  }
  formError.value = ''
  showModal.value = true
}

const openEditModal = (course) => {
  activeActionMenuId.value = null
  isEditing.value = true
  editingId.value = course.id
  
  const existingOffering = course.offerings && course.offerings.length > 0 ? course.offerings[0] : null
  const existingYears = course.offerings && course.offerings.length > 0 
    ? [...new Set(course.offerings.map(o => o.study_year))].sort((a, b) => a - b)
    : [2]

  const existingDeptId = existingOffering ? existingOffering.department_id : ''
  const existingDept = departments.value.find(d => d.id === existingDeptId)
  modalCampusId.value = existingOffering?.department?.university_id || existingDept?.university_id || ''

  form.value = {
    code: course.code,
    title: course.title || course.name || '',
    description: course.description || '',
    credit_hours: course.credit_hours ?? course.credits ?? 3,
    is_active: Boolean(course.is_active),
    department_id: existingDeptId,
    study_years: existingYears,
    semester: existingOffering ? existingOffering.semester : '1',
    academic_year: existingOffering?.academic_year || 2026,
  }
  formError.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
}

const saveCourse = async () => {
  if (!form.value.code.trim() || !form.value.title.trim()) {
    formError.value = 'Please provide both course code and course title.'
    return
  }
  if (!form.value.credit_hours || form.value.credit_hours < 1 || form.value.credit_hours > 10) {
    formError.value = 'Credit hours must be an integer between 1 and 10.'
    return
  }
  if (form.value.department_id && (!form.value.study_years || form.value.study_years.length === 0)) {
    formError.value = 'Please select at least one study year for the curriculum assignment.'
    return
  }

  saving.value = true
  formError.value = ''

  try {
    const payload = {
      code: form.value.code.trim().toUpperCase(),
      title: form.value.title.trim(),
      description: form.value.description ? form.value.description.trim() : null,
      credit_hours: parseInt(form.value.credit_hours),
      is_active: form.value.is_active,
      department_id: form.value.department_id || null,
      study_years: form.value.study_years,
      semester: form.value.semester,
      academic_year: form.value.academic_year || 2026,
    }

    if (isEditing.value) {
      await api.put(`/admin/courses/${editingId.value}`, payload)
      notify.success(`Course "${payload.code}" updated successfully.`)
    } else {
      await api.post('/admin/courses', payload)
      notify.success(`Course "${payload.code}" created and assigned to curriculum.`)
    }

    closeModal()
    await fetchCourses()
  } catch (err) {
    console.error('Failed to save course:', err)
    if (err.response?.data?.message) {
      formError.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      const firstError = Object.values(err.response.data.errors)[0]
      formError.value = Array.isArray(firstError) ? firstError[0] : firstError
    } else {
      formError.value = 'An error occurred while saving the course.'
    }
  } finally {
    saving.value = false
  }
}

const deleteCourse = async (course) => {
  activeActionMenuId.value = null
  const confirmed = await notify.confirm({
    title: `Delete Course "${course.code}"?`,
    message: `Are you sure you want to permanently delete "${course.title || course.code}"? Associated offerings and course materials will be removed.`,
    confirmText: 'Delete Course',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/courses/${course.id}?force=true`)
    if (response.data.status === 'success') {
      notify.success(`Course "${course.code}" deleted successfully.`)
      await fetchCourses()
    } else {
      notify.error(response.data.message || 'Failed to delete course')
    }
  } catch (err) {
    console.error('Failed to delete course:', err)
    const msg = err.response?.data?.message || 'Failed to delete course'
    notify.error(msg)
  }
}

onMounted(() => {
  fetchCourses()
  fetchDepartments()
  fetchCampuses()
})
</script>
