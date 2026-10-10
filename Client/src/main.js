import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './style.css'
import App from './App.vue'
import LandingPage from './pages/LandingPage.vue'
import LoginPage from './pages/LoginPage.vue'
import RegisterPage from './pages/RegisterPage.vue'
import ContactPage from './pages/legal/ContactPage.vue'
import PrivacyPolicyPage from './pages/legal/PrivacyPolicyPage.vue'
import TermsOfUsePage from './pages/legal/TermsOfUsePage.vue'

const routes = [
  { path: '/', component: LandingPage },
  { path: '/login', component: LoginPage },
  { path: '/register', component: RegisterPage },
  { path: '/legal/contact', component: ContactPage },
  { path: '/legal/privacy-policy', component: PrivacyPolicyPage },
  { path: '/legal/terms-of-use', component: TermsOfUsePage },
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

createApp(App).use(router).mount('#app')
