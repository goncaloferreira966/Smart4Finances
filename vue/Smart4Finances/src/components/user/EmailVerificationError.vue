<template>
    <div class="flex items-center justify-center min-h-screen">
      <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm text-center">
        <h2 class="text-2xl font-bold mb-6 text-red-600">Link Inválido ou Expirado</h2>
        <p class="mb-4">O link de confirmação não é válido ou já expirou.</p>
        <p class="mb-6">Pode solicitar um novo link na página de login.</p>
        <button @click="goToLogin"
          class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Voltar para Login
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
  