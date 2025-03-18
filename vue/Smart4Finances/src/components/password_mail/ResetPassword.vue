<template>
  <div class="max-w-lg mx-auto p-5 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 10vh;">
    <h2>Redefinir Password</h2>
    <form @submit.prevent="submit">
      <!-- Campo oculto para o token -->
      <input type="hidden" :value="token" />
      <div style="margin-top: 1vh;">
        <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail:</label>
        <input id="email" type="email" :value="email" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" readonly />
      </div>
      <div style="margin-top: 1vh;">
        <label for="password" class="block text-gray-700 font-semibold mb-2">Nova Password:</label>
        <input id="password" type="password" v-model="password" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <div style="margin-top: 1vh;">
        <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">Confirmar Password:</label>
        <input id="password_confirmation" type="password" v-model="password_confirmation" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required />
      </div>
      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mb-4" style="margin-top: 3vh;" :disabled="loading">
        <span v-if="loading">A Redefinir...</span>
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
  props: {
    token: {
      type: String,
      required: true
    },
    email: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      password: '',
      password_confirmation: '',
      message: '',
      loading: false
    };
  },
  methods: {
    async submit() {
      this.loading = true;
      this.message = '';
      try {
        await axios.post('/reset-password', {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });
        toast.success('Password redefinida com sucesso!');
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
input[readonly] {
  background-color: #f0f0f0;
  cursor: not-allowed;
}
</style>
