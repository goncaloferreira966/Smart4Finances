<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard Administrativo</h2>
    <div v-if="statistics" class="stats mt-4">
      <div class="row">
        <div class="col-md-6">
          <GChart type="PieChart" :data="chartData" :options="chartOptions" />
        </div>
        <div class="col-md-6">
          <GChart type="PieChart" :data="chartData2" :options="chartOptions2" />
        </div>
      </div>

      <div class="row mt-5">

        <div class="col-md-6">
          <GChart type="PieChart" :data="chartData3" :options="chartOptions3" />
        </div>

        <div class="col-md-6 mt-5">
          <p>Último Utilizador Apagado</p>
          <div v-if="statistics.last_deleted_user">

            <p class="card-text mt-4"><strong><i class="bi bi-envelope"></i> E-mail:</strong>
              {{ statistics.last_deleted_user.email }}</p>
            <p class="card-text mt-4"><strong><i class="bi bi-person-circle"></i> Nickname:</strong> {{
              statistics.last_deleted_user.nickname }}</p>

            <p class="card-text mt-4" v-if="statistics.last_deleted_user.type === 'C'">
              <i class="bi bi-person-badge"></i><strong> Tipo de Utilizador:</strong> Cliente
            </p>
            <p class="card-text mt-4" v-else>
              <i class="bi bi-person-fill-gear"></i><strong> Tipo de Utilizador:</strong> Administrador
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { useAuthStore } from "@/stores/auth";
import { GChart } from "vue-google-charts";

export default {
  components: { GChart },
  data() {
    return {
      apiDomain: import.meta.env.VITE_API_DOMAIN,
      statistics: null,
      //Gráfico para o estado dos users
      chartData: [
        ["Métrica", "Valor"],
      ],
      chartOptions: {
        title: "Distribuição de Utilizadores por Estado",
        width: 700,
        height: 400,
        pieHole: 0.4,  // Isso cria o efeito de "donut"
        backgroundColor: "transparent", // Fundo transparente
        is3D: false,
        colors: ['#2196F3', '#FFC107', '#9C27B0', '#FF5722'], 
      },

      chartData2: [
        ["Métrica", "Valor"],
      ],
      chartOptions2: {
        title: "Distribuição de Utilizadores por Role",
        width: 700,
        height: 400,
        pieHole: 0.4,  // Isso cria o efeito de "donut"
        backgroundColor: "transparent", // Fundo transparente
        is3D: false,
        colors: ['#4CAF50', '#FF9800'],
      },

      chartData3: [
        ["Métrica", "Valor"],
      ],
      chartOptions3: {
        title: "Distribuição de Utilizadores por Estado da Fotografia de Perfil",
        width: 700,
        height: 400,
        pieHole: 0.4,  // Isso cria o efeito de "donut"
        backgroundColor: "transparent", // Fundo transparente
        is3D: false,
        colors: ['#3F51B5', '#E91E63'],
      },
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
        //Gráfico para o estado dos users
        this.chartData = [
          ["Métrica", "Valor"],
          ["Total de Utilizadores", this.statistics.total_users],
          ["Utilizadores Ativos", this.statistics.active_users],
          ["Utilizadores Bloqueados", this.statistics.blocked_users],
          ["Utilizadores Apagados", this.statistics.deleted_users]
        ];

        this.chartData2 = [
          ["Métrica", "Valor"],
          ["Total de Utilizadores Administradores", this.statistics.adminCount],
          ["Total de Utilizadores Clientes", this.statistics.clientCount]
        ];

        this.chartData3 = [
          ["Métrica", "Valor"],
          ["Total de Utilizadores com Fotografia", this.statistics.users_with_avatar],
          ["Total de Utilizadores sem Fotografia", this.statistics.users_without_avatar]
        ];

        

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
