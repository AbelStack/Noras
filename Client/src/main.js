import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './style.css'
import App from './App.vue'
import { authService } from './services/auth'

// Public pages
import LandingPage from './pages/LandingPage.vue'
import LoginPage from './pages/LoginPage.vue'
import RegisterPage from './pages/RegisterPage.vue'
import ForgotPasswordPage from './pages/ForgotPasswordPage.vue'
import ResetPasswordPage from './pages/ResetPasswordPage.vue'
import ContactPage from './pages/legal/ContactPage.vue'
import PrivacyPolicyPage from './pages/legal/PrivacyPolicyPage.vue'
import TermsOfUsePage from './pages/legal/TermsOfUsePage.vue'

// Protected pages
import StudentDashboard from './pages/StudentDashboard.vue'
import AdminDashboard from './pages/AdminDashboard.vue'
import AdminStudentApprovalsPage from './pages/AdminStudentApprovalsPage.vue'
import AdminCampusPage from './pages/AdminCampusPage.vue'
import AdminDepartmentsPage from './pages/AdminDepartmentsPage.vue'
import AdminCoursesPage from './pages/AdminCoursesPage.vue'
import AdminMaterialsPage from './pages/AdminMaterialsPage.vue'
import AdminNotesPage from './pages/AdminNotesPage.vue'
import AdminQuizzesPage from './pages/AdminQuizzesPage.vue'
import AdminNotificationsPage from './pages/AdminNotificationsPage.vue'
import AdminSettingsPage from './pages/AdminSettingsPage.vue'
import DepartmentsPage from './pages/DepartmentsPage.vue'
import CourseOfferingsPage from './pages/CourseOfferingsPage.vue'
import MaterialsPage from './pages/MaterialsPage.vue'
import NotesLibraryPage from './pages/NotesLibraryPage.vue'
import NoteDetailPage from './pages/NoteDetailPage.vue'
import QuizzesLibraryPage from './pages/QuizzesLibraryPage.vue'
import QuizTakingPage from './pages/QuizTakingPage.vue'
import StudyTasksPage from './pages/StudyTasksPage.vue'
import BookmarksPage from './pages/BookmarksPage.vue'
import LearningProgressPage from './pages/LearningProgressPage.vue'
import SuspendedAccountPage from './pages/SuspendedAccountPage.vue'

const routes = [
  { path: '/', component: LandingPage, meta: { public: true } },
  { path: '/login', component: LoginPage, meta: { public: true } },
  { path: '/register', component: RegisterPage, meta: { public: true } },
  { path: '/forgot-password', component: ForgotPasswordPage, meta: { public: true } },
  { path: '/reset-password', component: ResetPasswordPage, meta: { public: true } },
  { path: '/suspended', component: SuspendedAccountPage, meta: { public: true } },
  { path: '/legal/contact', component: ContactPage, meta: { public: true } },
  { path: '/legal/privacy-policy', component: PrivacyPolicyPage, meta: { public: true } },
  { path: '/legal/terms-of-use', component: TermsOfUsePage, meta: { public: true } },
  
  // Protected student routes
  { 
    path: '/dashboard', 
    component: StudentDashboard,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  { 
    path: '/departments', 
    component: DepartmentsPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  { 
    path: '/departments/:departmentId/courses', 
    component: CourseOfferingsPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  { 
    path: '/offerings/:offeringId/materials', 
    component: MaterialsPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // AI Notes routes
  { 
    path: '/notes', 
    component: NotesLibraryPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  { 
    path: '/notes/:id', 
    component: NoteDetailPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // AI Quiz routes
  { 
    path: '/quizzes', 
    component: QuizzesLibraryPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  { 
    path: '/quizzes/:id', 
    component: QuizTakingPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // Study Tasks routes
  { 
    path: '/tasks', 
    component: StudyTasksPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // Bookmarks routes
  { 
    path: '/bookmarks', 
    component: BookmarksPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // Learning Progress routes
  { 
    path: '/progress', 
    component: LearningProgressPage,
    meta: { requiresAuth: true, requiresApproval: true }
  },

  // Student aliases and friendly redirects (prevent blank pages on alternative paths)
  { path: '/student', redirect: '/dashboard' },
  { path: '/student/dashboard', redirect: '/dashboard' },
  { path: '/student/courses', redirect: '/departments' },
  { path: '/courses', redirect: '/departments' },
  { path: '/student/departments', redirect: '/departments' },
  { path: '/student/departments/:departmentId/courses', redirect: to => `/departments/${to.params.departmentId}/courses` },
  { path: '/student/materials', redirect: '/departments' },
  { path: '/materials', redirect: '/departments' },
  { path: '/student/offerings/:offeringId/materials', redirect: to => `/offerings/${to.params.offeringId}/materials` },
  { path: '/student/notes', redirect: '/notes' },
  { path: '/student/notes/:id', redirect: to => `/notes/${to.params.id}` },
  { path: '/student/quizzes', redirect: '/quizzes' },
  { path: '/student/quizzes/:id', redirect: to => `/quizzes/${to.params.id}` },
  { path: '/student/tasks', redirect: '/tasks' },
  { path: '/student/study-tasks', redirect: '/tasks' },
  { path: '/study-tasks', redirect: '/tasks' },
  { path: '/student/bookmarks', redirect: '/bookmarks' },
  { path: '/student/progress', redirect: '/progress' },
  
  // Protected admin routes
  { 
    path: '/admin', 
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/dashboard', 
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/campus', 
    component: AdminCampusPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/departments', 
    component: AdminDepartmentsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/courses', 
    component: AdminCoursesPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/materials', 
    component: AdminMaterialsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/notes', 
    component: AdminNotesPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/quizzes', 
    component: AdminQuizzesPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/students', 
    component: AdminStudentApprovalsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/approvals', 
    component: AdminStudentApprovalsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/notifications', 
    component: AdminNotificationsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { 
    path: '/admin/settings', 
    component: AdminSettingsPage,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { path: '/admin/student-approvals', redirect: '/admin/students' },

  // Catch-all route to gracefully prevent blank white screens on unknown URLs
  { 
    path: '/:pathMatch(.*)*', 
    redirect: () => {
      const user = authService.getUser()
      if (user?.role === 'admin') {
        return '/admin/dashboard'
      }
      if (authService.isAuthenticated()) {
        return '/dashboard'
      }
      return '/login'
    } 
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }
    return { top: 0 }
  }
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated()
  const isAdmin = authService.isAdmin()
  const isApproved = authService.isApproved()
  const user = authService.getUser()
  const isSuspended = user?.account_status === 'suspended'

  // If account is suspended, always route to /suspended
  if (isSuspended && to.path !== '/suspended') {
    return next('/suspended')
  }

  // Public routes
  if (to.meta.public) {
    // Redirect authenticated users away from login/register
    if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
      if (isAdmin) {
        return next('/admin/dashboard')
      } else if (isApproved) {
        return next('/dashboard')
      }
    }
    return next()
  }

  // Protected routes - require authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  // Admin routes
  if (to.meta.requiresAdmin) {
    if (!isAdmin) {
      return next('/dashboard')
    }
    return next()
  }

  // Student routes - require approval
  if (to.meta.requiresApproval) {
    if (!isApproved) {
      // Unapproved users see a message
      alert('Your account is pending approval. You will be notified once approved.')
      authService.logout()
      return next('/login')
    }
    return next()
  }

  next()
})

createApp(App).use(router).mount('#app')
