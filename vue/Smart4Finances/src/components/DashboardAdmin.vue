<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard Administrativo</h2>
    <div v-if="statistics" class="stats mt-4">
      <div class="row">
      <div class="col-md-6">

        <p>Total de Utilizadores: {{ statistics.total_users }}</p>
        <p>Utilizadores Ativos: {{ statistics.active_users }}</p>
        <p>Utilizadores Bloqueados: {{ statistics.blocked_users }}</p>
        <p>Utilizadores Apagados: {{ statistics.deleted_users }}</p>
      </div>
      <div class="col-md-6">
        <h4>Tipos de Utilizadores</h4>
        <ul>
          <li v-for="type in statistics.user_types" :key="type.type">
            {{ type.type }}: {{ type.count }}
          </li>
        </ul>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-md-6">
        <p>Utilizadores com Foto de Perfil: {{ statistics.users_with_avatar }}</p>
        <p>Utilizadores sem Foto de Perfil: {{ statistics.users_without_avatar }}</p>
      </div>
      <div class="col-md-6">
        <h4>Último Utilizador Apagado</h4>
      <p v-if="statistics.last_deleted_user">
        {{ statistics.last_deleted_user.name }} ({{ statistics.last_deleted_user.nickname }})
      </p>
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
      apiDomain: import.meta.env.VITE_API_DOMAIN,
      statistics: null,
    };
  },
  created() {
    this.fetchStatistics();
  },
  mounted() {
    const authStore = useAuthStore();
    this.userRole = authStore.user.data.type;
  },
  methods: {
    async fetchStatistics() {
      try {
        const response = await axios.get(`/admin-statistics`);
        this.statistics = response.data;
      } catch (error) {
        toast.error("Erro ao carregar estatísticas.");
      }
    },
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1]));
        return payload.sub;
      }
      return null;
    },
  },
};
</script>

<style scoped>
.stats {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}
</style>
