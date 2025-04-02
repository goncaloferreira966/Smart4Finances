<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Detalhes do Orçamento</h2>
    <div v-if="budget">
      <p><strong>Data:</strong> {{ formatDate(budget.created_at) }}</p>
      <p><strong>Valor:</strong> {{ budget.limit_amount + " " + coin }}</p>
      <p><strong>Categoria:</strong> {{ budget.category }}</p>

      <GChart type="PieChart" :data="chartData" :options="chartOptions" class="mt-4" />

      <button @click="editBudget" class="w-full mt-4 bg-blue-500 text-white px-4 py-2 rounded">
        <i class="bi bi-pen"></i>
        Editar Orçamento
      </button>
      <br>
      <button @click="backList" class="w-full mt-4 bg-red-500 text-white px-4 py-2 rounded">
        <i class="bi bi-arrow-left-short"></i>
        Voltar
      </button>
    </div>
    <div v-else>
      A carregar...
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from "@/stores/auth";
import { GChart } from 'vue-google-charts';

export default {
  components: { GChart },
  data() {
    return {
      budget: null,
      coin: "",
    };
  },
  props: {
    budgetId: {
      type: Number,
      required: true
    }
  },
  created() {
    this.loadBudget();
    const authStore = useAuthStore();
    this.coin = (authStore.user?.data?.coin);
  },
  methods: {
    loadBudget() {
      axios.get(`/budgets/${this.budgetId}`).then(response => {
        this.budget = response.data;
      }).catch(error => {
        console.error(error);
      });
    },
    formatDate(dateString) {
      const date = new Date(Date.parse(dateString));
      if (isNaN(date)) return 'Data inválida';
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    editBudget() {
      this.$emit('editBudget', this.budget.id);
    },
    backList() {
      this.$emit('BackBudget');
    },

  },
  computed: {
    chartData() {
      if (!this.budget) return [];
      const totalGasto = parseFloat(this.budget.total_expenses_this_month);
      const limite = parseFloat(this.budget.limit_amount);
      const restante = Math.max(0, limite - totalGasto);

      return [
        ['Categoria', 'Valor'],
        ['Total Gasto', totalGasto],
        ['Restante', restante]
      ];
    },
    chartOptions() {
      return {
        title: 'Orçamento Utilizado',
        pieHole: 0.4,
        colors: ['#d14343', '#6fde8d'],
        height: window.innerWidth < 768 ? 300 : 500,
        backgroundColor: 'transparent',
        legend: { position: 'bottom' },
        titleTextStyle: {
          fontSize: 18, // Tamanho da fonte do título
          bold: true
        },
      };
    }
  }
};
</script>
