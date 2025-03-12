<template>
  <nav class="navbar bg-blue-500 p-4 text-#DAA520 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-xl font-bold">
        
        <li  style=" list-style: none;" v-if="userRole !== 'C'">
            <button @click="$emit('navigate', 'dashboardadmin')" class="hover:text-gray-200">
              <i class="bi bi-coin"></i> Smart4Finances
            </button>
          </li>
          <li style=" list-style: none;" v-if="userRole !== 'A'">
            <button @click="$emit('navigate', 'dashboardclient')" class="hover:text-gray-200">
              <i class="bi bi-coin"></i> Smart4Finances
            </button>
          </li>
      </h1>
      <ul class="flex space-x-4">
        <template v-if="isLoggedIn">
          <li v-if="userRole !== 'C'">
            <button @click="$emit('navigate', 'dashboardadmin')" class="hover:text-gray-200">
              <i class="bi bi-pie-chart"></i> Dashboard
            </button>
          </li>
          <li v-if="userRole !== 'A'">
            <button @click="$emit('navigate', 'dashboardclient')" class="hover:text-gray-200">
              <i class="bi bi-pie-chart"></i> Dashboard
            </button>
          </li>
          <li v-if="userRole !== 'C'">
            <button @click="$emit('navigate', 'administration')" class="hover:text-gray-200">
              <i class="bi bi-people"></i> Administração
            </button>
          </li>
          <li v-if="userRole !== 'A'">
            <button @click="$emit('navigate', 'IncomeList')" class="hover:text-gray-200">
              <i class="bi bi-plus-circle"></i> Receitas
            </button>
          </li>
          <li v-if="userRole !== 'A'">
            <button @click="$emit('navigate', 'ExpensesList')" class="hover:text-gray-200">
              <i class="bi bi-dash-circle"></i> Despesas
            </button>
          </li>
          <li>
            <button @click="$emit('navigate', 'notifications')" class="hover:text-gray-200">
              <i class="bi bi-bell"></i> Notificações
            </button>
          </li>
          <li>
            <button @click="$emit('navigate', 'profile')" class="hover:text-gray-200">
              <i class="bi bi-person"></i> {{ formattedNickname }}
            </button>
          </li>
          <li>
            <button @click="handleLogout" class="hover:text-red-500">
              <i class="bi bi-box-arrow-right"></i> Sair
            </button>
          </li>
        </template>
        <template v-else>
          <!-- Se estivermos no formulário de login, exibe o botão de Registar -->
          <li v-if="activeForm === 'login'">
            <button @click="$emit('navigate', 'register')" class="hover:text-gray-200">
              <i class="bi bi-person-bounding-box"></i> Registar
            </button>
          </li>
          <!-- Se estivermos no formulário de registo, exibe o botão de Login -->
          <li v-else-if="activeForm === 'register'">
            <button @click="$emit('navigate', 'login')" class="hover:text-gray-200">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script>
  import { useAuthStore } from "@/stores/auth";
  
  export default {
    name: "Navbar",
    data() {
      return {
        userRole: "",
      };
    },
    props: {
      activeForm: {
        type: String,
        required: true,
      },
      isLoggedIn: {
        type: Boolean,
        required: true
      }
    },
    computed: {
      formattedNickname() {
        const authStore = useAuthStore();
        const nickname = authStore.user?.data?.nickname || "";
        this.userRole = authStore.user?.data?.type;        
        return nickname.charAt(0).toUpperCase() + nickname.slice(1);
      },
    },
    mounted() {
    },
    methods: {
      async handleLogout() {
        const authStore = useAuthStore();
        await authStore.logout();
        this.$emit("logout");
      }
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
