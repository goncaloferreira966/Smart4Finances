<template>
  <nav class="navbar bg-blue-500 p-4 text-#DAA520 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-xl font-bold">
        <i class="bi bi-coin"></i> Smart4Finances
      </h1>
      <ul class="flex space-x-4">
        <template v-if="isLoggedIn">
          <li>
            <router-link to="/profile" class="hover:text-gray-200">
              <i class="bi bi-person"></i> {{ formattedNickname }}
            </router-link>
          </li>
          <li>
            <router-link to="/notifications" class="hover:text-gray-200">
              <i class="bi bi-bell"></i> Notificações
            </router-link>
          </li>
          <li>
            <router-link to="/add-expenses" class="hover:text-gray-200">
              <i class="bi bi-plus-circle"></i> Despesa
            </router-link>
          </li>
          <li v-if="userRole === 'A'">
            <router-link to="/administration" class="hover:text-gray-200">
              <i class="bi bi-people"></i> Administração
            </router-link>
          </li>
          <li>
            <button @click="handleLogout" class="hover:text-red-500">
              <i class="bi bi-box-arrow-right"></i> Sair
            </button>
          </li>
        </template>
        <template v-else>
          <li>
            <router-link to="/login" class="hover:text-gray-200">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </router-link>
          </li>
          <li>
            <router-link to="/register" class="hover:text-gray-200">
              <i class="bi bi-person-bounding-box"></i> Registar
            </router-link>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script>
import { useAuthStore } from '@/stores/auth';
import { computed } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: "Navbar",
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();

    // Cria um computed reativo que verifica se há usuário logado
    const isLoggedIn = computed(() => authStore.user !== null);

    const formattedNickname = computed(() => {
      const nickname = authStore.user?.data?.nickname || "Usuário";
      return nickname.charAt(0).toUpperCase() + nickname.slice(1);
    });

    // Obtem a role diretamente do authStore
    const userRole = computed(() => authStore.user?.data?.type || 'user');

    const handleLogout = () => {
      authStore.logout();
      console.log('Usuário deslogado');
      authStore.clearUser(); // Limpa a store e dados persistidos
      router.push({ name: 'Login' });
    };

    return { isLoggedIn, formattedNickname, userRole, handleLogout };
  }
};
</script>

<style scoped>
.navbar a {
  text-decoration: none;
  color: inherit;
}
.navbar button {
  background: none;
  border: none;
  cursor: pointer;
  font: inherit;
}
</style>
