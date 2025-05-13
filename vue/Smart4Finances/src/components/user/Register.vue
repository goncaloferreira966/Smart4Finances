<template>
  <div class="flex items-center justify-center min-h-screen">
    <div class="register-container bg-#DAA520 p-8 rounded-lg shadow-lg w-full max-w-xs">
      <h2 class="text-2xl font-bold mb-6 text-center" style="color: black;">Registo</h2>
      <form @submit.prevent="handleRegister">
        <div class="mb-4">
          <label for="name" class="block text-gray-700 font-semibold mb-2">Nome</label>
          <input type="text" id="name" v-model="name"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <div class="mb-4">
          <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail</label>
          <input type="email" id="email" v-model="email"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <div class="mb-4">
          <label for="nickname" class="block text-gray-700 font-semibold mb-2">Nickname</label>
          <input type="text" id="nickname" v-model="nickname"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <div class="mb-4">
          <label for="photo_filename" class="block text-gray-700 font-semibold mb-2">Fotografia de Perfil</label>
          <input type="file" id="photo_filename" @change="handleFileUpload" accept=".png, .jpeg, .jpg"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div class="mb-4">
          <label for="coin" class="block text-gray-700 font-semibold mb-2">Moeda</label>
          <select required id="coin" v-model="coin"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Selecione uma moeda</option>
            <option value="$">Dólar (USD)</option>
            <option value="€">Euro (EUR)</option>
            <option value="R$">Real (BRL)</option>
            <option value="£">Libra (GBP)</option>
          </select>
        </div>
        <div class="mb-6">
          <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
          <input type="password" id="password" v-model="password"
            class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <button style="background-color: black;color: white ;" type="submit"
          class="w-full bg-blue-500 hover:bg-blue-600 font-bold py-2 px-4 rounded">
          Registar
        </button>
      </form>
      <p v-if="errorMessage" class="text-red-500 text-sm mt-4">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
  data() {
    return {
      name: "",
      email: "",
      nickname: "",
      password: "",
      coin: "",
      photo_filename: null,
      errorMessage: "",
    };
  },
  methods: {
    translateErrorMessage(message) {
      const translations = {
        "The nickname has already been taken.": "Este nickname já está em uso.",
        "The email has already been taken.": "Este e-mail já está em uso.",
        "The password must be at least 8 characters.": "A senha deve ter pelo menos 8 caracteres.",
        "The email must be a valid email address.": "O e-mail deve ser um endereço válido."
      };

      return translations[message] || message;
    },
    async handleRegister() {
      if (this.name && this.email && this.nickname && this.password) {
        try {
          const formData = new FormData();
          formData.append("name", this.name);
          formData.append("email", this.email);
          formData.append("nickname", this.nickname);
          formData.append("password", this.password);
          formData.append("coin", this.coin);
          if (this.photo_filename) {
            formData.append("photo_filename", this.photo_filename);
          }
          const response = await axios.post(`/usersPost`, formData, {
            headers: { "Content-Type": "multipart/form-data" },
          });
          if (response.status === 200 || response.status === 201) {
            this.$emit("register-success", response.data);
          } else {
            this.errorMessage = response.data.message || "Erro ao registrar. Tente novamente.";
            toast.error(this.errorMessage);
          }
        } catch (error) {
          console.error(error);
          if (error.response && error.response.data) {
            if (error.response.data.errors) {
              // Processar os erros de validação específicos
              const errorMessages = [];
              for (const field in error.response.data.errors) {
                const originalMessage = error.response.data.errors[field][0];
                errorMessages.push(this.translateErrorMessage(originalMessage));
              }
              this.errorMessage = errorMessages.join(' ');
              toast.error(this.errorMessage);
            } else if (error.response.data.message) {
              this.errorMessage = error.response.data.message;
              toast.error(this.errorMessage);
            } else {
              this.errorMessage = "Erro ao registrar. Tente novamente.";
              toast.error(this.errorMessage);
            }
          } else {
            this.errorMessage = "Ocorreu um erro ao conectar à API. Tente novamente.";
            toast.error(this.errorMessage);
          }
        }
      } else {
        this.errorMessage = "Preencha todos os campos corretamente.";
        toast.error(this.errorMessage);
      }
    },
    handleFileUpload(event) {
      this.photo_filename = event.target.files[0];
    },
  },
};
</script>