<template>
    <nav class="navbar bg-blue-500 p-4 text-#DAA520 shadow-md">
      <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold">
          <i class="bi bi-coin"></i> Smart4Finances
        </h1>
        <ul class="flex space-x-3 mt-2">
          <!-- Se estivermos no formulário de login, exibe o botão de Registar -->
          <li v-if="activeForm === 'login'">
            <button @click="$emit('navigate', 'register')" class="hover:text-gray-200">
              <i class="bi bi-person-bounding-box"></i> Registar
            </button>
          </li>
          <!-- Se estivermos no formulário de registo, exibe o botão de Login -->
          <li v-else-if="activeForm === 'register'">
            <button @click="$emit('navigate', 'login')" class="hover:text-gray-200">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
          </li>
        </ul>
      </div>
    </nav>
  </template>
  
  <script>
  export default {
    name: "NavbarLoginRegister",
    props: {
      // activeForm deve ser "login" ou "register"
      activeForm: {
        type: String,
        required: true,
      },
    },
  };
  </script>
  