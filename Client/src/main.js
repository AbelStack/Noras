import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './style.css'
import App from './App.vue'
import { authService } from './services/auth'

// Public pages
import LandingPage from './pages/LandingPage.vue'
import LoginPage from './pages/LoginPage.vue'
import RegisterPage from './pages/RegisterPage.vue'
import ContactPage from './pages/legal/ContactPage.vue'
import PrivacyPolicyPage from './pages/legal/PrivacyPolicyPage.vue'
import TermsOfUsePage from './pages/legal/TermsOfUsePage.vue'

// Protected pages
import StudentDashboard from './pages/StudentDashboard.vue'
import AdminDashboard from './pages/AdminDashboard.vue'

const routes = [
  { path: '/', component: LandingPage, meta: { public: true } },
  { path: '/login', component: LoginPage, meta: { public: true } },
  { path: '/register', component: RegisterPage, meta: { public: true } },
  { path: '/legal/contact', component: ContactPage, meta: { public: true } },
  { path: '/legal/privacy-policy', component: PrivacyPolicyPage, meta: { public: true } },
  { path: '/legal/terms-of-use', component: TermsOfUsePage, meta: { public: true } },
  
  // Protected student routes
  { 
    path: '/dashboard', 
    component: StudentDashboard,
    meta: { requiresAuth: true, requiresApproval: true }
  },
  
  // Protected admin routes
  { 
    path: '/admin/dashboard', 
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
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
