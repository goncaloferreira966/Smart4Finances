<template>
    <nav class="navbar bg-blue-500 p-4 text-#DAA520 shadow-md">
      <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold"><i class="bi bi-coin"></i> Smart4Finances</h1>
        <ul class="flex space-x-3 mt-2">
          <li v-if="userRole !== 'C'">
            <button @click="$emit('navigate', 'administration')" class="hover:text-gray-200">
              <i class="bi bi-people"></i> Administração
            </button>
          </li>
          <li>
            <button @click="$emit('navigate', 'notifications')" class="hover:text-gray-200">
              <i class="bi bi-bell"></i> Notificações
            </button>
          </li>
          <li v-if="userRole !== 'A'">
            <button @click="$emit('navigate', 'test')" class="hover:text-gray-200">
              <i class="bi bi-bell"></i> Despesas
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
    computed: {
      formattedNickname() {
        const authStore = useAuthStore();
        const nickname = authStore.user?.data?.nickname || "";
        return nickname.charAt(0).toUpperCase() + nickname.slice(1);
      },
    },
    mounted() {
      const authStore = useAuthStore();
      this.userRole = authStore.user.data.type;
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
  