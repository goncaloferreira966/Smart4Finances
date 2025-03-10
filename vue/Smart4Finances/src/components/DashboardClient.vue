<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard Financeiro</h2>

    <div class=" space-x-4 mb-6 stats mt-4">
      <div class="flex space-x-4 mb-6 ">
        <input v-model="year" type="number" class="border p-2" placeholder="Ano" />
        <select v-model="month" class="border p-2">
          <option :value="null">Todos os meses</option>
          <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
        </select>
        <button @click="fetchData" class="bg-blue-500 text-white p-2 rounded"> <i class="bi bi-funnel"></i>
          Filtrar</button>
        <button class="bg-green-500 text-white p-2 rounded"> <i class="bi bi-share-fill"></i> Exportar</button>

      </div>

      <div class="row">
        <div class="col-md-6">
          <GChart v-if="incomeData.length" type="ColumnChart" :data="incomeData"
            :options="chartOptions('Receitas Mensais (€)')" />

        </div>

        <div class="col-md-6">
          <GChart v-if="expenseData.length" type="ColumnChart" :data="expenseData"
            :options="chartOptions('Despesas Mensais (€)')" />

        </div>
      </div>

      <div class="row mt-5">
        <div class="col-md-6">
          <GChart v-if="investmentData.length" type="ColumnChart" :data="investmentData"
            :options="chartOptions('Despesas Mensais (€)')" />

        </div>

        <div class="col-md-6">
          <GChart v-if="incomeBySource.length" type="PieChart" :data="incomeBySource"
          :options="chartOptions('Distribuição de Rendimento por Fonte (%)')" />

        </div>
      </div>

      <div class="row mt-5">
        <div class="col-md-6">
         
          <GChart v-if="expenseCategories.length" type="PieChart" :data="expenseCategories"
          :options="chartOptions('Distribuição de Despesas por Categoria (%)')" />
        </div>

        <div class="col-md-6">
          <GChart v-if="investmentByType.length" type="PieChart" :data="investmentByType"
        :options="chartOptions('Distribuição de Investimentos por Tipo (%)')" />

        </div>
      </div> 
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { GChart } from "vue-google-charts";
import axios from "axios";

export default {
  components: { GChart },
  setup() {
    const year = ref(new Date().getFullYear());
    const month = ref(null);
    const incomeData = ref([]);
    const investmentData = ref([]);
    const expenseData = ref([]);
    const expenseCategories = ref([]);
    const investmentByType = ref([]);
    const incomeBySource = ref([]);

    const fetchData = async () => {
      try {
        const response = await axios.get("/client-statistics", {
          params: { year: year.value, month: month.value },
        });
        incomeData.value = formatChartData(response.data.incomeByMonth, "Mês", "Receita");
        expenseData.value = formatChartData(response.data.expenseByMonth, "Mês", "Despesa");
        investmentData.value =  formatChartData(response.data.investmentByMonth, "Mês", "Investimento");
        expenseCategories.value = formatChartData(response.data.expensesByCategory, "Categoria", "Total");
        investmentByType.value = formatChartData(response.data.investmentsByType, "Tipo", "Valor Investido");
        incomeBySource.value = formatChartData(response.data.incomeBySource, "Fonte", "Valor");
      } catch (error) {
        console.error("Erro ao selecionar dados", error);
      }
    };

    const formatChartData = (data, label1, label2) => {
      const formatted = [[label1, label2]];
      for (const key in data) {
        formatted.push([key, parseFloat(data[key])]); // Converte os valores para número
      }
      return formatted;
    };

    const chartOptions = (title) => ({
      title,
      width: '100%',
      height: window.innerWidth < 768 ? 300 : 500, // Altura dinâmica baseada na largura do ecra
      legend: { position: "bottom" },
      is3D: false,
      backgroundColor: "transparent", // Fundo transparente
    });

    onMounted(fetchData);

    return {
      year,
      month,
      incomeData,
      expenseData,
      investmentData,
      expenseCategories,
      investmentByType,
      incomeBySource,
      fetchData,
      chartOptions
    };
  },
};
</script>
