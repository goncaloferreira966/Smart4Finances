<template>
  <nav class="navbar bg-black p-1 text-[#DAA520] shadow-md">
    <div class="container mx-auto flex justify-between items-center">
      <!-- Logo -->
      <h1 class="text-xl font-bold">
        <button @click="$emit('navigate', 'home')" class="hover:text-gray-200 flex items-center">
          <i class="bi bi-coin mr-2 text-2xl"></i> Smart4Finances
        </button>
      </h1>

      <!-- Menu Button - Shows on mobile -->
      <button @click="toggleMenu"
        class="block lg:hidden text-[#DAA520] p-1 rounded-md hover:bg-gray-800 transition-colors">
        <i class="bi bi-list text-2xl"></i>
      </button>

      <!-- Menu Items -->
      <div v-if="isMenuOpen || isLargeScreen"
        class="lg:flex fixed lg:static inset-0 top-[72px] z-50 w-full lg:w-auto bg-black lg:bg-transparent mobile-menu-wrapper">
        <div class="mobile-menu-container">
          <ul
            class="flex flex-col lg:flex-row items-start lg:items-center space-y-2 lg:space-y-0 lg:space-x-1 mobile-menu-content">
            <template v-if="isLoggedIn">
              <li v-if="userRole !== 'C'" class="w-full lg:w-auto">
                <button @click="navigateTo('dashboardadmin')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'dashboardadmin' }">
                  <i class="bi bi-pie-chart text-lg"></i> Dashboard
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('dashboardclient')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'dashboardclient' }">
                  <i class="bi bi-pie-chart text-lg"></i> Dashboard
                </button>
              </li>
              <li v-if="userRole !== 'C'" class="w-full lg:w-auto">
                <button @click="navigateTo('administration')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'administration' }">
                  <i class="bi bi-people text-lg"></i> Administração
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('BudgetList')"  class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-cash-coin"></i> Limites
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('InvestmentList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'InvestmentList' }">
                  <i class="bi bi-currency-bitcoin text-lg"></i> Investimentos
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('IncomeList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'IncomeList' }">
                  <i class="bi bi-plus-circle text-lg"></i> Receitas
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('ExpensesList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'ExpensesList' }">
                  <i class="bi bi-dash-circle text-lg"></i> Despesas
                </button>
              </li>
              <li v-if="userRole !== 'A'" class="w-full lg:w-auto">
                <button @click="navigateTo('CategoryList')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'CategoryList' }">
                  <i class="bi bi-tag text-lg"></i> Categorias
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="navigateTo('notifications')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'notifications' }">
                  <i class="bi bi-bell text-lg"></i> Notificações
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="navigateTo('profile')" class="nav-link rounded-md w-full hover:bg-gray-800"
                  :class="{ 'active-link': activeForm === 'profile' }">
                  <i class="bi bi-person text-lg"></i> {{ formattedNickname }}
                </button>
              </li>
              <li class="w-full lg:w-auto">
                <button @click="handleLogout"
                  class="nav-link rounded-md w-full hover:bg-red-900 text-red-500 hover:text-red-300">
                  <i class="bi bi-box-arrow-right text-lg"></i> Sair
                </button>
              </li>
            </template>
            <template v-else>
              <li v-if="activeForm === 'login'" class="w-full lg:w-auto">
                <button @click="navigateTo('register')" class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-person-bounding-box text-lg"></i> Registar
                </button>
              </li>
              <li v-else-if="activeForm === 'register'" class="w-full lg:w-auto">
                <button @click="navigateTo('login')" class="nav-link rounded-md w-full hover:bg-gray-800">
                  <i class="bi bi-box-arrow-in-right text-lg"></i> Login
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

export default {
  name: "Navbar",
  data() {
    return {
      userRole: "",
      isMenuOpen: false
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
    isLargeScreen() {
      return window.innerWidth >= 1024;
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
    }
  },
  mounted() {
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 1024) {
        this.isMenuOpen = false;
        document.body.style.overflow = '';
      }
    });
  },
  beforeUnmount() {
    window.removeEventListener('resize', null);
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
  @apply flex items-center gap-2 px-4 py-3 text-[#DAA520] transition-all duration-200;
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

@media (max-width: 1023px) {
  .navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
  }

  .mobile-menu-wrapper {
    height: calc(100vh - 72px);
    max-height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 72px;
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
