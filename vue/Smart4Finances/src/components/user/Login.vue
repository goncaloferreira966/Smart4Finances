<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="login-container bg-white p-8 rounded-lg shadow-lg w-full max-w-xs">
      <h2 class="text-2xl font-bold mb-6 text-center" style="color: black;">Login</h2>
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
        <button style="background-color: black; border-color: #DAA520;" type="submit"
          class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
          Entrar
        </button>
      </form>
      <p class="mt-4 text-center">
        <a href="#" @click.prevent="goToForgotPassword" class="text-blue-500 hover:underline">
          Esqueceu-se da password?
        </a>
      </p>
      <p v-if="showResend" class="mt-2 text-center">
        <a href="#" @click.prevent="resendEmail" class="text-blue-500 hover:underline">
          Reenviar e-mail de confirmação
        </a>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { useAuthStore } from "@/stores/auth";

export default {
  name: 'Login',
  data() {
    return {
      username: "",
      password: "",
      errorMessage: "",
      showResend: false,
    };
  },
  mounted() {
    const token = localStorage.getItem("AccessToken");
    if (token) {
      this.handleLoginWithToken(token);
    }
  },
  methods: {
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1]));
        return payload.sub;
      }
      return null;
    },
    async handleLoginWithToken(token) {
      const authStore = useAuthStore();
      try {
        axios.defaults.headers.common.Authorization = 'Bearer ' + token;
        const user = await authStore.loginWithToken(token);
        if (user) {
          this.$emit('login-success');
        } else {
          throw new Error('Usuário não encontrado');
        }
      } catch (error) {
        console.error(error);
        toast.error("Erro ao realizar login automático.");
      }
    },
    async handleLogin() {
      const authStore = useAuthStore();
      try {
        const user = await authStore.login({
          username: this.username,
          password: this.password,
        });

        if (user) {
          console.log(user.data.email_verified_at);
          if (!user.data.email_verified_at) {
            toast.error("E-mail não verificado. Por favor, confirme o seu e-mail antes de fazer login.");
            this.showResend = true;
            return;
          }
          this.$emit("login-success");
        } else {
          toast.error("Credenciais Inválidas ❌");
        }
      } catch (error) {
        console.error(error);
        toast.error("Erro inesperado no login ❌");
      }
    },
    async resendEmail() {
      try {
        await axios.post('/email/resend', {}, {
          headers: { Authorization: 'Bearer ' + localStorage.getItem('AccessToken') }
        });
        toast.success('E-mail de confirmação reenviado com sucesso!');
        this.showResend = false;
      } catch (err) {
        toast.error('Erro ao reenviar e-mail. Faça login novamente ou contacte o suporte.');
      }
    },
    goToForgotPassword() {
      this.$emit('navigate', 'forgotPassword');
    }
  },
};
</script>

<style scoped>
.login-container {
  background-color: #f7f7f7;
}
</style>
