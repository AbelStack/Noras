<template>
  <DashboardLayout 
    pageTitle="Student" 
    :navigationItems="studentNavigationItems"
  >
    <div class="p-5 sm:p-6 lg:p-7 max-w-4xl mx-auto space-y-4 sm:space-y-4.5">
      <!-- Back Button -->
      <div>
        <button
          @click="$router.push('/quizzes')"
          class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-primary-600 transition-colors mb-1.5 group cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Quizzes
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading">
        <SkeletonLoader type="quiz" />
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-sm text-red-700 font-medium">{{ errorMessage }}</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="loadQuiz"
            class="text-sm text-red-700 hover:text-red-800 underline font-medium cursor-pointer"
          >
            Try Again
          </button>
          <button
            @click="$router.push('/quizzes')"
            class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-xs font-medium cursor-pointer"
          >
            Back to Library
          </button>
        </div>
      </div>

      <!-- Quiz Content Area -->
      <div v-else-if="quiz && questions.length > 0" class="space-y-4 sm:space-y-5">
        <!-- Quiz Header Card (White Card with Brand Blue and Yellow Details) -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600 border border-primary-100 flex-shrink-0">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                  </svg>
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                  {{ material?.title || 'Course Quiz' }}
                </h1>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-gray-500 ml-10 mb-2">
                <span v-if="material?.course" class="font-medium text-gray-800">
                  {{ material.course.code }} &bull; {{ material.course.name }}
                </span>
                <span v-if="material?.study_year" class="text-gray-300">•</span>
                <span v-if="material?.study_year" class="font-semibold text-primary-700">
                  Year {{ material.study_year }}
                </span>
                <span v-if="material?.semester" class="text-gray-300">•</span>
                <span v-if="material?.semester" class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                  Semester {{ material.semester }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-3 text-xs text-gray-400 ml-10">
                <span class="font-semibold text-primary-600">{{ questions.length }} Questions</span>
                <span>•</span>
                <span>Generated {{ formatDate(quiz.created_at) }}</span>
                <!-- Admin Only AI technical details -->
                <template v-if="isAdmin">
                  <span v-if="quiz.ai_provider">•</span>
                  <span v-if="quiz.ai_provider" class="text-amber-700 font-mono">{{ quiz.ai_provider }} ({{ quiz.ai_model }})</span>
                  <span v-if="quiz.generation_time_ms">•</span>
                  <span v-if="quiz.generation_time_ms" class="text-amber-700 font-mono">{{ quiz.generation_time_ms }}ms</span>
                </template>
              </div>

              <!-- Past Attempt Badge if available -->
              <div 
                v-if="!isSubmitted && latestAttempt" 
                class="mt-4 p-3.5 bg-primary-50/70 border border-primary-200 rounded-xl ml-10 flex items-center justify-between gap-3 text-xs"
              >
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-primary-950 font-medium">
                    Previous attempt: <strong class="text-primary-700 font-bold">{{ latestAttempt.score }}/{{ latestAttempt.total_questions }}</strong> ({{ latestAttempt.percentage }}%)
                  </span>
                </div>
                <span class="text-primary-600 font-medium">{{ formatDate(latestAttempt.created_at) }}</span>
              </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2 self-start sm:self-center ml-10 sm:ml-0 flex-shrink-0">
              <button
                v-if="isAdmin"
                @click="confirmDelete"
                :disabled="isDeleting"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-red-200 text-red-600 rounded-xl hover:bg-red-50 text-xs sm:text-sm font-medium transition-colors disabled:opacity-50 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>{{ isDeleting ? 'Deleting...' : 'Delete' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- SCREEN 1: TAKING THE QUIZ (ACTIVE MODE)   -->
        <!-- ========================================== -->
        <template v-if="!isSubmitted">
          <!-- Question Navigation Numbers Row -->
          <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3 text-xs font-semibold text-gray-500">
              <span>Jump to Question</span>
              <span class="text-primary-700 font-bold">{{ answeredCount }} of {{ questions.length }} Answered</span>
            </div>
            <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-thin">
              <button
                v-for="(q, index) in questions"
                :key="q.id"
                @click="goToQuestion(index)"
                :class="[
                  'w-9 h-9 rounded-xl text-xs font-bold transition-all flex-shrink-0 flex items-center justify-center cursor-pointer',
                  index === currentQuestionIndex
                    ? 'bg-primary-600 text-white shadow-sm ring-2 ring-primary-200'
                    : userAnswers[q.id]
                    ? 'bg-primary-50 text-primary-700 border border-primary-200 font-semibold'
                    : 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'
                ]"
              >
                {{ index + 1 }}
              </button>
            </div>
          </div>

          <!-- Active Question Card -->
          <div v-if="currentQuestion" class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold uppercase tracking-wider text-primary-700 bg-primary-50 px-2.5 py-1 rounded-md border border-primary-100">
                Question {{ currentQuestionIndex + 1 }}
              </span>
              <span v-if="userAnswers[currentQuestion.id]" class="text-xs font-semibold text-primary-600 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Answered
              </span>
              <span v-else class="text-xs font-medium text-gray-400">Not answered yet</span>
            </div>

            <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-6 leading-relaxed">
              {{ currentQuestion.question_text }}
            </h3>

            <!-- Options (White cards with Blue highlight on selection) -->
            <div class="space-y-3">
              <button
                v-for="(option, optIndex) in currentQuestion.options"
                :key="option.id"
                @click="selectAnswer(option.id)"
                :class="[
                  'w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer',
                  userAnswers[currentQuestion.id] === option.id
                    ? 'border-primary-600 bg-primary-50/40 shadow-2xs'
                    : 'border-gray-200 bg-white hover:border-primary-300 hover:bg-primary-50/20'
                ]"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-0.5">
                    <div :class="[
                      'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors',
                      userAnswers[currentQuestion.id] === option.id
                        ? 'border-primary-600 bg-primary-600'
                        : 'border-gray-300 bg-white'
                    ]">
                      <div 
                        v-if="userAnswers[currentQuestion.id] === option.id" 
                        class="w-2 h-2 rounded-full bg-white"
                      ></div>
                    </div>
                  </div>
                  <div class="ml-3 flex-1">
                    <p :class="[
                      'text-sm sm:text-base font-medium leading-snug',
                      userAnswers[currentQuestion.id] === option.id ? 'text-primary-950 font-bold' : 'text-gray-800'
                    ]">
                      {{ formatOptionText(option.option_text, optIndex) }}
                    </p>
                  </div>
                </div>
              </button>
            </div>

            <!-- BOTTOM NAVIGATION BAR (Previous on left bottom, counter centered in middle, Next/Submit on right bottom) -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
              <!-- Left Bottom: Previous Button -->
              <button
                @click="previousQuestion"
                :disabled="currentQuestionIndex === 0"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed font-medium text-xs sm:text-sm text-gray-700 transition-colors cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
              </button>

              <!-- Middle: Centered Counter -->
              <div class="text-xs sm:text-sm text-gray-500 font-medium text-center">
                Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
                <span class="text-gray-300 mx-1.5">•</span>
                <span :class="answeredCount === questions.length ? 'text-primary-700 font-bold' : 'text-gray-600'">
                  {{ answeredCount }} of {{ questions.length }} Answered
                </span>
              </div>

              <!-- Right Bottom: Next or Submit Button -->
              <div class="w-full sm:w-auto flex items-center justify-end gap-2">
                <button
                  v-if="currentQuestionIndex < questions.length - 1"
                  @click="nextQuestion"
                  class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-semibold text-xs sm:text-sm transition-colors shadow-xs cursor-pointer"
                >
                  <span>Next</span>
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>

                <button
                  v-else
                  @click="handleFinalSubmitClick"
                  :disabled="isSubmitting"
                  class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-accent-500 hover:bg-accent-600 text-gray-900 font-bold text-xs sm:text-sm transition-colors shadow-sm cursor-pointer disabled:opacity-50"
                >
                  <svg v-if="isSubmitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  <span>{{ isSubmitting ? 'Grading Quiz...' : 'Submit Quiz' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Submission Action Bar when All Questions Answered (even if on earlier question) -->
          <div 
            v-if="answeredCount === questions.length && currentQuestionIndex < questions.length - 1"
            class="bg-primary-50 border border-primary-200 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs"
          >
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <p class="text-xs sm:text-sm font-semibold text-primary-950">
                All {{ questions.length }} questions answered! Ready to see your score?
              </p>
            </div>
            <button
              @click="handleFinalSubmitClick"
              :disabled="isSubmitting"
              class="px-5 py-2 bg-accent-500 hover:bg-accent-600 text-gray-900 font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer self-stretch sm:self-auto disabled:opacity-50"
            >
              {{ isSubmitting ? 'Submitting...' : 'Submit Quiz Now' }}
            </button>
          </div>
        </template>

        <!-- ========================================== -->
        <!-- SCREEN 2: POST-SUBMISSION RESULTS REVIEW   -->
        <!-- Brand Theme: Royal Blue, Yellow Accent, White -->
        <!-- ========================================== -->
        <template v-else-if="isSubmitted && submissionResult">
          <!-- Hero Score Banner (Solid Brand Blue for the whole container, no white fade) -->
          <div class="bg-primary-600 text-white rounded-2xl p-6 sm:p-8 shadow-md">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
              <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-700/70 text-white text-xs font-semibold border border-primary-500/40">
                  <span class="w-2 h-2 rounded-full bg-accent-500"></span>
                  <span>Quiz Completed</span>
                  <span>&bull;</span>
                  <span>{{ formatDate(submissionResult.created_at) }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                  Your Score: <span class="text-accent-500 font-black">{{ submissionResult.score }} / {{ submissionResult.total_questions }}</span>
                  <span class="text-blue-100 font-normal"> ({{ submissionResult.percentage }}%)</span>
                </h2>
                <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                  <template v-if="submissionResult.percentage >= 80">
                    🎉 Outstanding performance! You have mastered this course material.
                  </template>
                  <template v-else-if="submissionResult.percentage >= 50">
                    👍 Good effort! Review the questions you missed below along with explanations to reinforce your learning.
                  </template>
                  <template v-else>
                    📚 Keep practicing! Thoroughly read the explanations below and study the notes before trying again.
                  </template>
                </p>
              </div>

              <!-- Action buttons (High contrast Retake Quiz in yellow, Back to Library in solid white) -->
              <div class="flex flex-wrap sm:flex-col gap-2.5 self-start md:self-center flex-shrink-0">
                <button
                  @click="retakeQuiz"
                  class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-gray-900 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-colors cursor-pointer text-center"
                >
                  Retake Quiz
                </button>
                <router-link
                  to="/quizzes"
                  class="px-5 py-2.5 bg-white text-primary-700 hover:bg-blue-50 rounded-xl font-bold text-xs sm:text-sm shadow-sm text-center transition-colors cursor-pointer"
                >
                  Back to Library
                </router-link>
              </div>
            </div>

            <!-- Score Stats Tiles (All 4 tiles have identical, solid contrast) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-primary-500/50">
              <div class="bg-primary-700/80 rounded-xl p-3.5 text-center border border-primary-500/50 shadow-2xs">
                <p class="text-xs text-blue-200 font-semibold">Total Questions</p>
                <p class="text-xl sm:text-2xl font-black text-white mt-1">{{ submissionResult.total_questions }}</p>
              </div>
              <div class="bg-primary-700/80 rounded-xl p-3.5 text-center border border-primary-500/50 shadow-2xs">
                <p class="text-xs text-blue-200 font-semibold">Correct Answers</p>
                <p class="text-xl sm:text-2xl font-black text-accent-500 mt-1">{{ submissionResult.score }}</p>
              </div>
              <div class="bg-primary-700/80 rounded-xl p-3.5 text-center border border-primary-500/50 shadow-2xs">
                <p class="text-xs text-blue-200 font-semibold">Missed Questions</p>
                <p class="text-xl sm:text-2xl font-black text-red-300 mt-1">{{ missedCount }}</p>
              </div>
              <div class="bg-primary-700/80 rounded-xl p-3.5 text-center border border-primary-500/50 shadow-2xs">
                <p class="text-xs text-blue-200 font-semibold">Final Percentage</p>
                <p class="text-xl sm:text-2xl font-black text-accent-500 mt-1">{{ submissionResult.percentage }}%</p>
              </div>
            </div>
          </div>

          <!-- Review Filter Navigation -->
          <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 bg-gray-100 p-1 rounded-xl">
              <button
                @click="reviewFilter = 'all'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                  reviewFilter === 'all'
                    ? 'bg-primary-600 text-white shadow-xs'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                All ({{ submissionResult.question_results?.length || 0 }})
              </button>
              <button
                @click="reviewFilter = 'missed'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                  reviewFilter === 'missed'
                    ? 'bg-red-600 text-white shadow-xs'
                    : 'text-red-700 hover:bg-red-50'
                ]"
              >
                <span>Missed</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="reviewFilter === 'missed' ? 'bg-white/20' : 'bg-red-100'">
                  {{ missedCount }}
                </span>
              </button>
              <button
                @click="reviewFilter = 'correct'"
                :class="[
                  'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                  reviewFilter === 'correct'
                    ? 'bg-accent-500 text-gray-900 shadow-xs'
                    : 'text-primary-700 hover:bg-primary-50'
                ]"
              >
                <span>Correct</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="reviewFilter === 'correct' ? 'bg-black/20' : 'bg-primary-100'">
                  {{ submissionResult.score }}
                </span>
              </button>
            </div>

            <div class="text-xs text-gray-500 font-medium">
              Review answers &amp; explanations below
            </div>
          </div>

          <!-- Detailed Question by Question Review List -->
          <div class="space-y-6">
            <div
              v-for="(qr, qIdx) in filteredReviewQuestions"
              :key="qr.question_id"
              class="bg-white rounded-2xl border p-6 sm:p-8 shadow-sm transition-all"
              :class="qr.is_correct ? 'border-gray-200' : 'border-red-200 ring-1 ring-red-100'"
            >
              <!-- Question Header Status -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700">
                    Question {{ qr.question_order || (qIdx + 1) }}
                  </span>
                  <span 
                    v-if="qr.is_correct"
                    class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-md bg-blue-50 text-primary-700 border border-blue-200"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Correct
                  </span>
                  <span 
                    v-else
                    class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-md bg-red-100 text-red-800 border border-red-200"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Missed
                  </span>
                </div>

                <span v-if="!qr.selected_option_id" class="text-xs font-semibold text-gray-400">
                  Unanswered
                </span>
              </div>

              <!-- Question text -->
              <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-5 leading-relaxed">
                {{ qr.question_text }}
              </h3>

              <!-- Options with Graded Highlights -->
              <div class="space-y-2.5">
                <div
                  v-for="(option, optIdx) in qr.options"
                  :key="option.id"
                  :class="[
                    'w-full text-left p-3.5 rounded-xl border-2 transition-all flex items-start gap-3',
                    // Option is both selected and correct
                    qr.selected_option_id === option.id && option.is_correct
                      ? 'border-primary-500 bg-primary-50/70 text-primary-950 font-semibold'
                      // Option is selected but INCORRECT
                      : qr.selected_option_id === option.id && !option.is_correct
                      ? 'border-red-500 bg-red-50/70 text-red-950 font-semibold'
                      // Option is the correct answer (missed by student)
                      : option.is_correct
                      ? 'border-amber-400 bg-amber-50/60 text-gray-900 font-semibold'
                      // Neutral unselected incorrect option
                      : 'border-gray-200 bg-gray-50/50 text-gray-600'
                  ]"
                >
                  <!-- Radio / Icon indicator -->
                  <div class="flex-shrink-0 mt-0.5">
                    <div 
                      :class="[
                        'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                        qr.selected_option_id === option.id && option.is_correct
                          ? 'border-primary-600 bg-primary-600 text-white'
                          : qr.selected_option_id === option.id && !option.is_correct
                          ? 'border-red-600 bg-red-600 text-white'
                          : option.is_correct
                          ? 'border-amber-500 bg-accent-500 text-gray-900'
                          : 'border-gray-300 bg-white'
                      ]"
                    >
                      <svg v-if="qr.selected_option_id === option.id && option.is_correct" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                      <svg v-else-if="qr.selected_option_id === option.id && !option.is_correct" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      <svg v-else-if="option.is_correct" class="w-3 h-3 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                  </div>

                  <div class="flex-1 min-w-0">
                    <p class="text-sm">
                      {{ formatOptionText(option.option_text, optIdx) }}
                    </p>
                    
                    <!-- Labels -->
                    <div v-if="qr.selected_option_id === option.id && option.is_correct" class="mt-1 text-xs text-primary-700 font-bold flex items-center gap-1">
                      <span>✓ Your Answer (Correct)</span>
                    </div>
                    <div v-else-if="qr.selected_option_id === option.id && !option.is_correct" class="mt-1 text-xs text-red-700 font-bold flex items-center gap-1">
                      <span>✗ Your Answer (Incorrect)</span>
                    </div>
                    <div v-else-if="option.is_correct" class="mt-1 text-xs text-amber-800 font-bold flex items-center gap-1">
                      <span>✓ Correct Answer</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Explanation & Source Citation Box -->
              <div v-if="qr.explanation" class="mt-5 p-4 bg-primary-50/70 border border-primary-200 rounded-xl">
                <div class="flex items-start gap-2.5">
                  <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="space-y-1">
                    <p class="text-xs font-bold text-primary-950 uppercase tracking-wider">
                      Explanation &amp; Source Citation
                    </p>
                    <p class="text-xs sm:text-sm text-primary-900 leading-relaxed">
                      {{ qr.explanation }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Footer Retake / Navigation Bar -->
          <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div>
              <h4 class="text-sm font-bold text-gray-900">Finished reviewing your results?</h4>
              <p class="text-xs text-gray-500 mt-0.5">Your attempt has been saved to your learning progress dashboard.</p>
            </div>
            <div class="flex items-center gap-3">
              <button
                @click="retakeQuiz"
                class="px-5 py-2.5 bg-accent-500 hover:bg-accent-600 text-gray-900 rounded-xl font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer"
              >
                Retake Quiz
              </button>
              <router-link
                to="/quizzes"
                class="px-4 py-2.5 border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-medium text-xs sm:text-sm transition-colors"
              >
                Back to Library
              </router-link>
            </div>
          </div>
        </template>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DashboardLayout from '../components/DashboardLayout.vue'
import SkeletonLoader from '../components/SkeletonLoader.vue'
import { quizzesService } from '../services/quizzes'
import { authService } from '../services/auth'
import { studentNavigationItems } from '../config/navigation'
import { useNotification } from '../composables/useNotification'

const router = useRouter()
const route = useRoute()
const notify = useNotification()

const currentUser = authService.getUser()
const isAdmin = computed(() => authService.isAdmin())

const quiz = ref(null)
const material = ref(null)
const questions = ref([])
const latestAttempt = ref(null)
const currentQuestionIndex = ref(0)
const userAnswers = ref({}) // { questionId: selectedOptionId }

// Submission and review state
const isSubmitting = ref(false)
const isSubmitted = ref(false)
const submissionResult = ref(null)
const reviewFilter = ref('all') // 'all' | 'missed' | 'correct'

const isLoading = ref(true)
const isDeleting = ref(false)
const errorMessage = ref('')

const quizId = computed(() => parseInt(route.params.id))
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])

// Number of answered questions
const answeredCount = computed(() => {
  return Object.keys(userAnswers.value).length
})

// Number of missed questions in submission
const missedCount = computed(() => {
  if (!submissionResult.value) return 0
  return (submissionResult.value.total_questions || 0) - (submissionResult.value.score || 0)
})

// Filtered questions for post-submission review
const filteredReviewQuestions = computed(() => {
  if (!submissionResult.value?.question_results) return []
  const list = submissionResult.value.question_results
  if (reviewFilter.value === 'missed') {
    return list.filter(q => !q.is_correct)
  }
  if (reviewFilter.value === 'correct') {
    return list.filter(q => q.is_correct)
  }
  return list
})

onMounted(() => {
  loadQuiz()
})

const loadQuiz = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await quizzesService.getQuiz(quizId.value)
    quiz.value = data.quiz
    material.value = data.material
    latestAttempt.value = data.latest_attempt || null
    questions.value = (data.questions || []).sort((a, b) => a.question_order - b.question_order)
    
    // Sort options within each question
    questions.value.forEach(q => {
      q.options = (q.options || []).sort((a, b) => a.option_order - b.option_order)
    })
  } catch (error) {
    console.error('Failed to load quiz:', error)
    
    if (error.response?.status === 404) {
      errorMessage.value = 'Quiz not found. It may have been deleted.'
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        authService.logout()
        router.push('/login')
      }, 2000)
    } else if (error.response?.status === 403) {
      errorMessage.value = 'You do not have permission to view this quiz.'
    } else {
      errorMessage.value = error.response?.data?.message || 'Failed to load quiz. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

const selectAnswer = (optionId) => {
  if (!currentQuestion.value) return
  userAnswers.value[currentQuestion.value.id] = optionId
}

const handleFinalSubmitClick = async () => {
  const total = questions.value.length
  const answered = answeredCount.value

  if (answered < total) {
    const confirmed = await notify.confirm({
      title: 'Submit Incomplete Quiz?',
      message: `You have answered ${answered} of ${total} questions. Unanswered questions will be marked incorrect. Do you want to submit now?`,
      confirmText: 'Submit Quiz',
      cancelText: 'Keep Answering',
      type: 'warning',
    })
    if (!confirmed) return
  }

  await submitQuiz()
}

const submitQuiz = async () => {
  isSubmitting.value = true

  try {
    const response = await quizzesService.submitQuiz(quizId.value, userAnswers.value)
    submissionResult.value = response
    isSubmitted.value = true
    reviewFilter.value = 'all'

    // Scroll to top to see score banner
    window.scrollTo({ top: 0, behavior: 'smooth' })
    notify.success('Quiz graded and saved to your learning progress!')
  } catch (error) {
    console.error('Failed to submit quiz:', error)
    notify.error(error.response?.data?.message || 'Failed to submit quiz. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

const retakeQuiz = () => {
  userAnswers.value = {}
  currentQuestionIndex.value = 0
  isSubmitted.value = false
  submissionResult.value = null
  reviewFilter.value = 'all'
  window.scrollTo({ top: 0, behavior: 'smooth' })
  notify.info('Quiz reset. Good luck!')
}

const formatOptionText = (optionText, index) => {
  if (!optionText) return ''
  const trimmed = optionText.trim()
  if (/^[A-D]\.\s*/i.test(trimmed)) {
    return trimmed
  }
  const prefix = String.fromCharCode(65 + index)
  return `${prefix}. ${trimmed}`
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

const goToQuestion = (index) => {
  if (index >= 0 && index < questions.value.length) {
    currentQuestionIndex.value = index
  }
}

const confirmDelete = async () => {
  const confirmed = await notify.confirm({
    title: 'Delete Quiz?',
    message: 'Are you sure you want to delete this quiz? This action cannot be undone.',
    confirmText: 'Delete Quiz',
    cancelText: 'Cancel',
    type: 'danger',
  })

  if (!confirmed) return
  await handleDelete()
}

const handleDelete = async () => {
  isDeleting.value = true

  try {
    await quizzesService.deleteQuiz(quizId.value)
    notify.success('Quiz deleted successfully.')
    router.push('/quizzes')
  } catch (error) {
    console.error('Failed to delete quiz:', error)
    
    if (error.response?.status === 403) {
      notify.error('You do not have permission to delete this quiz.')
    } else if (error.response?.status === 404) {
      notify.error('Quiz not found. It may have been deleted already.')
      setTimeout(() => router.push('/quizzes'), 2000)
    } else {
      notify.error(error.response?.data?.message || 'Failed to delete quiz. Please try again.')
    }
  } finally {
    isDeleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}
</script>
