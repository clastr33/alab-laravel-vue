import { createRouter, createWebHistory } from 'vue-router'
import LoginView from './views/LoginView.vue'
import ResultsView from './views/ResultsView.vue'
import { getToken, isTokenValid } from './services/auth'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/results', name: 'results', component: ResultsView, meta: { requiresAuth: true } },
]

export const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  if (!to.meta.requiresAuth) return true
  const token = getToken()
  if (!token) return { name: 'login' }
  if (!isTokenValid(token)) return { name: 'login' }
  return true
})
