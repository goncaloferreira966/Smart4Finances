<template>
  <div id="app">

    <!-- Exibir o componente Login apenas se o user não estiver logado -->
    <div v-if="!isLoggedIn">
      <NavbarLoginRegister @navigate="navigateTo" @logout="logout" />
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
  
    </div>

    <footer class="footer">
      <p>&copy; {{ currentYear }} Smart4Finances. Todos os direitos reservados.</p>
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


export default {
  components: {
    Login,
    Navbar,
    NavbarLoginRegister,
    Register,
    Profile,
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
  data() {
    return {
      isLoggedIn: false,
      currentSection: 'profile',
    };
  },
  methods: {
    handleLoginSuccess() {
      this.isLoggedIn = true;
      this.currentSection = 'profile';
    },
    logout() {
      this.isLoggedIn = false;
    },
    navigateTo(section) {
      this.currentSection = section; // Altera a secção ativa
    },
    handleRegisterSuccess() {
      this.isRegistering = false;
      this.currentSection = 'login';
    },
    handleEditUser() {
      this.isRegistering = false;
      this.currentSection = 'editUser';
    },
    showRegisterForm() {
      this.isRegistering = true;
    },
    handleUpdateSuccess() {
      this.currentSection = 'profile';
    },
  },
};



</script>

<style>
/* Estilos para o modo claro */
@media (prefers-color-scheme: light) {
  #app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    background-color: #f0f0f0;
    width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }


/* Track */
::-webkit-scrollbar-track {
  background: #f0f0f0; 
}

  .footer {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: lightseagreen;
    color: #fff;
    font-size: 0.9em;
    margin-top: auto;
  }
}

/* Estilos para o modo escuro */
@media (prefers-color-scheme: dark) {
  #app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    background-color: #f0f0f0;
    width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }


/* Track */
::-webkit-scrollbar-track {
  background: #f0f0f0; 
}

  .footer {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: lightseagreen;
    color: #fff;
    font-size: 0.9em;
    margin-top: auto;
  }
  /*#app {
    font-family: Avenir, Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    background-color: #444e52;
    width: 100%;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  /* Track 
::-webkit-scrollbar-track {
  background: #444e52; 
}

  p, label {
    color: lightseagreen;
  }

  footer p {
    color: white;
  }

  .footer {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background-color: lightseagreen;
    color: #fff;
    font-size: 0.9em;
    margin-top: auto;
  }
    */
}

.footer-links {
  display: flex;
  gap: 15px;
}

.footer-links a {
  color: wheat;
  text-decoration: none;
}

.footer-links a:hover {
  text-decoration: underline;
}

::-webkit-scrollbar {
  width: 15px;
}
 
/* Handle */
::-webkit-scrollbar-thumb {
  background: lightseagreen; 
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: lightseagreen; 
}
</style>
