<template>
  <nav class="navbar bg-black p-1 text-[#DAA520] shadow-md">
    <div class="container mx-auto flex justify-between items-center">
      <!-- Logo -->
      <h1 class="text-xl font-bold">
        <button @click="$emit('navigate', 'home')" class="hover:text-gray-200 flex items-center">
          <i class="bi bi-coin mr-2 text-xl"></i> Smart4Finances
        </button>
      </h1>

      <!-- Theme Toggle -->
      <button @click="toggleTheme" class="text-[#DAA520] p-1 rounded-md hover:bg-gray-800 transition-colors mr-2">
        <i class="bi" :class="{ 'bi-moon-fill': themeStore.darkMode, 'bi-sun-fill': !themeStore.darkMode }"></i>
      </button>

      <!-- Menu Button - Shows on mobile -->
      <button @click="toggleMenu"
        class="block lg:hidden text-[#DAA520] p-1 rounded-md hover:bg-gray-800 transition-colors">
        <i class="bi bi-list text-xl"></i>
      </button>

      <!-- Menu Items -->
      <div v-if="isMenuOpen || isLargeScreen"
        class="lg:flex fixed lg:static inset-0 top-[60px] z-50 w-full lg:w-auto bg-black lg:bg-transparent mobile-menu-wrapper">
        <div class="mobile-menu-container">
          <ul
            class="flex flex-col lg:flex-row items-start lg:items-center space-y-2 lg:space-y-0 lg:space-x-1 mobile-menu-content">
            <template v-if="isLoggedIn">
              <li v-if="userRole !== 'C'" class="w-full lg:w-auto">
                <button @click="navigateTo('dashboardadmin')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'dashboardadmin' }">
                  <i class="bi bi-pie-chart"></i> <span class="lg:hidden xl:inline">Dashboard</span>
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('dashboardclient')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'dashboardclient' }">
                  <i class="bi bi-pie-chart"></i> <span class="lg:hidden xl:inline">Dashboard</span>
                </button>
              </li>
              <li v-if="userRole !== 'C'" class="w-full lg:w-auto">
                <button @click="navigateTo('administration')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'administration' }">
                  <i class="bi bi-people"></i> <span class="lg:hidden xl:inline">Administração</span>
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('BudgetList')"  class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-cash-coin"></i> <span class="lg:hidden xl:inline">Limites</span>
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('InvestmentList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'InvestmentList' }">
                  <i class="bi bi-currency-bitcoin"></i> <span class="lg:hidden xl:inline">Investimentos</span>
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('IncomeList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'IncomeList' }">
                  <i class="bi bi-plus-circle"></i> <span class="lg:hidden xl:inline">Receitas</span>
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('ExpensesList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'ExpensesList' }">
                  <i class="bi bi-dash-circle"></i> <span class="lg:hidden xl:inline">Despesas</span>
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="navigateTo('notifications')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'notifications' }">
                  <i class="bi bi-bell"></i> <span class="lg:hidden xl:inline">Notificações</span>
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="navigateTo('profile')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'profile' }">
                  <i class="bi bi-person"></i> <span class="lg:hidden xl:inline">{{ formattedNickname }}</span>
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="handleLogout"
                  class="nav-link rounded-md w-full hover:bg-red-900 text-red-500 hover:text-red-300">
                  <i class="bi bi-box-arrow-right"></i> <span class="lg:hidden xl:inline">Sair</span>
                </button>
              </li>
            </template>
            <template v-else>
              <li v-if="activeForm === 'login'" class="w-full lg:w-auto">
                <button @click="navigateTo('register')" class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-person-bounding-box"></i> Registar
                </button>
              </li>
              <li v-else-if="activeForm === 'register'" class="w-full lg:w-auto">
                <button @click="navigateTo('login')" class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { useAuthStore } from "@/stores/auth";
import { useThemeStore } from "@/stores/theme";

export default {
  name: "Navbar",
  data() {
    return {
      userRole: "",
      isMenuOpen: false,
      isLargeScreen: false
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
    themeStore() {
      return useThemeStore();
    }
  },
  methods: {
    async handleLogout() {
      const authStore = useAuthStore();
      await authStore.logout();
      this.$emit("logout");
    },
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
      // Prevenir scroll da página quando o menu está aberto no mobile
      if (this.isMenuOpen && !this.isLargeScreen) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    },
    navigateTo(section) {
      this.$emit('navigate', section);
      if (!this.isLargeScreen) {
        this.isMenuOpen = false;
        document.body.style.overflow = '';
      }
    },
    handleResize() {
      this.isLargeScreen = window.innerWidth >= 1024;
      if (this.isLargeScreen) {
        document.body.style.overflow = '';
      }
    },
    toggleTheme() {
      this.themeStore.toggleDarkMode();
    }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
    this.handleResize(); // Inicializa o estado do menu ao montar
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize);
    document.body.style.overflow = '';
  }
};
</script>

<style scoped>
.navbar {
  position: relative;
  z-index: 100;
}

.nav-link {
  @apply flex items-center gap-2 px-2 py-2 text-[#DAA520] transition-all duration-200;
}

.navbar button {
  background: none;
  border: none;
  cursor: pointer;
  font: inherit;
  white-space: nowrap;
}

.active-link {
  @apply bg-gray-800 text-white;
}

/* Para telas grandes (desktop), fazer os botões mais compactos */
@media (min-width: 1024px) {
  .nav-link {
    @apply px-2 py-1;
  }
}

/* Para telas médias (apenas ícones) */
@media (min-width: 1024px) and (max-width: 1279px) {
  .nav-link {
    @apply px-2 py-1;
  }
}

@media (max-width: 1023px) {
  .navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
  }

  .mobile-menu-wrapper {
    height: calc(100vh - 60px);
    max-height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    bottom: 0;
  }

  .mobile-menu-container {
    height: auto;
    max-height: 100%;
    overflow-y: scroll;
    overflow-x: hidden;
    padding: 1rem;
    scrollbar-width: thin;
    -ms-overflow-style: none;
    overscroll-behavior: contain;
    touch-action: pan-y;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 100px;
  }

  .mobile-menu-content {
    padding-bottom: 20px;
  }

  .mobile-menu-container::-webkit-scrollbar {
    width: 5px;
  }

  .mobile-menu-container::-webkit-scrollbar-track {
    background: #111;
  }

  .mobile-menu-container::-webkit-scrollbar-thumb {
    background-color: #DAA520;
    border-radius: 10px;
  }

  .nav-link {
    width: 100%;
    justify-content: flex-start;
    border-radius: 8px;
    margin: 4px 0;
  }
}
</style>
