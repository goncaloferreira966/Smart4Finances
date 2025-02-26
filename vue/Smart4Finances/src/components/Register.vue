<template>
    <div class="flex items-center justify-center min-h-screen">
        <div class="register-container bg-white p-8 rounded-lg shadow-lg w-full max-w-xs">
            <h2 style="color: lightseagreen;" class="text-2xl font-bold mb-6 text-center">Registo</h2>
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
                    <label for="photo_filename" class="block text-gray-700 font-semibold mb-2">Fotografia de
                        Perfil</label>
                    <input type="file" id="photo_filename" @change="handleFileUpload" accept=".png, .jpeg, .jpg"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" id="password" v-model="password"
                        class="form-control w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required />
                </div>
                <button style="background-color: lightseagreen; border-color: lightseagreen;" type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Registar
                </button>
            </form>
            <p v-if="errorMessage" class="text-red-500 text-sm mt-4">{{ errorMessage }}</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

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
        async handleRegister() {
            if (this.name && this.email && this.nickname && this.password) {
                try {
                    const formData = new FormData();
                    formData.append("name", this.name);
                    formData.append("email", this.email);
                    formData.append("nickname", this.nickname);
                    formData.append("password", this.password);

                    // Verifica se uma foto foi selecionada
                    if (this.photo_filename) {
                        formData.append("photo_filename", this.photo_filename);
                    }

                    const response = await fetch(`http://localhost/api/usersPost`, {
                        method: "POST",
                        body: formData,
                    });

                    const result = await response.json();

                    if (response.ok) {
                        alert("Registo realizado com sucesso! Já pode fazer Login!");
                        // Redireciona ou executa outra ação
                        this.$emit("register-success", result);
                    } else {
                        this.errorMessage = result.message || "Erro ao registrar. Tente novamente.";
                    }
                } catch (error) {
                    console.error(error);
                    this.errorMessage = "Ocorreu um erro ao conectar à API. Tente novamente.";
                }
            } else {
                this.errorMessage = "Preencha todos os campos corretamente.";
            }
        },
        handleFileUpload(event) {
            this.photo_filename = event.target.files[0];
        },
    },
};
</script>

<style scoped>

</style>