<template>
  <div id="app">
    <!-- Navbar única que se adapta ao estado da aplicação -->
    <Navbar :isLoggedIn="isLoggedIn" />

    <!-- Conteúdo principal renderizado conforme a rota -->
    <router-view />

    <!-- Footer fixo para toda a aplicação -->
    <footer class="footer">
      <p style="color:#DAA520">&copy; {{ currentYear }} Smart4Finances. Todos os direitos reservados.</p>
      <div class="footer-links">
        <a href="#">Política de Privacidade</a>
        <a href="#">Termos de Serviço</a>
        <a href="#">Suporte</a>
      </div>
    </footer>
  </div>
</template>

<script>
import Navbar from './components/NavBar/Navbar.vue';
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export default {
  components: {
    Navbar,
  },
  setup() {
    const authStore = useAuthStore();
    // Supondo que seu store possui uma propriedade "isLoggedIn"
    const isLoggedIn = computed(() => authStore.isLoggedIn);
    const currentYear = new Date().getFullYear();
    return { isLoggedIn, currentYear };
  }
};
</script>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.footer {
  text-align: center;
  padding: 1rem;
}
.footer-links a {
  margin: 0 0.5rem;
  text-decoration: none;
  color: inherit;
}
</style>
