<template>
 <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard Financeiro</h2>
    
    <div class="flex space-x-4 mb-6">
      <input v-model="year" type="number" class="border p-2" placeholder="Ano" />
      <select v-model="month" class="border p-2">
        <option :value="null">Todos os meses</option>
        <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
      </select>
      <button @click="fetchData" class="bg-blue-500 text-white p-2 rounded">Filtrar</button>
    </div>
    
    <GChart
      v-if="incomeData.length"
      type="ColumnChart"
      :data="incomeData"
      :options="chartOptions('Receitas Mensais')"
    />
    
    <GChart
      v-if="expenseData.length"
      type="ColumnChart"
      :data="expenseData"
      :options="chartOptions('Despesas Mensais')"
    />
    
    <GChart
      v-if="expenseCategories.length"
      type="PieChart"
      :data="expenseCategories"
      :options="chartOptions('Distribuição de Despesas por Categoria')"
    />

    <!-- Adicionando gráficos de investimentos por tipo -->
    <GChart
      v-if="investmentByType.length"
      type="PieChart"
      :data="investmentByType"
      :options="chartOptions('Distribuição de Investimentos por Tipo')"
    />

    <!-- Adicionando gráficos de rendimento por fonte -->
    <GChart
      v-if="incomeBySource.length"
      type="PieChart"
      :data="incomeBySource"
      :options="chartOptions('Distribuição de Rendimento por Fonte')"
    />
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
    const expenseData = ref([]);
    const expenseCategories = ref([]);
    const investmentByType = ref([]);
    const incomeBySource = ref([]);

    const fetchData = async () => {
      try {
        const response = await axios.get("/client-statistics", {
          params: { year: year.value, month: month.value },
        });
        console.log(response.data); 
        incomeData.value = formatChartData(response.data.incomeByMonth, "Mês", "Receita");
        console.log(incomeData.value); 
        expenseData.value = formatChartData(response.data.expenseByMonth, "Mês", "Despesa");
        expenseCategories.value = formatChartData(response.data.expensesByCategory, "Categoria", "Total");
        investmentByType.value = formatChartData(response.data.investmentsByType, "Tipo", "Valor Investido");
        console.log( investmentByType.value)
        incomeBySource.value = formatChartData(response.data.incomeBySource, "Fonte", "Valor");
      } catch (error) {
        console.error("Erro ao buscar dados", error);
      }
    };

    const formatChartData = (data, label1, label2) => {
      const formatted = [[label1, label2]];
      for (const key in data) {
        formatted.push([key, data[key]]);
      }
      return formatted;
    };

    const chartOptions = (title) => ({
      title,
      width: "100%",
      height: 400,
      legend: { position: "bottom" },
    });

    onMounted(fetchData);

    return { 
      year, 
      month, 
      incomeData, 
      expenseData, 
      expenseCategories, 
      investmentByType, 
      incomeBySource, 
      fetchData, 
      chartOptions 
    };
  },
};
</script>
