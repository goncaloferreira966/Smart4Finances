<template>
  <div  class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard</h2>

    <div ref="content" class=" space-x-4 mb-6 stats mt-4">
      <div class="flex space-x-4 mb-6 ">
        <input v-model="year" type="number" class="border p-2" placeholder="Ano" />
        <select v-model="month" class="border p-2">
          <option :value="null">Todos os meses</option>
          <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
        </select>
        <button @click="fetchData" class="bg-blue-500 text-white p-2 rounded"> <i class="bi bi-funnel"></i>
          Filtrar</button>
        <button @click="exportToPDF" class="bg-green-500 text-white p-2 rounded"> <i class="bi bi-share-fill"></i>
          Exportar</button>
          <button class="bg-yellow-500 text-white p-2 rounded"> <i class="bi bi-envelope"></i>
            Partilhar por E-mail</button>

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
            :options="chartOptions('Investimnetos Mensais (€)')" />

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
import html2canvas from "html2canvas";
import jsPDF from "jspdf";
import logo from '@/assets/logo.png'; // Imagem no Vue


export default {
  methods: {
    exportToPDF() {
      const content = this.$refs.content;

      // Captura o conteúdo da página como imagem
      html2canvas(content, {
        allowTaint: true,
        useCORS: true, // Permite capturar gráficos do Google Charts (por causa da política de CORS)
        scale: 2, // Aumenta a qualidade da imagem gerada
      }).then((canvas) => {
        const imgData = canvas.toDataURL("image/png");

        // Cria um novo documento PDF
        const doc = new jsPDF({
          orientation: "portrait", // ou "landscape" se preferir
          unit: "mm",
          format: "a4",
        });

        // Definindo a capa preta
        doc.setFillColor(0, 0, 0);
        doc.rect(0, 0, doc.internal.pageSize.width, doc.internal.pageSize.height, 'F');

        // Adicionando o logotipo
        const logoWidth = 250;
        const logoHeight = 200;
        const logoX = (doc.internal.pageSize.width - logoWidth) / 2; // Centralizado
        const logoY = 30;
        doc.addImage(logo, 'PNG', logoX, logoY, logoWidth, logoHeight);

        doc.addPage(); // Isso vai para a segunda página

        const imgWidth = 190; // Defina a largura desejada
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Mantém a proporção

        doc.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);

        // Salva o PDF
        doc.save("Smart4Finances_Relatório_Financeiro.pdf");
      });
    },
  },
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
    const lineChartData = ref([]);

    const fetchData = async () => {
      try {
        const response = await axios.get("/client-statistics", {
          params: { year: year.value, month: month.value },
        });
        incomeData.value = formatChartData(response.data.incomeByMonth, "Mês", "Receita");
        expenseData.value = formatChartData(response.data.expenseByMonth, "Mês", "Despesa");
        investmentData.value = formatChartData(response.data.investmentByMonth, "Mês", "Investimento");
        expenseCategories.value = formatChartData(response.data.expensesByCategory, "Categoria", "Total");
        investmentByType.value = formatChartData(response.data.investmentsByType, "Tipo", "Valor Investido");
        incomeBySource.value = formatChartData(response.data.incomeBySource, "Fonte", "Valor");

        lineChartData.value = formatLineChartData(
          response.data.incomeByMonth,
          response.data.expenseByMonth,
          response.data.investmentByMonth
        );

        console.log(lineChartData.value);

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

    const formatLineChartData = (income, expenses, investments) => {
      const formatted = [["Mês", "Receita", "Despesa", "Investimento"]];

      // Obter todos os meses únicos
      const allMonths = new Set([
        ...Object.keys(income),
        ...Object.keys(expenses),
        ...Object.keys(investments),
      ]);

      // Construir os dados no formato necessário
      allMonths.forEach((month) => {
        formatted.push([
          `Mês ${month}`,
          parseFloat(income[month] || 0),   // Receita ou 0 se não houver
          parseFloat(expenses[month] || 0), // Despesa ou 0 se não houver
          parseFloat(investments[month] || 0) // Investimento ou 0 se não houver
        ]);
      });

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
