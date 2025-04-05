<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Detalhes do Investimento</h2>
    <div v-if="investment">
      <p><strong>Data:</strong> {{ formatDate(investment.created_at) }}</p>
      <p><strong>Valor:</strong> {{ investment.amount + " " + coin }}</p>
      <p><strong>Tipo:</strong> {{ investment.type }}</p>
      <p><strong>ROI:</strong> {{ investment.roi + " %" }}</p>

      <label for="years">Projeção de Crescimento (Anos):</label>
      <input id="years" v-model.number="years" type="number" min="1" max="50" class="w-full mt-2 p-2 border rounded" />

      <GChart type="LineChart" :data="chartData" :options="chartOptions" style="width: 100%; margin-top: 25px;" />


      <button @click="editInvestment" class="w-full mt-4 bg-blue-500 text-white px-4 py-2 rounded">
        <i class="bi bi-pen"></i>
        Editar Investimento
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
      investment: null,
      coin: "",
      years: 10,
    };
  },
  props: {
    investmentId: {
      type: Number,
      required: true
    }
  },
  created() {
    this.loadInvestment();
    const authStore = useAuthStore();
    this.coin = (authStore.user?.data?.coin);
  },
  methods: {
    loadInvestment() {
      axios.get(`/investments/${this.investmentId}`).then(response => {
        this.investment = response.data;
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
    editInvestment() {
      this.$emit('editInvestment', this.investment.id);
    },
    backList() {
      this.$emit('BackInvestment');
    },

  },
  computed: {
    chartData() {
      const data = [['Ano', 'Projeção']];
      let valorAtual = parseFloat(this.investment?.amount || 0);
      const roi = parseFloat(this.investment?.roi || 0) / 100;

      for (let i = 0; i <= this.years; i++) {
        data.push([`${i}`, valorAtual]);
        valorAtual *= (1 + roi);
      }
      return data;
    },
    chartOptions() {
      return {
        title: 'Projeção de Crescimento',
        curveType: 'function',
        legend: { position: 'bottom' },
        backgroundColor: '',
        height: window.innerWidth < 768 ? 300 : 500,
        hAxis: { title: 'Anos' },
        vAxis: { title: `Valor (${this.coin})` },
        titleTextStyle: {
          fontSize: 18, // Tamanho da fonte do título
          bold: true
        },
      };
    }
  }
};
</script>
