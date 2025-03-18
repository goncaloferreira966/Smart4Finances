<template>
    <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
      <h2>Redefinir Password</h2>
      <form @submit.prevent="submit">
        <!-- Campo oculto para o token -->
        <input type="hidden" v-model="token" />
        <div>
          <label for="email">E-mail:</label>
          <input id="email" type="email" v-model="email" required />
        </div>
        <div>
          <label for="password">Nova Password:</label>
          <input id="password" type="password" v-model="password" required />
        </div>
        <div>
          <label for="password_confirmation">Confirmar Password:</label>
          <input id="password_confirmation" type="password" v-model="password_confirmation" required />
        </div>
        <button type="submit" :disabled="loading">
          <span v-if="loading">A Redifinir...</span>
          <span v-else>Redefinir Password</span>
        </button>
      </form>
      <p v-if="message">{{ message }}</p>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { toast } from 'vue3-toastify';
  
  export default {
    name: 'ResetPassword',
    data() {
      return {
        email: '',
        password: '',
        password_confirmation: '',
        token: '',
        message: '',
        loading: false
      };
    },
    created() {
      // Extrai os parâmetros da URL utilizando URLSearchParams
      // Exemplo de URL: http://seudominio.com/reset-password?token=12345&email=usuario@exemplo.com
      const params = new URLSearchParams(window.location.search);
      this.token = params.get('token') || '';
      this.email = params.get('email') || '';
    },
    methods: {
      async submit() {
        this.loading = true;
        this.message = '';
        try {
          // Envia os dados para a rota de reset de Password no back-end
          await axios.post('/reset-password', {
            token: this.token,
            email: this.email,
            password: this.password,
            password_confirmation: this.password_confirmation,
          });
          toast.success('Password redefinida com sucesso!');
          // Redireciona para a página de login (ou onde desejar) usando window.location
          window.location.href = '/login';
        } catch (error) {
          this.message = 'Erro ao redefinir a Password. Verifique os dados e tente novamente.';
          toast.error(this.message);
        } finally {
          this.loading = false;
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .reset-password {
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
  