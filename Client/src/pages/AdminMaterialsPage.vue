<template>
  <DashboardLayout 
    pageTitle="Noras Admin" 
    :navigationItems="navigationItems"
  >
    <div class="p-6 sm:p-8 max-w-7xl mx-auto space-y-6">
      <!-- Back Button & Page Header -->
      <div>
        <button 
          @click="$router.push('/admin/dashboard')"
          class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors mb-3 group"
        >
          <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Dashboard
        </button>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Course Materials</h1>
            <p class="text-sm text-gray-600 mt-1">Upload and manage course presentations (PPT) and documents (PDF) for students</p>
          </div>
          <button 
            @click="openUploadModal"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 active:bg-primary-800 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload Material
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Materials</div>
          <div class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</div>
          <p class="text-xs text-gray-400 mt-1">Uploaded academic files</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PDF Documents</div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-red-700">PDF</span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-red-600 mt-2">{{ stats.pdfCount }}</div>
          <p class="text-xs text-gray-400 mt-1">Reading & reference materials</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PPT Slides</div>
            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-700">PPT</span>
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-amber-600 mt-2">{{ stats.pptCount }}</div>
          <p class="text-xs text-gray-400 mt-1">Lecture presentations</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
          <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Published</div>
          <div class="text-2xl sm:text-3xl font-bold text-emerald-600 mt-2">{{ stats.published }}</div>
          <p class="text-xs text-gray-400 mt-1">Accessible by students</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row gap-3 items-center justify-between">
          <div class="relative w-full md:w-80">
            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="Search by title, course, or department..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
            />
          </div>

          <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
            <!-- Campus Filter -->
            <select 
              v-model="filters.campusId"
              @change="onFilterCampusChange"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Campuses</option>
              <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>

            <!-- Department Filter -->
            <select 
              v-model="filters.departmentId"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700 max-w-[180px]"
            >
              <option value="">All Departments</option>
              <option v-for="d in filteredDepartmentsForFilter" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>

            <!-- Study Year Filter -->
            <select 
              v-model="filters.studyYear"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Years</option>
              <option v-for="yr in [1, 2, 3, 4, 5, 6, 7]" :key="yr" :value="yr">Year {{ yr }}</option>
            </select>

            <!-- Semester Filter -->
            <select 
              v-model="filters.semester"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Semesters</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
            </select>

            <!-- File Type Filter -->
            <select 
              v-model="filters.fileType"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Types</option>
              <option value="pdf">PDF Documents</option>
              <option value="ppt">PowerPoint (PPT)</option>
            </select>

            <!-- Status Filter -->
            <select 
              v-model="filters.status"
              class="px-3 py-2 border border-gray-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Statuses</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>

            <button 
              v-if="hasActiveFilters"
              @click="resetFilters"
              class="px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-800 underline cursor-pointer"
            >
              Reset
            </button>
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
        <button @click="fetchMaterials" class="text-sm text-red-700 hover:text-red-800 underline font-medium cursor-pointer">
          Try again
        </button>
      </div>

      <!-- Materials List -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="filteredMaterials.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
          <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No course materials found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            {{ hasActiveFilters ? 'No materials match your current filter settings.' : 'Upload PDFs or PowerPoint presentations for student access.' }}
          </p>
          <button 
            v-if="!hasActiveFilters"
            @click="openUploadModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Upload your first material
          </button>
        </div>

        <!-- Table -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Document</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Target Curriculum</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type & Size</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Uploaded</th>
                  <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="material in filteredMaterials" :key="material.id" class="hover:bg-gray-50/80 transition-colors">
                  <!-- Document title and original name -->
                  <td class="px-6 py-4">
                    <div class="flex items-start gap-3">
                      <div 
                        class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs shadow-2xs"
                        :class="isPpt(material) ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-red-50 text-red-600 border border-red-200'"
                      >
                        <span v-if="isPpt(material)">PPT</span>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div class="min-w-0">
                        <div class="text-sm font-bold text-gray-900 truncate max-w-xs sm:max-w-md" :title="material.title">
                          {{ material.title }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">
                          {{ material.original_filename }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Target Curriculum -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      type="button"
                      @click="viewCurriculumDetails(material)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-xl border border-primary-200 transition-all shadow-2xs cursor-pointer hover:shadow-xs active:scale-95"
                      title="View target curriculum details"
                    >
                      <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <span>View</span>
                    </button>
                  </td>

                  <!-- Type & Size -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                      <span 
                        class="px-2 py-0.5 text-[10px] font-bold rounded-md uppercase"
                        :class="isPpt(material) ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'"
                      >
                        {{ isPpt(material) ? 'PPT' : 'PDF' }}
                      </span>
                      <span class="text-xs text-gray-600 font-medium">
                        {{ formatFileSize(material.file_size) }}
                      </span>
                    </div>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize"
                      :class="getStatusBadgeClass(material.status)"
                    >
                      {{ material.status || 'published' }}
                    </span>
                  </td>

                  <!-- Uploaded Date -->
                  <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                    {{ formatDate(material.created_at) }}
                  </td>

                  <!-- 3-Dots Action Dropdown -->
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <div class="relative inline-block text-left" @click.stop>
                      <button 
                        type="button"
                        @click.stop="toggleActionMenu(material.id)" 
                        class="p-1.5 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 cursor-pointer"
                        title="Actions"
                      >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </button>

                      <!-- Dropdown Menu -->
                      <div 
                        v-if="activeActionMenuId === material.id"
                        @click.stop
                        v-click-outside="() => activeActionMenuId = null"
                        class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-30 divide-y divide-gray-100 overflow-hidden"
                      >
                        <!-- Standard File Actions Group -->
                        <div class="py-1">
                          <button 
                            type="button"
                            @click="viewCurriculumDetails(material); activeActionMenuId = null" 
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
                            @click="downloadMaterial(material); activeActionMenuId = null" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download File</span>
                          </button>
                          <button 
                            type="button"
                            @click="togglePublish(material)" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ material.status === 'published' ? 'Set as Draft' : 'Publish' }}</span>
                          </button>
                        </div>
                        <div class="py-1">
                          <button 
                            type="button"
                            @click="deleteMaterial(material)" 
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Delete Material</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Material Modal -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeUploadModal"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div 
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100"
            @click.stop
          >
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900">Upload Course Material</h3>
                <p class="text-xs text-gray-500 mt-0.5">Upload a PDF document or PowerPoint presentation (PPT) to Cloudflare</p>
              </div>
              <button @click="closeUploadModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="submitUpload" class="p-6 space-y-4">
              <!-- Error Banner -->
              <div v-if="uploadError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                {{ uploadError }}
              </div>

              <!-- Target Academic Curriculum (Campus, Department, Year, Semester, Course) -->
              <div class="p-4 bg-gray-50/90 rounded-2xl border border-gray-200 space-y-3.5">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                  Target Curriculum Assignment
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <!-- Campus Selection -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Campus <span class="text-red-500">*</span></label>
                    <select 
                      v-model="uploadForm.campus_id"
                      @change="onModalCampusChange"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                    >
                      <option value="" disabled>Select Campus</option>
                      <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                  </div>

                  <!-- Department Selection -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                    <select 
                      v-model="uploadForm.department_id"
                      @change="onModalDepartmentChange"
                      required
                      :disabled="!uploadForm.campus_id"
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none disabled:bg-gray-100 disabled:text-gray-400"
                    >
                      <option value="" disabled>{{ !uploadForm.campus_id ? 'Select campus first' : 'Select Department' }}</option>
                      <option v-for="d in filteredDepartmentsForModal" :key="d.id" :value="d.id">
                        {{ d.name }} ({{ d.total_years || 4 }} Yrs)
                      </option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <!-- Study Year -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Study Year <span class="text-red-500">*</span></label>
                    <select 
                      v-model="uploadForm.study_year"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                    >
                      <option v-for="yr in availableYearsForModal" :key="yr" :value="yr">Year {{ yr }}</option>
                    </select>
                  </div>

                  <!-- Semester -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                    <select 
                      v-model="uploadForm.semester"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none"
                    >
                      <option value="1">Semester 1</option>
                      <option value="2">Semester 2</option>
                    </select>
                  </div>

                  <!-- Course Selection -->
                  <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Course <span class="text-red-500">*</span></label>
                    <select 
                      v-model="uploadForm.course_id"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-primary-500 outline-none truncate"
                    >
                      <option value="" disabled>Select Course</option>
                      <option v-for="course in courses" :key="course.id" :value="course.id">
                        {{ course.code }} - {{ course.title || course.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Document Title -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Material Title <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model.trim="uploadForm.title"
                  type="text" 
                  required
                  placeholder="e.g. Chapter 1: Introduction to Data Structures & Algorithms"
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Description / Topics Covered
                </label>
                <textarea 
                  v-model.trim="uploadForm.description"
                  rows="2"
                  placeholder="Brief summary or lecture outline for students..."
                  class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all resize-none"
                ></textarea>
              </div>

              <!-- File Upload Dropzone (PDF or PPT) -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                  Document / Presentation File <span class="text-red-500">*</span>
                </label>

                <div 
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleFileDrop"
                  :class="[
                    'relative border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer',
                    isDragging ? 'border-primary-500 bg-primary-50/50' : 'border-gray-300 hover:border-primary-400 bg-gray-50/50'
                  ]"
                  @click="$refs.fileInput.click()"
                >
                  <input 
                    ref="fileInput"
                    type="file" 
                    accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation"
                    class="hidden" 
                    @change="handleFileSelect"
                  />

                  <!-- Selected File Display -->
                  <div v-if="selectedFile" class="flex items-center justify-between gap-3 text-left p-2.5 bg-white rounded-xl border border-gray-200">
                    <div class="flex items-center gap-3 min-w-0">
                      <div 
                        class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0"
                        :class="detectedType === 'ppt' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'"
                      >
                        {{ detectedType.toUpperCase() }}
                      </div>
                      <div class="min-w-0">
                        <div class="text-xs font-bold text-gray-900 truncate">{{ selectedFile.name }}</div>
                        <div class="text-[11px] text-gray-400">{{ formatFileSize(selectedFile.size) }}</div>
                      </div>
                    </div>
                    <button 
                      type="button" 
                      @click.stop="clearSelectedFile" 
                      class="text-gray-400 hover:text-red-600 p-1 rounded-lg hover:bg-gray-50 transition-colors"
                      title="Remove file"
                    >
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>

                  <!-- Prompt to select/drop -->
                  <div v-else class="space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-primary-100 text-primary-600 mx-auto flex items-center justify-center">
                      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs font-bold text-primary-600 hover:underline">Click to upload</span>
                      <span class="text-xs text-gray-500"> or drag and drop</span>
                    </div>
                    <p class="text-[11px] text-gray-400">
                      PDF documents or PowerPoint presentations (.pdf, .ppt, .pptx) up to 10MB
                    </p>
                  </div>
                </div>
              </div>

              <!-- Publish Status -->
              <div class="flex items-center gap-3 pt-1">
                <input 
                  id="material-publish"
                  v-model="uploadForm.is_published"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500 cursor-pointer"
                />
                <label for="material-publish" class="text-xs font-medium text-gray-700 select-none cursor-pointer">
                  Publish immediately (students can access and download this material right away)
                </label>
              </div>

              <!-- Modal Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                <button
                  type="button"
                  @click="closeUploadModal"
                  class="px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="isUploading || !selectedFile"
                  class="inline-flex items-center gap-2 px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 transition-colors shadow-sm cursor-pointer"
                >
                  <svg v-if="isUploading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>{{ isUploading ? 'Uploading to Cloudflare...' : 'Upload Material' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- Target Curriculum Details Modal -->
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
            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100"
            @click.stop
          >
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
              <div class="flex items-center gap-3">
                <div 
                  class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs shadow-2xs"
                  :class="isPpt(curriculumMaterial) ? 'bg-amber-100 text-amber-700 border border-amber-200' : 'bg-red-100 text-red-700 border border-red-200'"
                >
                  <span v-if="isPpt(curriculumMaterial)">PPT</span>
                  <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <h3 class="text-base font-bold text-gray-900 truncate max-w-xs sm:max-w-sm">
                    {{ curriculumMaterial?.title }}
                  </h3>
                  <p class="text-xs text-gray-500 truncate max-w-xs">
                    {{ curriculumMaterial?.original_filename }} &bull; {{ formatFileSize(curriculumMaterial?.file_size) }}
                  </p>
                </div>
              </div>
              <button @click="closeCurriculumModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Modal Content: Target Curriculum Details -->
            <div class="p-6 space-y-4">
              <div>
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Assigned Academic Curriculum</h4>
                <p class="text-xs text-gray-500 mt-0.5">Students enrolled in this curriculum will see and download this material.</p>
              </div>

              <div class="bg-gray-50/75 rounded-xl border border-gray-200/80 p-4 space-y-3">
                <!-- Course -->
                <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Course</span>
                  <div class="text-right">
                    <div class="text-xs font-bold text-gray-900">
                      {{ curriculumMaterial?.course_offering?.course?.code || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-600">
                      {{ curriculumMaterial?.course_offering?.course?.title || curriculumMaterial?.course_offering?.course?.name || 'General Course' }}
                    </div>
                  </div>
                </div>

                <!-- Campus -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Campus</span>
                  <span class="text-xs font-bold text-gray-900">
                    {{ curriculumMaterial?.course_offering?.department?.university?.name || 'All Campuses' }}
                  </span>
                </div>

                <!-- Department -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Department</span>
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md border border-primary-100">
                      {{ curriculumMaterial?.course_offering?.department?.name || 'General' }}
                    </span>
                    <span v-if="curriculumMaterial?.course_offering?.department?.total_years" class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">
                      {{ curriculumMaterial.course_offering.department.total_years }} Yrs
                    </span>
                  </div>
                </div>

                <!-- Study Year & Semester -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Cohort Level</span>
                  <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                      Year {{ curriculumMaterial?.course_offering?.study_year || 1 }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                      Semester {{ curriculumMaterial?.course_offering?.semester || 1 }}
                    </span>
                  </div>
                </div>

                <!-- Academic Year -->
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-semibold text-gray-500">Academic Year</span>
                  <span class="text-xs font-mono font-medium text-gray-700">
                    AY {{ curriculumMaterial?.course_offering?.academic_year || 2026 }}
                  </span>
                </div>
              </div>

              <!-- Document Description if available -->
              <div v-if="curriculumMaterial?.description" class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-600">
                <span class="font-semibold text-gray-800 block mb-0.5">Description:</span>
                <p>{{ curriculumMaterial.description }}</p>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
              <button 
                type="button" 
                @click="downloadMaterial(curriculumMaterial)"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-primary-700 bg-primary-50 border border-primary-200 rounded-xl hover:bg-primary-100 transition-colors cursor-pointer"
              >
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download {{ isPpt(curriculumMaterial) ? 'Presentation' : 'Document' }}</span>
              </button>
              <button 
                type="button" 
                @click="closeCurriculumModal" 
                class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
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
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'
import { adminNavigationItems } from '../config/navigation'

const notify = useNotification()

const materials = ref([])
const campuses = ref([])
const departments = ref([])
const courses = ref([])
const loading = ref(true)
const error = ref(null)

const activeActionMenuId = ref(null)
const showUploadModal = ref(false)
const showCurriculumModal = ref(false)
const curriculumMaterial = ref(null)

const viewCurriculumDetails = (material) => {
  curriculumMaterial.value = material
  showCurriculumModal.value = true
}

const closeCurriculumModal = () => {
  showCurriculumModal.value = false
  curriculumMaterial.value = null
}

const isUploading = ref(false)
const uploadError = ref('')
const selectedFile = ref(null)
const isDragging = ref(false)
const fileInput = ref(null)

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

const toggleActionMenu = (materialId) => {
  activeActionMenuId.value = activeActionMenuId.value === materialId ? null : materialId
}

const filters = ref({
  search: '',
  campusId: '',
  departmentId: '',
  studyYear: '',
  semester: '',
  fileType: '',
  status: '',
})

const uploadForm = ref({
  title: '',
  description: '',
  campus_id: '',
  department_id: '',
  study_year: 2,
  semester: '1',
  course_id: '',
  is_published: true,
})

const navigationItems = adminNavigationItems

const hasActiveFilters = computed(() => {
  return Boolean(
    filters.value.search ||
    filters.value.campusId ||
    filters.value.departmentId ||
    filters.value.studyYear ||
    filters.value.semester ||
    filters.value.fileType ||
    filters.value.status
  )
})

const resetFilters = () => {
  filters.value = {
    search: '',
    campusId: '',
    departmentId: '',
    studyYear: '',
    semester: '',
    fileType: '',
    status: '',
  }
}

const isPpt = (material) => {
  if (!material) return false
  const name = (material.original_filename || '').toLowerCase()
  const mime = (material.mime_type || '').toLowerCase()
  return name.endsWith('.ppt') || name.endsWith('.pptx') || mime.includes('presentation') || mime.includes('powerpoint')
}

const detectedType = computed(() => {
  if (!selectedFile.value) return 'pdf'
  const name = selectedFile.value.name.toLowerCase()
  return (name.endsWith('.ppt') || name.endsWith('.pptx')) ? 'ppt' : 'pdf'
})

const stats = computed(() => {
  const total = materials.value.length
  const published = materials.value.filter(m => m.status === 'published').length
  const pptCount = materials.value.filter(m => isPpt(m)).length
  const pdfCount = total - pptCount

  return {
    total,
    published,
    pptCount,
    pdfCount,
  }
})

const filteredDepartmentsForFilter = computed(() => {
  if (!filters.value.campusId) return departments.value
  return departments.value.filter(d => d.university_id === parseInt(filters.value.campusId))
})

const onFilterCampusChange = () => {
  filters.value.departmentId = ''
}

const filteredDepartmentsForModal = computed(() => {
  if (!uploadForm.value.campus_id) return []
  return departments.value.filter(d => d.university_id === parseInt(uploadForm.value.campus_id))
})

const selectedDeptInModal = computed(() => {
  if (!uploadForm.value.department_id) return null
  return departments.value.find(d => d.id === uploadForm.value.department_id) || null
})

const availableYearsForModal = computed(() => {
  const totalYears = selectedDeptInModal.value?.total_years || 4
  return Array.from({ length: totalYears }, (_, i) => i + 1)
})

const onModalCampusChange = () => {
  uploadForm.value.department_id = ''
  uploadForm.value.study_year = 2
}

const onModalDepartmentChange = () => {
  uploadForm.value.study_year = 2
}

const filteredMaterials = computed(() => {
  return materials.value.filter(m => {
    const search = filters.value.search.toLowerCase().trim()
    const matchesSearch = !search ||
      m.title?.toLowerCase().includes(search) ||
      m.original_filename?.toLowerCase().includes(search) ||
      m.course_offering?.course?.code?.toLowerCase().includes(search) ||
      m.course_offering?.course?.title?.toLowerCase().includes(search) ||
      m.course_offering?.course?.name?.toLowerCase().includes(search) ||
      m.course_offering?.department?.name?.toLowerCase().includes(search)

    const matchesStatus = !filters.value.status || m.status === filters.value.status

    const matchesType = !filters.value.fileType || 
      (filters.value.fileType === 'ppt' ? isPpt(m) : !isPpt(m))

    const matchesCampus = !filters.value.campusId || 
      m.course_offering?.department?.university_id === parseInt(filters.value.campusId)

    const matchesDept = !filters.value.departmentId || 
      m.course_offering?.department_id === parseInt(filters.value.departmentId)

    const matchesYear = !filters.value.studyYear || 
      m.course_offering?.study_year === parseInt(filters.value.studyYear)

    const matchesSemester = !filters.value.semester || 
      String(m.course_offering?.semester) === String(filters.value.semester)

    return matchesSearch && matchesStatus && matchesType && matchesCampus && matchesDept && matchesYear && matchesSemester
  })
})

const fetchMaterials = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/admin/materials')
    if (response.data.status === 'success') {
      materials.value = response.data.data.materials || []
    }
  } catch (err) {
    console.error('Error fetching materials:', err)
    error.value = 'Failed to load course materials'
  } finally {
    loading.value = false
  }
}

const fetchCatalogData = async () => {
  try {
    const [campusesRes, deptsRes, coursesRes] = await Promise.all([
      api.get('/admin/universities'),
      api.get('/admin/departments'),
      api.get('/admin/courses'),
    ])

    if (campusesRes.data.status === 'success') {
      campuses.value = campusesRes.data.data.universities || []
    }
    if (deptsRes.data.status === 'success') {
      departments.value = deptsRes.data.data.departments || []
    }
    if (coursesRes.data.status === 'success') {
      courses.value = coursesRes.data.data.courses || []
    }
  } catch (err) {
    console.error('Error fetching catalog data:', err)
  }
}

const openUploadModal = () => {
  activeActionMenuId.value = null
  uploadError.value = ''
  selectedFile.value = null
  
  const defaultCampus = campuses.value.length > 0 ? campuses.value[0].id : ''
  const defaultDeptList = departments.value.filter(d => d.university_id === defaultCampus)
  const defaultDept = defaultDeptList.length > 0 ? defaultDeptList[0].id : ''
  const defaultCourse = courses.value.length > 0 ? courses.value[0].id : ''

  uploadForm.value = {
    title: '',
    description: '',
    campus_id: defaultCampus,
    department_id: defaultDept,
    study_year: 2,
    semester: '1',
    course_id: defaultCourse,
    is_published: true,
    auto_generate: true,
    quiz_question_count: 20,
  }

  showUploadModal.value = true
}

const closeUploadModal = () => {
  showUploadModal.value = false
  uploadError.value = ''
  selectedFile.value = null
}

const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    validateAndAssignFile(file)
  }
}

const handleFileDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files?.[0]
  if (file) {
    validateAndAssignFile(file)
  }
}

const validateAndAssignFile = (file) => {
  const ext = file.name.split('.').pop().toLowerCase()
  if (!['pdf', 'ppt', 'pptx'].includes(ext)) {
    uploadError.value = 'Only PDF and PowerPoint (.ppt, .pptx) files are supported.'
    return
  }
  if (file.size > 10485760) {
    uploadError.value = 'File size exceeds the 10MB limit.'
    return
  }
  selectedFile.value = file
  uploadError.value = ''

  // If title is empty, prefill from filename
  if (!uploadForm.value.title) {
    const baseName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name
    uploadForm.value.title = baseName.replace(/[-_]/g, ' ')
  }
}

const clearSelectedFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const submitUpload = async () => {
  if (!selectedFile.value) {
    uploadError.value = 'Please select a PDF or PowerPoint file to upload.'
    return
  }
  if (!uploadForm.value.title.trim()) {
    uploadError.value = 'Please provide a document title.'
    return
  }
  if (!uploadForm.value.course_id) {
    uploadError.value = 'Please select a target course.'
    return
  }
  if (!uploadForm.value.department_id) {
    uploadError.value = 'Please select a target department.'
    return
  }

  isUploading.value = true
  uploadError.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('title', uploadForm.value.title.trim())
    if (uploadForm.value.description) {
      formData.append('description', uploadForm.value.description.trim())
    }
    formData.append('course_id', uploadForm.value.course_id)
    formData.append('department_id', uploadForm.value.department_id)
    formData.append('study_year', uploadForm.value.study_year)
    formData.append('semester', uploadForm.value.semester)
    formData.append('status', uploadForm.value.is_published ? 'published' : 'draft')
    if (uploadForm.value.auto_generate) {
      formData.append('auto_generate', '1')
      formData.append('quiz_question_count', String(uploadForm.value.quiz_question_count))
    }

    await api.post('/admin/materials', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    notify.success(`Material "${uploadForm.value.title}" uploaded successfully to Cloudflare!`)
    closeUploadModal()
    await fetchMaterials()
  } catch (err) {
    console.error('Failed to upload material:', err)
    uploadError.value = err.response?.data?.message || err.response?.data?.errors?.file?.[0] || 'Upload failed. Please check file format and storage connection.'
  } finally {
    isUploading.value = false
  }
}

const downloadMaterial = async (material) => {
  activeActionMenuId.value = null
  try {
    notify.info(`Starting download: "${material.title}"`)
    const response = await api.get(`/admin/materials/${material.id}/download`, {
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: response.headers['content-type'] })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', material.original_filename || `${material.title}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    notify.success(`Downloaded "${material.title}" successfully.`)
  } catch (err) {
    console.error('Failed to download material:', err)
    notify.error('Failed to download file from storage.')
  }
}

const togglePublish = async (material) => {
  activeActionMenuId.value = null
  const isCurrentlyPublished = material.status === 'published'
  const endpoint = isCurrentlyPublished 
    ? `/admin/materials/${material.id}/unpublish`
    : `/admin/materials/${material.id}/publish`

  try {
    const response = await api.post(endpoint)
    if (response.data.status === 'success') {
      notify.success(isCurrentlyPublished ? 'Material set to draft.' : 'Material published for students.')
      await fetchMaterials()
    }
  } catch (err) {
    console.error('Failed to toggle publish status:', err)
    notify.error('Failed to update status.')
  }
}

const deleteMaterial = async (material) => {
  activeActionMenuId.value = null
  const confirmed = await notify.confirm({
    title: `Delete Material "${material.title}"?`,
    message: 'Are you sure you want to delete this course material from Cloudflare storage? Any linked student bookmarks and tasks will be cleaned up.',
    confirmText: 'Delete Material',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await api.delete(`/admin/materials/${material.id}`)
    if (response.data.status === 'success') {
      notify.success('Course material deleted successfully.')
      await fetchMaterials()
    } else {
      notify.error(response.data.message || 'Failed to delete material')
    }
  } catch (err) {
    console.error('Error deleting material:', err)
    notify.error(err.response?.data?.message || 'Failed to delete material')
  }
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'published':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    case 'draft':
      return 'bg-amber-50 text-amber-700 border border-amber-200'
    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(1) + ' MB'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  await Promise.all([fetchMaterials(), fetchCatalogData()])
})
</script>
