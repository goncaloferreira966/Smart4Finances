<template>
    <div class="flex items-center justify-center min-h-screen">
        <div class="register-container bg-white p-8 rounded-lg shadow-lg w-full max-w-xs">
            <h2 style="color: lightseagreen;" class="text-2xl font-bold mb-6 text-center">Edição de Perfil</h2>
            <form @submit.prevent="handleUpdate">
                <p class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Altere apenas os campos que
                    deseja Atualizar.</p>
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-semibold mb-2">Nome</label>
                    <input type="text" id="name" v-model="name"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">E-mail</label>
                    <input type="email" id="email" v-model="email"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="mb-4">
                    <label for="nickname" class="block text-gray-700 font-semibold mb-2">Nickname</label>
                    <input type="text" id="nickname" v-model="nickname"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="mb-4">
                    <label for="photo_filename" class="block text-gray-700 font-semibold mb-2">Fotografia de
                        Perfil</label>
                    <input type="file" id="photo_filename" @change="handleFileUpload" accept=".png, .jpeg, .jpg"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" id="password" v-model="password"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <button style="background-color: lightseagreen; border-color: lightseagreen;" type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Guardar Alterações
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
                // Obtém a role do user do localStorage
                 const authStore = useAuthStore();

            try {

                const formData = new FormData();

                if (this.name) {
                    formData.append("name", this.name);
                    console.log("Adicionado name:", this.name);
                }

                if (this.email) {
                    formData.append("email", this.email);
                    console.log("Adicionado email:", this.email);
                }

                if (this.nickname) {
                    formData.append("nickname", this.nickname);
                    console.log("Adicionado nickname:", this.nickname);
                }

                if (this.password) {
                    formData.append("password", this.password);
                    console.log("Adicionado password:", this.password);
                }

                if (this.photo_filename) {
                    formData.append("photo_filename", this.photo_filename);
                    console.log("Adicionado photo_filename:", this.photo_filename);
                }

                const userId = this.getUserIdFromToken();

                axios.defaults.baseURL = `http://localhost/api`;
                const response = await axios.post(`/user/${userId}/updateProfile`, formData, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem("AccessToken")}`
                    }
                })

                if (response.status === 200) {
                    alert("Alteração de Dados realizada com Sucesso!");
                    const user = await authStore.atualizar(localStorage.getItem("AccessToken"));
                    console.log(user)
                    this.$emit("update-success");
                } else {
                    this.errorMessage = response || "Erro ao Atualizar Dados. Tente novamente.";
                }
            } catch (error) {
                console.error(error);
                this.errorMessage = "Ocorreu um erro ao conectar à API. Tente novamente.";
            }
        },
        handleEditUserCancel(){

        },
        getUserIdFromToken() {
            const token = localStorage.getItem("AccessToken");
            if (token) {
                const payload = JSON.parse(atob(token.split(".")[1])); // Decodifica o payload do token
                return payload.sub; // Retorna o ID do user (sub)
            }
            return null;
        },
        handleFileUpload(event) {
            this.photo_filename = event.target.files[0];
        },
    },
};
</script>

<style scoped></style>