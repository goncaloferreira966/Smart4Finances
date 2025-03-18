<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2>Recuperar Password</h2>
    <form @submit.prevent="submit">
      <label for="email">Digite o seu e-mail:</label>
      <br>
      <input
        id="email"
        type="email"
        v-model="email"
        placeholder="O seu e-mail"
        required
        class="p-2 border rounded w-full mb-4 focus:outline-none focus:ring-2 focus:ring-yellow-500"
        style="width: 30vh;"
      />
      <br>
      <button class="bg-green-500 text-white px-4 py-2 rounded mb-4" type="submit" :disabled="loading">
        <span v-if="loading">A Enviar...</span>
        <span v-else>Enviar</span>
      </button>
    </form>
    <p v-if="message">{{ message }}</p>
    <p>
      <a href="#" @click.prevent="backToLogin" class="text-blue-500 hover:underline">
        Voltar para o Login
      </a>
    </p>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
  name: 'ForgotPassword',
  data() {
    return {
      email: '',
      message: '',
      loading: false
    };
  },
  methods: {
    async submit() {
      this.loading = true;
      this.message = '';
      try {
        // Envia o e-mail para recuperação de senha (confirme que a rota está correta)
        await axios.post('/forgot-password', { email: this.email });
        toast.success("Link de redefinição enviado para seu e-mail!");
        // Após sucesso, volta para a tela de login
        this.backToLogin();
      } catch (error) {
        this.message = 'Erro ao enviar o e-mail. Verifique e tente novamente.';
        toast.error("Erro ao enviar o e-mail. Verifique e tente novamente.");
      } finally {
        this.loading = false;
      }
    },
    backToLogin() {
      // Emite um evento para que o componente pai mude para a seção 'login'
      this.$emit('navigate', 'login');
    }
  }
};
</script>

<style scoped>
.forgot-password {
  max-width: 400px;
  margin: 0 auto;
  padding: 1rem;
  text-align: center;
}

input {
  width: 100%;
  padding: 0.5rem;
  margin: 0.5rem 0;
}

button {
  padding: 0.5rem 1rem;
  cursor: pointer;
}
</style>
