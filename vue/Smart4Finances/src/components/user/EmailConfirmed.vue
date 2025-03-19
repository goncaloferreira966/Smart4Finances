<template>
    <div class="flex items-center justify-center min-h-screen">
      <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm text-center">
        <h2 class="text-2xl font-bold mb-6 text-green-600">E-mail Confirmado!</h2>
        <p class="mb-4">A sua conta foi confirmada com sucesso. Já pode fazer login.</p>
        <button @click="goToLogin"
          class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Ir para Login
        </button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    methods: {
      goToLogin() {
        this.$emit('navigate', 'login');
      }
    }
  };
  </script>
  