<template>
  <div class="min-h-screen bg-white py-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="relative h-24 bg-gradient-to-r via-black dark:to-gray-800">
          <div class="absolute inset-0 opacity-10">
            <div class="h-full w-full pattern-bg"></div>
          </div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
              <h2 class="card-title" style="color: black;">Dashboard</h2>
            </div>
          </div>
        </div>

        <!-- Conteúdo -->
        <div class="p-6">
          <div v-if="statistics" class="space-y-8">
            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm">Total de Utilizadores</h3>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ statistics.total_users }}</p>
                  </div>
                  <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <i class="bi bi-people text-blue-500 dark:text-blue-300 text-xl"></i>
                  </div>
                </div>
              </div>
              
              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm">Utilizadores Ativos</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ statistics.active_users }}</p>
                  </div>
                  <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                    <i class="bi bi-person-check text-green-500 dark:text-green-300 text-xl"></i>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm">Utilizadores Bloqueados</h3>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ statistics.blocked_users }}</p>
                  </div>
                  <div class="bg-orange-100 dark:bg-orange-900 p-3 rounded-full">
                    <i class="bi bi-person-lock text-orange-500 dark:text-orange-300 text-xl"></i>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm">Utilizadores Removidos</h3>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ statistics.deleted_users }}</p>
                  </div>
                  <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                    <i class="bi bi-person-x text-red-500 dark:text-red-300 text-xl"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                <GChart type="PieChart" :data="chartData" :options="chartOptions" />
              </div>
              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                <GChart type="PieChart" :data="chartData2" :options="chartOptions2" />
              </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                <GChart type="PieChart" :data="chartData3" :options="chartOptions3" />
              </div>

              <div class="bg-white dark:bg-gray-700 rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-6 text-center">
                  <i class="bi bi-person-x-fill text-red-500 dark:text-red-400"></i>
                  Último Utilizador Removido
                </h3>
                <div v-if="statistics.last_deleted_user" class="space-y-6 flex flex-col items-center">
                  <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center mb-2">
                      <i class="bi bi-envelope text-gray-500 dark:text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">E-mail</p>
                    <p class="text-gray-800 dark:text-gray-200 font-medium">{{ statistics.last_deleted_user.email }}</p>
                  </div>
                  
                  <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center mb-2">
                      <i class="bi bi-person-circle text-gray-500 dark:text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nickname</p>
                    <p class="text-gray-800 dark:text-gray-200 font-medium">{{ statistics.last_deleted_user.nickname }}</p>
                  </div>

                  <div class="flex flex-col items-center space-y-2">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center mb-2">
                      <i class="bi" :class="[
                        statistics.last_deleted_user.type === 'C' ? 'bi-person-badge text-blue-500 dark:text-blue-400' : 'bi-person-fill-gear text-purple-500 dark:text-purple-400',
                        'text-xl'
                      ]"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tipo de Utilizador</p>
                    <p class="text-gray-800 dark:text-gray-200 font-medium">
                      {{ statistics.last_deleted_user.type === 'C' ? 'Cliente' : 'Administrador' }}
                    </p>
                  </div>
                </div>
                <div v-else class="text-gray-500 dark:text-gray-400 text-center py-8">
                  Nenhum utilizador removido recentemente
                </div>
              </div>
            </div>
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
        width: '100%',
        height: 400,
        pieHole: 0.4,
        backgroundColor: "transparent",
        colors: ['#4F46E5', '#059669', '#D97706', '#DC2626'],
        titleTextStyle: {
          fontSize: 16,
          bold: true,
          color: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#1F2937',
        },
        legend: {
          position: 'bottom',
          textStyle: {
            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#4B5563',
          }
        },
        pieSliceTextStyle: {
          color: '#ffffff',
        },
        tooltip: {
          showColorCode: true,
          textStyle: {
            color: '#1F2937',
          }
        },
      },

      chartData2: [
        ["Métrica", "Valor"],
      ],
      chartOptions2: {
        title: "Distribuição de Utilizadores por Role",
        width: '100%',
        height: 400,
        pieHole: 0.4,
        backgroundColor: "transparent",
        colors: ['#6366F1', '#0EA5E9'],
        titleTextStyle: {
          fontSize: 16,
          bold: true,
          color: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#1F2937',
        },
        legend: {
          position: 'bottom',
          textStyle: {
            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#4B5563',
          }
        },
        pieSliceTextStyle: {
          color: '#ffffff',
        },
        tooltip: {
          showColorCode: true,
          textStyle: {
            color: '#1F2937',
          }
        },
      },

      chartData3: [
        ["Métrica", "Valor"],
      ],
      chartOptions3: {
        title: "Distribuição de Utilizadores por Estado da Fotografia",
        width: '100%',
        height: 400,
        pieHole: 0.4,
        backgroundColor: "transparent",
        colors: ['#8B5CF6', '#2DD4BF'],
        titleTextStyle: {
          fontSize: 16,
          bold: true,
          color: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#1F2937',
        },
        legend: {
          position: 'bottom',
          textStyle: {
            color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#4B5563',
          }
        },
        pieSliceTextStyle: {
          color: '#ffffff',
        },
        tooltip: {
          showColorCode: true,
          textStyle: {
            color: '#1F2937',
          }
        },
      },
    };
  },
  created() {
    this.fetchStatistics();
    this.updateChartColors();
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateChartColors);
  },
  beforeUnmount() {
    window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateChartColors);
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
        toast.error("Erro ao carregar estatísticas: " + error.message);
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
    updateChartColors() {
      const isDark = document.documentElement.classList.contains('dark');
      const textColor = isDark ? '#E5E7EB' : '#1F2937';
      const legendColor = isDark ? '#D1D5DB' : '#4B5563';

      [this.chartOptions, this.chartOptions2, this.chartOptions3].forEach(options => {
        options.titleTextStyle.color = textColor;
        options.legend.textStyle.color = legendColor;
      });
    },
  },
};
</script>

<style scoped>
.pattern-bg {
  background-image: url('data:image/svg+xml,%3Csvg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%239C92AC" fill-opacity="0.2" fill-rule="evenodd"%3E%3Ccircle cx="3" cy="3" r="3"/%3E%3Ccircle cx="13" cy="13" r="3"/%3E%3C/g%3E%3C/svg%3E');
}

.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient 15s ease infinite;
}

@keyframes gradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>


