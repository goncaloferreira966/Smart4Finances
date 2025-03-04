import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Users/Login.vue';
import Register from '../components/Users/Register.vue';
import Profile from '../components/Users/Profile.vue';
import EditUser from '../components/Users/EditUser.vue';
import Administration from '../components/Users/Administration.vue';
import Notifications from '../components/Users/Notifications.vue';
import addExpenses from '../components/expenses/addExpenses.vue';
import { useAuthStore } from "@/stores/auth";

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: Notifications,
    meta: { requiresAuth: true }
  },
  {
    path: '/add-expenses',
    name: 'AddExpenses',
    component: addExpenses,
    props: route => ({ expenseId: route.query.id || null }),
    meta: { requiresAuth: true }
  },
  {
    path: '/edit-user',
    name: 'EditUser',
    component: EditUser,
    meta: { requiresAuth: true, requiresAdmin: true } // Apenas admin pode acessar
  },
  {
    path: '/administration',
    name: 'Administration',
    component: Administration,
    meta: { requiresAuth: true, requiresAdmin: true } // Apenas admin pode acessar
  },
  {
    path: '/',
    redirect: '/login'
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

// Navigation guard usando a store (Pinia) para verificar o estado de autenticação
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isLoggedIn = authStore.user !== null; // Reativo: se não houver usuário, não está logado
  console.log(isLoggedIn);
  // Se a rota requer autenticação e o usuário não estiver logado, redireciona para Login
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: 'Login' });
  }

  // Se o usuário já estiver logado e tentar acessar Login ou Register, redireciona para Profile
  if ((to.name === 'Login' || to.name === 'Register') && isLoggedIn) {
    return next({ name: 'Profile' });
  }
  console.log(authStore.user?.data?.type);
  // Rotas que exigem admin: verifique o tipo de usuário
  if (to.meta.requiresAdmin) {
    // Supondo que o tipo de usuário venha no campo "data.type" e que o admin seja identificado como 'C'
    if (authStore.user?.data?.type !== 'A') {
      return next({ name: 'Profile' });
    }
  }

  next();
});

export default router;
