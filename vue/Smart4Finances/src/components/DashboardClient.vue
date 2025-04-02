<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard</h2>
    <div id="content" ref="content" class=" space-x-4 mb-6 stats mt-4">
      <div class="flex space-x-4 mb-6 ">
        <input v-model="year" type="number" class="border p-2" placeholder="Ano" />
        <select v-model="month" class="border p-2">
          <option :value="null">Todos os meses</option>
          <option value="1">Janeiro</option>
          <option value="2">Fevereiro</option>
          <option value="3">Março</option>
          <option value="4">Abril</option>
          <option value="5">Maio</option>
          <option value="6">Junho</option>
          <option value="7">Julho</option>
          <option value="8">Agosto</option>
          <option value="9">Setembro</option>
          <option value="10">Outubro</option>
          <option value="11">Novembro</option>
          <option value="12">Dezembro</option>
        </select>
        <button @click="fetchData" class="bg-blue-500 text-white p-2 rounded"> <i class="bi bi-funnel"></i>
          Filtrar</button>
        <button @click="exportToPDF" class="bg-green-500 text-white p-2 rounded"> <i class="bi bi-share-fill"></i>
          Exportar PDF</button>
        <button @click="sendEmail" class="bg-yellow-500 text-white p-2 rounded"> <i class="bi bi-envelope"></i>
          Partilhar PDF por E-mail</button>

      </div>

      <div class="row">
        <div class="col-md-12">
          <GChart type="LineChart" :data="lineChartData"
            :options="chartOptions(chartTitles.lineChart)" />
        </div>
      </div>

      <div class="row mt-1">
        <div class="col-md-6">
          <GChart v-if="incomeData.length" type="AreaChart" :data="incomeData"
          :options="chartOptions(chartTitles.incomeChart)" />

        </div>

        <div class="col-md-6">
          <GChart v-if="expenseData.length" type="AreaChart" :data="expenseData"
          :options="chartOptions(chartTitles.expenseChart)" />

        </div>
      </div>

      <div class="row mt-1">
        <div class="col-md-6">
          <GChart v-if="investmentData.length" type="AreaChart" :data="investmentData"
          :options="chartOptions(chartTitles.investmentChart)" />

        </div>

        <div class="col-md-6">
          <GChart v-if="incomeBySource.length" type="PieChart" :data="incomeBySource"
            :options="chartOptions('Distribuição de Rendimento por Fonte (%)')" />

        </div>
      </div>

      <div class="row mt-1">
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
import { ref, computed, onMounted } from "vue";
import { GChart } from "vue-google-charts";
import axios from "axios";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";
import logo from '@/assets/logo.png'; // Imagem no Vue
import { toast } from 'vue3-toastify';
import { useAuthStore } from "@/stores/auth";

export default {
  methods: {
    exportToPDF() {
      const content = this.$refs.content;

      // Captura o conteúdo da página como imagem
      html2canvas(content, {
        allowTaint: true,
        useCORS: true, // Permite capturar gráficos do Google Charts (por causa da política de CORS)
        scale: 2, // Aumenta a qualidade da imagem gerada
        //(se for para enviar email, meter scale 1 porque o 2 é demasiado grande torna o ficheiro grande)
      }).then((canvas) => {
        const imgData = canvas.toDataURL("image/png");

        // Cria um novo documento PDF
        const doc = new jsPDF({
          orientation: "portrait", // ou "landscape" se preferir
          unit: "mm",
          format: "a3",
        });

        // Definindo a capa preta
        doc.setFillColor(0, 0, 0);
        doc.rect(0, 0, doc.internal.pageSize.width, doc.internal.pageSize.height, 'F');

        // Adicionando o logotipo
        const logoWidth = 250;
        const logoHeight = 200;
        const logoX = (doc.internal.pageSize.width - logoWidth) / 2; // Centralizado
        const logoY = 100; //Distancia do logo smart4fiances do topo da página da capa (como se fosse mt-x)
        doc.addImage(logo, 'PNG', logoX, logoY, logoWidth, logoHeight);

        doc.addPage(); // Isso vai para a segunda página

        const imgWidth = 220; // Define a largura desejada
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Mantém a proporção

        doc.addImage(imgData, "PNG", 40, 40, imgWidth, imgHeight);

        // Guarda o PDF
        doc.save("Smart4Finances_Relatório_Financeiro.pdf");
        toast.success("Relatório descarregado com sucesso!");

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
    const authStore = useAuthStore();
    const coin = ref(authStore.user?.data?.coin);  

    const chartTitles = computed(() => ({
    lineChart: `Receitas, Despesas e Investimentos Mensais (${coin.value})`,
    incomeChart: `Receitas Mensais (${coin.value})`,
    expenseChart: `Despesas Mensais (${coin.value})`,
    investmentChart: `Investimentos Mensais (${coin.value})`
  }));

    const fetchData = async () => {
      try {
        const response = await axios.get("/client-statistics", {
          params: { year: year.value, month: month.value },
        });
        incomeData.value = formatChartData(response.data.incomeByMonth, "Mês", "Receita");
        expenseData.value = formatChartData(response.data.expenseByMonth, "Mês", "Despesa");
        investmentData.value = formatChartData(response.data.investmentByMonth, "Mês", "Investimento");
        expenseCategories.value = formatPieChartData(response.data.expensesByCategory, "Categoria", "Total");
        investmentByType.value = formatPieChartData(response.data.investmentsByType, "Tipo", "Valor Investido");
        incomeBySource.value = formatPieChartData(response.data.incomeBySource, "Fonte", "Valor");

        lineChartData.value = formatLineChartData(
          response.data.incomeByMonth,
          response.data.expenseByMonth,
          response.data.investmentByMonth
        );
      } catch (error) {
        console.error("Erro ao selecionar dados", error);
      }
    };

    const formatChartData = (data, label1, label2) => {
      const formatted = [[label1, label2]];

      if (Object.keys(data).length === 0) {
        formatted.push(["Sem dados", 0]); // Adiciona um valor padrão
      } else {
        for (const key in data) {
          formatted.push([key, parseFloat(data[key])]); // Converte os valores para número
        }
      }

      return formatted;
    };

    const formatPieChartData = (data, label1, label2) => {
      const formatted = [[label1, label2]];
      for (const key in data) {
        formatted.push([key, parseFloat(data[key])]); // Converte os valores para número
      }
      return formatted;
    };

    const formatLineChartData = (income, expenses, investments) => {
      const formatted = [["Mês", "Receita", "Despesa", "Investimento"]];

      const allMonths = [...new Set([
        ...Object.keys(income),
        ...Object.keys(expenses),
        ...Object.keys(investments),
      ])].map(Number).sort((a, b) => a - b);

      if (allMonths.length === 0) {
        formatted.push(["Sem dados", 0, 0, 0]); // Placeholder quando não há dados
      } else {
        allMonths.forEach((month) => {
          formatted.push([
            `Mês ${month}`,
            parseFloat(income[month] || 0),
            parseFloat(expenses[month] || 0),
            parseFloat(investments[month] || 0)
          ]);
        });
      }

      return formatted;
    };

    const chartOptions = (title) => ({
      title: `${title}`,
      width: '100%',
      height: window.innerWidth < 768 ? 300 : 500, // Altura dinâmica baseada na largura do ecra
      legend: { position: "bottom" },
      is3D: false,
      pieHole: 0.4,  // Isso cria o efeito de "donut"
      backgroundColor: "transparent", // Fundo transparente
    });

    const sendEmail = async () => {
      try {
        const content = document.querySelector("#content");
        html2canvas(content, {
          allowTaint: true,
          useCORS: true,
          //1.2 está no limite máximo do que o gmail permite de mb por email de forma gratuita
          scale: 1.2, // Para envio por e-mail, manter a escala menor para reduzir o tamanho do arquivo
        }).then((canvas) => {
          const imgData = canvas.toDataURL("image/png");
          const doc = new jsPDF({
            orientation: "portrait",
            unit: "mm",
            format: "a3",
          });

          // Criar capa preta com logotipo
          doc.setFillColor(0, 0, 0);
          doc.rect(0, 0, doc.internal.pageSize.width, doc.internal.pageSize.height, "F");

          const logoWidth = 250;
          const logoHeight = 200;
          const logoX = (doc.internal.pageSize.width - logoWidth) / 2;
          const logoY = 100;
          doc.addImage(logo, "PNG", logoX, logoY, logoWidth, logoHeight);

          doc.addPage();

          const imgWidth = 220;
          const imgHeight = (canvas.height * imgWidth) / canvas.width;
          doc.addImage(imgData, "PNG", 40, 40, imgWidth, imgHeight);

          const pdfBlob = doc.output("blob"); // Gera um blob do PDF

          const formData = new FormData();
          formData.append("file", pdfBlob, "Smart4Finances_Relatório_Financeiro.pdf");

          axios.post("/send-email", formData, {
            headers: { "Content-Type": "multipart/form-data" },
          })
            .then(() => {
              toast.success("E-mail enviado com sucesso!");
            })
            .catch(() => {
              toast.error("Erro ao enviar e-mail.");
            });
        });
      } catch (error) {
        toast.error("Erro ao enviar o E-mail");
        console.error("Erro ao enviar email", error);
      }
    };


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
      chartOptions,
      formatPieChartData,
      lineChartData,
      sendEmail,
      coin,
      chartTitles,
    };
  },
};
</script>
