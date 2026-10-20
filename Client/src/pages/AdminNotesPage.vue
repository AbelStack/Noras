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
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">AI Study Notes</h1>
            <p class="text-sm text-gray-600 mt-1">
              Browse, inspect, regenerate and manage official master study notes organized by curriculum
            </p>
          </div>
          <button 
            @click="$router.push('/admin/materials')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Course Materials</span>
          </button>
        </div>
      </div>

      <!-- Stats Cards (Compact) -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs">
          <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Notes</div>
          <div class="text-xl font-bold text-gray-900 mt-1">{{ stats.total_notes }}</div>
          <p class="text-[11px] text-gray-400 mt-0.5">Master study notes</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs">
          <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Materials Covered</div>
          <div class="text-xl font-bold text-emerald-600 mt-1">{{ stats.unique_materials }}</div>
          <p class="text-[11px] text-gray-400 mt-0.5">Files with generated notes</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs">
          <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Pending Materials</div>
          <div class="text-xl font-bold text-amber-600 mt-1">{{ stats.pending_materials ?? 0 }}</div>
          <p class="text-[11px] text-gray-400 mt-0.5">Awaiting note generation</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs">
          <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Departments Covered</div>
          <div class="text-xl font-bold text-purple-600 mt-1">{{ stats.departments_covered ?? 0 }}</div>
          <p class="text-[11px] text-gray-400 mt-0.5">Active academic departments</p>
        </div>
      </div>

      <!-- Filters & Search Bar (Compact) -->
      <div class="bg-white p-2.5 sm:px-3.5 rounded-xl border border-gray-200 shadow-2xs">
        <div class="flex flex-col md:flex-row gap-2.5 items-center justify-between">
          <div class="relative w-full md:w-72">
            <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="filters.search"
              @input="debounceFetch"
              type="text" 
              placeholder="Search by material title, course code..."
              class="w-full pl-9 pr-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition-all"
            />
          </div>

          <div class="w-full md:w-auto flex flex-wrap items-center gap-2">
            <!-- Campus Filter -->
            <select 
              v-model="filters.campusId"
              @change="onFilterCampusChange"
              class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Campuses</option>
              <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>

            <!-- Department Filter -->
            <select 
              v-model="filters.departmentId"
              @change="fetchNotes"
              class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700 max-w-[160px]"
            >
              <option value="">All Departments</option>
              <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>

            <!-- Study Year Filter -->
            <select 
              v-model="filters.studyYear"
              @change="fetchNotes"
              class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Years</option>
              <option v-for="yr in [1, 2, 3, 4, 5, 6, 7]" :key="yr" :value="yr">Year {{ yr }}</option>
            </select>

            <!-- Semester Filter -->
            <select 
              v-model="filters.semester"
              @change="fetchNotes"
              class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none bg-white transition-all text-gray-700"
            >
              <option value="">All Semesters</option>
              <option value="1">Semester 1</option>
              <option value="2">Semester 2</option>
            </select>

            <button 
              v-if="hasActiveFilters"
              @click="resetFilters"
              class="px-2 py-1 text-xs font-semibold text-gray-500 hover:text-gray-800 underline cursor-pointer"
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
        <button @click="fetchNotes" class="text-sm text-red-700 hover:text-red-800 underline font-medium cursor-pointer">
          Try again
        </button>
      </div>

      <!-- Notes Content -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="notes.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm min-h-[400px] flex flex-col items-center justify-center">
          <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-1">No AI study notes found</h3>
          <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
            {{ hasActiveFilters ? 'No study notes match your active filter filters.' : 'Generate study notes for course materials to see them listed here.' }}
          </p>
          <button 
            @click="$router.push('/admin/materials')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition-colors shadow-sm cursor-pointer"
          >
            Go to Course Materials
          </button>
        </div>

        <!-- Table of Notes (Large Box) -->
        <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden min-h-[500px] flex flex-col justify-between">
          <div class="overflow-x-auto flex-1">
            <table class="w-full divide-y divide-gray-200 text-left">
              <thead class="bg-gray-50/75">
                <tr>
                  <th class="px-3.5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-tight">Course Material</th>
                  <th class="px-3 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-tight w-24">Curriculum</th>
                  <th class="px-3 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-tight w-28">AI Model</th>
                  <th class="px-3 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-tight w-24">Length</th>
                  <th class="px-3 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-tight w-24">Generated</th>
                  <th class="px-3 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-tight w-28">Read Note</th>
                  <th class="px-3.5 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-tight w-14">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="note in notes" :key="note.id" class="hover:bg-gray-50/80 transition-colors">
                  <!-- Course Material Document -->
                  <td class="px-3.5 py-2.5">
                    <div class="flex items-center gap-2.5">
                      <div 
                        class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 font-bold text-[10px] shadow-2xs"
                        :class="isPpt(note.course_material) ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-red-50 text-red-600 border border-red-200'"
                      >
                        <span v-if="isPpt(note.course_material)">PPT</span>
                        <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div class="min-w-0">
                        <button 
                          type="button" 
                          @click="viewNote(note)" 
                          class="text-xs font-bold text-gray-900 hover:text-emerald-700 transition-colors truncate max-w-[180px] sm:max-w-[260px] lg:max-w-[340px] block text-left cursor-pointer" 
                          :title="note.course_material?.title"
                        >
                          {{ note.course_material?.title || 'Course Material' }}
                        </button>
                        <div class="text-[11px] text-gray-400 truncate max-w-[160px] sm:max-w-[220px]" :title="note.course_material?.original_filename">
                          {{ note.course_material?.original_filename || 'Document file' }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Target Curriculum -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-center">
                    <button
                      type="button"
                      @click="viewCurriculumDetails(note)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg border border-primary-200 transition-all shadow-2xs cursor-pointer hover:shadow-xs active:scale-95"
                      title="View target curriculum details"
                    >
                      <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <span>
                        {{ note.course_material?.course_offering?.course?.code || 'Curriculum' }}
                      </span>
                    </button>
                  </td>

                  <!-- AI Model -->
                  <td class="px-3 py-2.5 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                      {{ formatAiModel(note.ai_model || note.ai_provider) }}
                    </span>
                  </td>

                  <!-- Length / Character Count -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-700 font-medium">
                    <div class="text-xs font-semibold">{{ (note.notes_char_count || note.content?.length || 0).toLocaleString() }} chars</div>
                    <div class="text-[10px] text-gray-400">~{{ Math.round((note.notes_char_count || note.content?.length || 0) / 5).toLocaleString() }} words</div>
                  </td>

                  <!-- Generated Date -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                    {{ formatDate(note.created_at) }}
                  </td>

                  <!-- Direct Read Note Button -->
                  <td class="px-3 py-2.5 whitespace-nowrap text-center">
                    <button
                      type="button"
                      @click="viewNote(note)"
                      class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition-all shadow-2xs cursor-pointer hover:shadow-xs active:scale-95"
                      title="Read study note"
                    >
                      <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                      <span>Read Note</span>
                    </button>
                  </td>

                  <!-- 3-Dots Action Menu -->
                  <td class="px-3.5 py-2.5 whitespace-nowrap text-right text-sm">
                    <div class="relative inline-block text-left" @click.stop>
                      <button 
                        type="button"
                        @click.stop="toggleActionMenu(note.id)" 
                        class="p-1 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/20 cursor-pointer"
                        title="More actions"
                      >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </button>

                      <!-- Dropdown Menu -->
                      <div 
                        v-if="activeActionMenuId === note.id"
                        @click.stop
                        class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-30 divide-y divide-gray-100 overflow-hidden"
                      >
                        <div class="py-1">
                          <button 
                            type="button"
                            @click="viewNote(note); activeActionMenuId = null" 
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>View Study Note</span>
                          </button>

                          <button 
                            type="button"
                            @click="regenerateNote(note); activeActionMenuId = null" 
                            :disabled="isRegeneratingId === note.id"
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50 transition-colors text-left cursor-pointer disabled:opacity-50"
                          >
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>{{ isRegeneratingId === note.id ? 'Regenerating...' : 'Regenerate Note' }}</span>
                          </button>
                        </div>

                        <div class="py-1">
                          <button 
                            type="button"
                            @click="deleteNote(note); activeActionMenuId = null" 
                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer"
                          >
                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Delete Note</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Dynamic Pagination -->
          <DynamicPagination
            v-if="pagination.total > 0"
            :currentPage="pagination.current_page"
            :totalPages="pagination.last_page"
            :totalItems="pagination.total"
            :perPage="pagination.per_page || 10"
            :showPerPage="false"
            itemLabel="notes"
            @update:currentPage="changePage"
          />
        </div>
      </div>
    </div>

    <!-- View Note Modal -->
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showNoteModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeNoteModal"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-gray-100" @click.stop>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-emerald-50/50">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                  ðŸ“
                </div>
                <div>
                  <h3 class="text-base font-bold text-gray-900">Official Master Study Note</h3>
                  <p class="text-xs text-gray-500 truncate max-w-md">
                    {{ activeNote?.course_material?.title }} &bull; {{ (activeNote?.notes_char_count || activeNote?.content?.length || 0).toLocaleString() }} characters
                  </p>
                </div>
              </div>
              <button @click="closeNoteModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="p-6 max-h-[65vh] overflow-y-auto text-gray-800 leading-relaxed font-sans whitespace-pre-wrap select-text text-sm bg-gray-50/40 rounded-xl m-4 border border-gray-150">
              <div v-if="loadingNoteContent" class="flex flex-col items-center justify-center py-12 text-gray-500">
                <svg class="animate-spin h-8 w-8 text-emerald-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm">Loading full study note content...</span>
              </div>
              <template v-else>
                {{ activeNote?.content }}
              </template>
            </div>
            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
              <button 
                type="button" 
                @click="copyNoteContent" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-200 transition-colors cursor-pointer"
              >
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Copy Note Text</span>
              </button>
              <button 
                type="button" 
                @click="closeNoteModal" 
                class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
              >
                Close
              </button>
            </div>
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
          <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100" @click.stop>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                  ðŸŽ¯
                </div>
                <div>
                  <h3 class="text-base font-bold text-gray-900 truncate max-w-xs sm:max-w-sm">
                    {{ activeNote?.course_material?.title }}
                  </h3>
                  <p class="text-xs text-gray-500">Target Curriculum Information</p>
                </div>
              </div>
              <button @click="closeCurriculumModal" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="p-6 space-y-4">
              <div class="bg-gray-50/75 rounded-xl border border-gray-200/80 p-4 space-y-3">
                <!-- Course -->
                <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Course</span>
                  <div class="text-right">
                    <div class="text-xs font-bold text-gray-900">
                      {{ activeNote?.course_material?.course_offering?.course?.code || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-600">
                      {{ activeNote?.course_material?.course_offering?.course?.title || activeNote?.course_material?.course_offering?.course?.name || 'General' }}
                    </div>
                  </div>
                </div>

                <!-- Campus -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Campus</span>
                  <span class="text-xs font-bold text-gray-900">
                    {{ activeNote?.course_material?.course_offering?.department?.university?.name || 'All Campuses' }}
                  </span>
                </div>

                <!-- Department -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Department</span>
                  <span class="text-xs font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md border border-primary-100">
                    {{ activeNote?.course_material?.course_offering?.department?.name || 'General' }}
                  </span>
                </div>

                <!-- Cohort Level (Year & Semester) -->
                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                  <span class="text-xs font-semibold text-gray-500">Cohort Level</span>
                  <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                      Year {{ activeNote?.course_material?.course_offering?.study_year || 1 }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                      Semester {{ activeNote?.course_material?.course_offering?.semester || 1 }}
                    </span>
                  </div>
                </div>

                <!-- Academic Year -->
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-semibold text-gray-500">Academic Year</span>
                  <span class="text-xs font-mono font-medium text-gray-700">
                    AY {{ activeNote?.course_material?.course_offering?.academic_year || 2026 }}
                  </span>
                </div>
              </div>
            </div>

            <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
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
import DynamicPagination from '../components/DynamicPagination.vue'
import api from '../services/api'
import { useNotification } from '../composables/useNotification'
import { adminNavigationItems } from '../config/navigation'

const notify = useNotification()
const navigationItems = adminNavigationItems

const notes = ref([])
const campuses = ref([])
const departments = ref([])
const loading = ref(true)
const error = ref(null)

const stats = ref({
  total_notes: 0,
  unique_materials: 0,
  pending_materials: 0,
  departments_covered: 0,
})

const isPpt = (material) => {
  if (!material) return false
  const filename = material.original_filename || material.title || ''
  const fileType = material.file_type || ''
  return fileType === 'ppt' || /\.pptx?$/i.test(filename)
}

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const filters = ref({
  search: '',
  campusId: '',
  departmentId: '',
  studyYear: '',
  semester: '',
})

const activeActionMenuId = ref(null)
const isRegeneratingId = ref(null)
const showNoteModal = ref(false)
const showCurriculumModal = ref(false)
const activeNote = ref(null)
const loadingNoteContent = ref(false)

let debounceTimer = null
const debounceFetch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    fetchNotes(1)
  }, 300)
}

const hasActiveFilters = computed(() => {
  return Boolean(
    filters.value.search ||
    filters.value.campusId ||
    filters.value.departmentId ||
    filters.value.studyYear ||
    filters.value.semester
  )
})

const filteredDepartments = computed(() => {
  if (!filters.value.campusId) return departments.value
  return departments.value.filter(d => String(d.university_id) === String(filters.value.campusId))
})

const onFilterCampusChange = () => {
  filters.value.departmentId = ''
  fetchNotes(1)
}

const resetFilters = () => {
  filters.value = {
    search: '',
    campusId: '',
    departmentId: '',
    studyYear: '',
    semester: '',
  }
  fetchNotes(1)
}

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

const toggleActionMenu = (id) => {
  activeActionMenuId.value = activeActionMenuId.value === id ? null : id
}

const fetchNotes = async (page = 1) => {
  loading.value = true
  error.value = null
  try {
    const params = {
      page,
      search: filters.value.search || undefined,
      campus_id: filters.value.campusId || undefined,
      department_id: filters.value.departmentId || undefined,
      study_year: filters.value.studyYear || undefined,
      semester: filters.value.semester || undefined,
    }

    const response = await api.get('/admin/notes', { params })
    if (response.data.status === 'success') {
      notes.value = response.data.data.notes || []
      pagination.value = response.data.data.pagination || pagination.value
      if (response.data.data.stats) {
        stats.value = response.data.data.stats
      }
    }
  } catch (err) {
    console.error('Failed to fetch notes:', err)
    error.value = err.response?.data?.message || 'Failed to load study notes'
  } finally {
    loading.value = false
  }
}

const fetchCampusesAndDepartments = async () => {
  try {
    const [campRes, deptRes] = await Promise.all([
      api.get('/admin/universities').catch(() => ({ data: { data: { universities: [] } } })),
      api.get('/admin/departments').catch(() => ({ data: { data: { departments: [] } } })),
    ])

    campuses.value = campRes.data?.data?.universities || campRes.data?.data || []
    departments.value = deptRes.data?.data?.departments || deptRes.data?.data || []
  } catch (err) {
    console.error('Failed to load filter metadata:', err)
  }
}

const viewNote = async (note) => {
  activeNote.value = note
  showNoteModal.value = true

  if (!note.content) {
    loadingNoteContent.value = true
    try {
      const res = await api.get(`/admin/notes/${note.id}`)
      if (res.data.status === 'success' && res.data.data.note) {
        activeNote.value = res.data.data.note
        note.content = res.data.data.note.content
      }
    } catch (err) {
      console.error('Failed to load note content:', err)
      notify.error('Failed to load study note text')
    } finally {
      loadingNoteContent.value = false
    }
  }
}

const closeNoteModal = () => {
  showNoteModal.value = false
}

const viewCurriculumDetails = (note) => {
  activeNote.value = note
  showCurriculumModal.value = true
}

const closeCurriculumModal = () => {
  showCurriculumModal.value = false
}

const copyNoteContent = async () => {
  if (!activeNote.value?.content) return
  try {
    await navigator.clipboard.writeText(activeNote.value.content)
    notify.success('Study note text copied to clipboard!')
  } catch {
    notify.error('Failed to copy note text')
  }
}

const regenerateNote = async (note) => {
  const confirmed = await notify.confirm({
    title: 'Regenerate Study Note?',
    message: `Regenerating will read "${note.course_material?.title}" page-by-page from start to finish to replace the current note for all students. Continue?`,
    confirmText: 'Regenerate',
    type: 'primary',
  })
  if (!confirmed) return

  isRegeneratingId.value = note.id
  notify.info(`Regenerating note for "${note.course_material?.title}"...`)

  try {
    const res = await api.post(`/admin/notes/generate/${note.course_material_id}`, { regenerate: true })
    if (res.data.status === 'success') {
      notify.success('Study note regenerated successfully!')
      await fetchNotes(pagination.value.current_page)
    }
  } catch (err) {
    notify.error(err.response?.data?.message || 'Failed to regenerate study note')
  } finally {
    isRegeneratingId.value = null
  }
}

const deleteNote = async (note) => {
  const confirmed = await notify.confirm({
    title: 'Delete Study Note?',
    message: `Are you sure you want to delete the study note for "${note.course_material?.title}"? Students will no longer see this note until regenerated.`,
    confirmText: 'Delete Note',
    type: 'danger',
  })
  if (!confirmed) return

  try {
    const res = await api.delete(`/admin/notes/${note.id}`)
    if (res.data.status === 'success') {
      notify.success('Study note deleted.')
      await fetchNotes(pagination.value.current_page)
    }
  } catch (err) {
    notify.error(err.response?.data?.message || 'Failed to delete note')
  }
}

const changePage = (newPage) => {
  fetchNotes(newPage)
}

const formatCharCount = (count) => {
  if (!count) return '0'
  if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M'
  if (count >= 1000) return (count / 1000).toFixed(1) + 'k'
  return count.toString()
}

const formatAiModel = (model) => {
  if (!model) return 'AI Engine'
  if (model.includes('gemini')) return 'Gemini 1.5'
  if (model.includes('groq') || model.includes('llama')) return 'Groq Llama 3'
  return model
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

onMounted(() => {
  fetchNotes(1)
  fetchCampusesAndDepartments()
})
</script>
