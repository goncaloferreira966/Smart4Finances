<template>
    <div class="flex items-center justify-center min-h-screen">
      <div class="register-container bg-#DAA520 p-8 rounded-lg shadow-lg w-full max-w-xs">
        <h2 class="text-2xl font-bold mb-6 text-center" style="color: black;">Edição de Perfil</h2>
        <form @submit.prevent="handleUpdate">
          <p class="text-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Altere apenas os campos que deseja Atualizar.
          </p>
          <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-2">Nome</label>
            <input type="text" id="name" v-model="name" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="mb-4">
            <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail</label>
            <input type="email" id="email" v-model="email" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="mb-4">
            <label for="nickname" class="block text-gray-700 font-semibold mb-2">Nickname</label>
            <input type="text" id="nickname" v-model="nickname" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="mb-4">
            <label for="photo_filename" class="block text-gray-700 font-semibold mb-2">Fotografia de Perfil</label>
            <input type="file" id="photo_filename" @change="handleFileUpload" accept=".png, .jpeg, .jpg" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div class="mb-6">
            <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
            <input type="password" id="password" v-model="password" class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <button style="background-color: black;color: #DAA520;" type="submit" class="w-full bg-blue-500 hover:bg-blue-600 font-bold py-2 px-4 rounded">
            <i class="bi bi-floppy-fill"></i> Guardar Alterações
          </button>
        </form>
        <button @click="handleEditUserCancel()" style="border: 0; color: white;" class="btn btn-danger w-100 mt-4">
          <i class="bi bi-x-circle-fill"></i> Cancelar
        </button>
        <p v-if="errorMessage" class="text-red-500 text-sm mt-4">{{ errorMessage }}</p>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { toast } from 'vue3-toastify';
  import { useAuthStore } from "@/stores/auth";
  
  export default {
    data() {
      return {
        name: "",
        email: "",
        nickname: "",
        password: "",
        photo_filename: null,
        errorMessage: "",
      };
    },
    methods: {
      async handleUpdate() {
        const authStore = useAuthStore();
        try {
          const formData = new FormData();
          if (this.name) formData.append("name", this.name);
          if (this.email) formData.append("email", this.email);
          if (this.nickname) formData.append("nickname", this.nickname);
          if (this.password) formData.append("password", this.password);
          if (this.photo_filename) formData.append("photo_filename", this.photo_filename);
          const userId = this.getUserIdFromToken();
          const token = localStorage.getItem("AccessToken");
          console.log(token);
          const response = await axios.post(`/user/${userId}/updateProfile`, formData, {
            headers: { Authorization: `Bearer ${token}` }
          });
          if (response.status === 200) {
            toast.success("Alteração de dados realizada com sucesso!");
            await authStore.atualizar(token);
            this.$emit("update-success");
          } else {
            this.errorMessage = response || "Erro ao Atualizar Dados. Tente novamente.";
            toast.error(this.errorMessage);
          }
        } catch (error) {
          console.error(error);
          this.errorMessage = "Ocorreu um erro ao conectar à API. Tente novamente.";
          toast.error(this.errorMessage);
        }
      },
      handleEditUserCancel() {
        this.$emit("update-cancel");
      },
      getUserIdFromToken() {
        const token = localStorage.getItem("AccessToken");
        if (token) {
          const payload = JSON.parse(atob(token.split(".")[1]));
          return payload.sub;
        }
        return null;
      },
      handleFileUpload(event) {
        this.photo_filename = event.target.files[0];
      },
    },
  };
  </script>