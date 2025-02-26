<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="login-container bg-white p-8 rounded-lg shadow-lg w-full max-w-xs">
      <h2 style="color: lightseagreen;" class="text-2xl font-bold mb-6 text-center">Login</h2>
      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label for="username" class="block text-gray-700 font-semibold mb-2">Utilizador</label>
          <input type="text" id="username" v-model="username"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <div class="mb-6">
          <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
          <input type="password" id="password" v-model="password"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <button style="background-color: lightseagreen; border-color: lightseagreen;" type="submit"
          class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
          Entrar
        </button>
      </form>
      <p v-if="errorMessage" class="text-red-500 text-sm mt-4">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from "@/stores/auth"; // Importa a store Pinia


export default {
  data() {
    return {
      username: "",
      password: "",
      errorMessage: "",
    };
  },
  mounted() {
    // Verifica se o token de acesso já está no localStorage
    const token = localStorage.getItem("AccessToken");
    if (token) {
      // Se o token existir, tenta realizar o login automaticamente
      this.handleLoginWithToken(token);
    }
  },
  methods: {
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1])); // Decodifica o payload do token
        return payload.sub; // Retorna o ID do user (sub)
      }
      return null;
    },
    async handleLoginWithToken(token) {
      const authStore = useAuthStore();
      try {
        // Configura o token para o axios
        axios.defaults.baseURL = `http://${import.meta.env.VITE_API_DOMAIN}/api`;
        axios.defaults.headers.common.Authorization = 'Bearer ' + token;

        // Tenta obter as informações do usuário
        const user = await authStore.loginWithToken(token); // Login automático
        if (user) {
          console.log('Login automático bem-sucedido', user);
          this.$emit('login-success');
        } else {
          throw new Error('Usuário não encontrado');
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = "Erro ao realizar login automático.";
      }
    },

    async handleLogin() {
      const authStore = useAuthStore(); // Obtém a instância da store

      try {
        // Realiza o login através da store
        const user = await authStore.login({
          username: this.username,
          password: this.password,
        });

        if (user) {
          console.log(user)
          // Redireciona ou emite evento após sucesso no login
          this.$emit("login-success");
        }
        else{
          this.errorMessage = "Credenciais Inválidas.";
        }
      } catch (error) {
        console.error(error);
        this.errorMessage = "Erro inesperado no login.";
      }
    },
  },
};
</script>

<style scoped></style>
