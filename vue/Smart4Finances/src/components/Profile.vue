<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: lightseagreen;">Perfil</h2>
    <div class="card hover-card">
      <div class="card-body">
        <div v-if="user">
          <h3 class="card-subtitle mb-2 text-muted mt-4"><i class="bi bi-person"></i> {{ user.data.name }}</h3>

          <img v-show="user && user.data.photoFileName" :src="`http://localhost${user.data.photoFileName}`"
            alt="User Photo" class="card-img-top rounded-circle mt-4" />

          <p class="card-text mt-4"><strong><i class="bi bi-envelope"></i> Email:</strong> {{ user.data.email }}</p>

          <p class="card-text mt-4"><strong><i class="bi bi-person-circle"></i> Nickname:</strong> {{ user.data.nickname
            }}</p>

          <p class="card-text mt-4" v-if="user.data.type == 'C'"><i class="bi bi-coin"></i><strong> Type:</strong>
            Cliente</p>

          <p class="card-text mt-4" v-else><i class="bi bi-person-fill-gear"></i><strong> Type:</strong> Administrador
          </p>

          <p v-if="userRole !== 'A'" class="card-text mt-4"><strong><i class="bi bi-coin"></i> Saldo:</strong> {{
                user.data.value }}€</p>

          <div class="container mt-2">
            <div class="col-md-12 mt-4">
              <button @click="handleEditUser()" style="background-color: lightseagreen; border: 0; color: white;"
                type="submit" class="btn btn-info w-100"><i class="bi bi-pencil"></i> Edit Profile</button>
            </div>
            <div v-if="userRole !== 'A'" @click="handleDeleteUser" class="col-md-12 mt-4 mb-2">
              <button @click="deleteUser()" style="border: 0; color: white;" type="submit"
                class="btn btn-danger w-100"><i class="bi bi-trash-fill"></i> Delete Profile</button>
            </div>
          </div>
        </div>
        <div v-else>
          <p>Loading user data...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from "@/stores/auth"; // Importa a store Pinia
export default {
  data() {
    return {
      user: null,
      userRole: "", // Armazena o tipo de user
    };
  },
  created() {
    this.fetchUser();
  },
  mounted() {
    // Obtém a role do user do localStorage
    const authStore = useAuthStore();

    // Obtém a role do user do localStorage
    this.userRole = authStore.user.data.type;

  },
  methods: {
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1])); // Decodifica o payload do token
        return payload.sub; // Retorna o ID do user (sub)
      }
      return null;
    },
    async fetchUser() {
      try {
        const userId = this.getUserIdFromToken();
        axios.defaults.baseURL = `http://localhost/api`;
        const token = localStorage.getItem('AccessToken');
        const response = await axios.get(`/users/${userId}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        this.user = response.data; // Assumindo que os dados do user estão na resposta direta
      } catch (error) {
        console.error('Error fetching user data:', error);
      }
    },
    handleEditUser() {
      this.$emit("editer");
    },
    async deleteUser() {
      if (confirm('Tem certeza que deseja remover a sua conta?')) {
        const nicknameInput = prompt('Por favor, insira o seu nickname para confirmar a exclusão da conta:');

        // Obtém o nickname do localStorage
        const authStore = useAuthStore();
        const storedNickname = authStore.user.data.nickname;

        // Verifica se o nickname inserido corresponde ao do localStorage
        if (nicknameInput !== storedNickname) {
          alert('O nickname inserido não corresponde ao seu perfil. Operação cancelada.');
          return; // Aborta o processo de exclusão
        }
        try {
          const token = localStorage.getItem('AccessToken');
          const userId = this.getUserIdFromToken();

          await axios.delete(`/users/${userId}`, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });

          alert("Conta Removida com Sucesso.");
          localStorage.removeItem("AccessToken");
          this.$emit("logout"); // Emite o evento de logout para redirecionamento

        } catch (err) {
          this.error = 'Erro ao excluir Utilizador';
          console.error(err);
        }
      }
    },
  },
};
</script>

<style scoped>
.card {
  margin: 20px auto;
  width: 100%;
  max-width: 400px;
  /* Limitar a largura do card */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: 0.3s;
}

.card-img-top {
  width: 200px;
  height: 200px;
  /* Definindo altura para a imagem */
  object-fit: cover;
  /* Manter a imagem dentro do card sem distorcer */
  margin: 0 auto;
  /* Centralizando a imagem */
}


.card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.pagination button {
  margin: 0 5px;
}

.hover-card {
  transition: transform 0.3s ease;
  /* Transição suave */
}

.hover-card:hover {
  transform: scale(1.05);
  /* Ampliação do card em 5% */
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
  /* Efeito de sombra */
}
</style>