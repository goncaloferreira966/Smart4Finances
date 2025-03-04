<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Perfil</h2>
    <div class="card hover-card">
      <div class="card-body">
        <div v-if="user">
          <h3 class="card-subtitle mb-2 text-muted mt-4">
            <i class="bi bi-person"></i> {{ user.data.name }}
          </h3>
          <img v-show="user && user.data.photoFileName" :src="`http://localhost${user.data.photoFileName}`"
               alt="User Photo" class="card-img-top rounded-circle mt-4" />
          <p class="card-text mt-4"><strong><i class="bi bi-envelope"></i> E-mail:</strong> {{ user.data.email }}</p>
          <p class="card-text mt-4"><strong><i class="bi bi-person-circle"></i> Nickname:</strong> {{ user.data.nickname }}</p>
          <p class="card-text mt-4" v-if="user.data.type === 'C'">
            <i class="bi bi-person-badge"></i> <strong>Tipo de Utilizador:</strong> Cliente
          </p>
          <p class="card-text mt-4" v-else>
            <i class="bi bi-person-fill-gear"></i> <strong>Tipo de Utilizador:</strong> Administrador
          </p>
          <p v-if="userRole !== 'A'" class="card-text mt-4">
            <strong><i class="bi bi-coin"></i> Saldo:</strong> {{ user.data.value }}€
          </p>
          <div class="container mt-2">
            <div class="col-md-12 mt-4">
              <button @click="handleEditUser" style="background-color: black; border: 0; color: #DAA520;"
                      type="button" class="btn btn-info w-100">
                <i class="bi bi-pencil"></i> Editar Perfil
              </button>
            </div>
            <div v-if="userRole !== 'A'" class="col-md-12 mt-4 mb-2">
              <button @click="deleteUser" style="border: 0; color: #DAA520;" type="button"
                      class="btn btn-danger w-100">
                <i class="bi bi-trash-fill"></i> Apagar Perfil
              </button>
            </div>
          </div>
        </div>
        <div v-else>
          <p>A Carregar Dados do Utilizador...</p>
        </div>
      </div>
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
      user: null,
      userRole: "",
      error: ""
    };
  },
  created() {
    this.fetchUser();
  },
  mounted() {
    const authStore = useAuthStore();
    this.userRole = authStore.user.data.type;
  },
  methods: {
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1]));
        return payload.sub;
      }
      return null;
    },
    async fetchUser() {
      try {
        const userId = this.getUserIdFromToken();
        const token = localStorage.getItem('AccessToken');
        const response = await axios.get(`/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.user = response.data;
      } catch (error) {
        console.error('Erro ao buscar dados do usuário:', error);
        toast.error("Erro ao buscar dados do usuário.");
        this.error = "Erro ao buscar dados do usuário.";
      }
    },
    handleEditUser() {
      this.$router.push({ name: 'EditUser' });
    },
    async deleteUser() {
      if (confirm('Tem certeza que deseja remover a sua conta?')) {
        const nicknameInput = prompt('Por favor, insira o seu Nickname para confirmar a exclusão da conta:');
        const authStore = useAuthStore();
        const storedNickname = authStore.user.data.nickname;
        if (nicknameInput !== storedNickname) {
          alert('O Nickname inserido não corresponde ao seu perfil. Operação cancelada.');
          return;
        }
        try {
          const token = localStorage.getItem('AccessToken');
          const userId = this.getUserIdFromToken();
          await axios.delete(`/users/${userId}`, {
            headers: { Authorization: `Bearer ${token}` }
          });
          toast.success("Conta removida com sucesso!");
          localStorage.removeItem("AccessToken");
          this.$router.push({ name: 'Login' });
        } catch (err) {
          console.error(err);
          toast.error("Erro ao excluir o usuário.");
          this.error = 'Erro ao excluir Utilizador';
        }
      }
    },
  },
};
</script>
