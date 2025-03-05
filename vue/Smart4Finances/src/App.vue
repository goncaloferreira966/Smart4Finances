<template>
  <div id="app">
    <!-- Exibir o componente Login apenas se o usuário não estiver logado -->
    <div v-if="!isLoggedIn">
      <NavbarLoginRegister :activeForm="currentSection" @navigate="navigateTo" @logout="logout" />
      <div v-if="currentSection === 'login'">
        <Login @login-success="handleLoginSuccess" />
      </div>
      <div v-else-if="currentSection === 'register'">
        <Register @register-success="handleRegisterSuccess" />
      </div>
      <div v-else>
        <Login @login-success="handleLoginSuccess" />
      </div>
    </div>

    <!-- Conteúdo da aplicação principal -->
    <div v-else>
      <Navbar @navigate="navigateTo" @logout="logout" />
      <div v-if="currentSection === 'profile'">
        <Profile @editer="handleEditUser" @logout="logout" />
      </div>
      <div v-else-if="currentSection === 'editUser'">
        <EditUser @update-success="handleUpdateSuccess" @update-cancel="handleUpdateCancel" />
      </div>
      <div v-else-if="currentSection === 'administration'">
        <Administration />
      </div>
      <div v-else-if="currentSection === 'notifications'">
        <Notifications />
      </div>
      <div v-else-if="currentSection === 'test'">
        <test />
      </div>
      <div v-else-if="currentSection === 'dashboardadmin'">
        <DashboardAdmin />
      </div>
      <div v-else-if="currentSection === 'dashboardclient'">
        <DashboardClient />
      </div>
    </div>

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
import Login from './components/Login.vue';
import Navbar from './components/Navbar.vue';
import NavbarLoginRegister from './components/NavbarLoginRegister.vue';
import Register from './components/Register.vue';
import Profile from './components/Profile.vue';
import EditUser from './components/EditUser.vue';
import test from './components/test.vue';
import Administration from './components/Administration.vue';
import Notifications from './components/Notifications.vue';
import DashboardAdmin from './components/DashboardAdmin.vue';
import DashboardClient from './components/DashboardClient.vue';

import { toast } from 'vue3-toastify';

export default {
  components: {
    Login,
    Navbar,
    NavbarLoginRegister,
    Register,
    Profile,
    EditUser,
    Administration,
    Notifications,
    test,
    DashboardAdmin,
    DashboardClient,
  },
  data() {
    return {
      isLoggedIn: false,
      isRegistering: false,
      currentSection: 'login',
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
  methods: {
    handleLoginSuccess() {
      this.isLoggedIn = true;
      this.currentSection = 'profile';
    },
    logout() {
      this.isLoggedIn = false;
      toast.info("Logout realizado com sucesso!");
    },
    navigateTo(section) {
      this.currentSection = section;
    },
    handleRegisterSuccess() {
      this.isRegistering = false;
      this.currentSection = 'login';
      toast.success("Registro realizado com sucesso! Faça login.");
    },
    handleEditUser() {
      this.isRegistering = false;
      this.currentSection = 'editUser';
    },
    handleUpdateSuccess() {
      this.currentSection = 'profile';
      toast.success("Dados atualizados com sucesso!");
    },
    handleUpdateCancel() {
      this.currentSection = 'profile';
    },
  },
};
</script>