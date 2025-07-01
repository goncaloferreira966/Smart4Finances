<template>
  <div class="container mx-auto p-6 shadow-md rounded-lg mb-10" style="margin-top: 7vh; margin-bottom: 7vh;">
    <h2 class="text-2xl font-bold mb-6">Dashboard Financeiro</h2>
    
    <div id="content" ref="content" class="stats">
      <!-- Filtros responsivos com design melhorado -->
      <div class="filters-container mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div class="w-full">
            <label class="block mb-2 font-medium">Ano:</label>
            <input v-model="year" type="number" 
                  class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                  placeholder="Ano" />
          </div>
          
          <div class="w-full">
            <label class="block mb-2 font-medium">Mês:</label>
            <select v-model="month" 
                   class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
          </div>
          
          <div class="flex items-end">
            <button @click="fetchData" 
                   class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors flex items-center justify-center"> 
              <i class="bi bi-funnel mr-2"></i> Filtrar
            </button>
          </div>
          
          <div class="flex items-end">
            <button @click="exportToPDF" 
                   class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors flex items-center justify-center"> 
              <i class="bi bi-share-fill mr-2"></i> Exportar PDF
            </button>
          </div>
          
          <div class="flex items-end">
            <button @click="sendEmail" 
                   class="w-full bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-colors flex items-center justify-center"> 
              <i class="bi bi-envelope mr-2"></i> Partilhar PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Cards com estatísticas principais -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card stat-card-income p-5 rounded-lg shadow-sm border"
             :style="{ 
               'background-color': isDarkMode ? '#071329' : '#ebf5ff',
               'border-color': isDarkMode ? '#1d4ed8' : '#bfdbfe',
               'box-shadow': isDarkMode ? '0 0 15px rgba(37, 99, 235, 0.2)' : 'initial'
             }">
          <h3 class="text-lg font-semibold mb-2 flex items-center"
              :style="{ 'color': isDarkMode ? '#f8fafc' : 'initial' }">
            <i class="bi bi-cash-coin mr-2" 
               :style="{ 'color': isDarkMode ? '#60a5fa' : '#3b82f6' }"></i> Receitas
          </h3>
          <p class="text-2xl font-bold"
             :style="{ 
               'color': isDarkMode ? '#f8fafc' : 'initial',
               'text-shadow': isDarkMode ? '0 1px 3px rgba(0, 0, 0, 0.5)' : 'initial'
             }">
            {{ getTotalValue(incomeData) }} {{ coin }}
          </p>
        </div>
        
        <div class="stat-card stat-card-expense p-5 rounded-lg shadow-sm border"
             :style="{ 
               'background-color': isDarkMode ? '#2c0303' : '#fee2e2',
               'border-color': isDarkMode ? '#dc2626' : '#fecaca',
               'box-shadow': isDarkMode ? '0 0 15px rgba(220, 38, 38, 0.2)' : 'initial'
             }">
          <h3 class="text-lg font-semibold mb-2 flex items-center"
              :style="{ 'color': isDarkMode ? '#f8fafc' : 'initial' }">
            <i class="bi bi-credit-card mr-2"
               :style="{ 'color': isDarkMode ? '#f87171' : '#ef4444' }"></i> Despesas
          </h3>
          <p class="text-2xl font-bold"
             :style="{ 
               'color': isDarkMode ? '#f8fafc' : 'initial',
               'text-shadow': isDarkMode ? '0 1px 3px rgba(0, 0, 0, 0.5)' : 'initial'
             }">
            {{ getTotalValue(expenseData) }} {{ coin }}
          </p>
        </div>
        
        <div class="stat-card stat-card-investment p-5 rounded-lg shadow-sm border"
             :style="{ 
               'background-color': isDarkMode ? '#052510' : '#ecfdf5',
               'border-color': isDarkMode ? '#10b981' : '#a7f3d0',
               'box-shadow': isDarkMode ? '0 0 15px rgba(16, 185, 129, 0.2)' : 'initial'
             }">
          <h3 class="text-lg font-semibold mb-2 flex items-center"
              :style="{ 'color': isDarkMode ? '#f8fafc' : 'initial' }">
            <i class="bi bi-graph-up-arrow mr-2"
               :style="{ 'color': isDarkMode ? '#34d399' : '#10b981' }"></i> Investimentos
          </h3>
          <p class="text-2xl font-bold"
             :style="{ 
               'color': isDarkMode ? '#f8fafc' : 'initial',
               'text-shadow': isDarkMode ? '0 1px 3px rgba(0, 0, 0, 0.5)' : 'initial'
             }">
            {{ getTotalValue(investmentData) }} {{ coin }}
          </p>
        </div>
      </div>

      <!-- Gráfico de linha principal -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Resumo Financeiro</h3>
        <GChart type="LineChart" :data="lineChartData"
          :options="chartOptions(chartTitles.lineChart)" />
      </div>

      <!-- Gráficos de área em duas colunas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Receitas Mensais</h3>
          <GChart v-if="incomeData.length" type="AreaChart" :data="incomeData"
          :options="chartOptions(chartTitles.incomeChart)" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de receitas para exibir</p>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Despesas Mensais</h3>
          <GChart v-if="expenseData.length" type="AreaChart" :data="expenseData"
          :options="chartOptions(chartTitles.expenseChart)" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de despesas para exibir</p>
          </div>
        </div>
      </div>

      <!-- Investimentos e Distribuição de Receitas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Investimentos Mensais</h3>
          <GChart v-if="investmentData.length" type="AreaChart" :data="investmentData"
          :options="chartOptions(chartTitles.investmentChart)" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de investimentos para exibir</p>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Distribuição de Rendimento por Fonte</h3>
          <GChart v-if="incomeBySource.length" type="PieChart" :data="incomeBySource"
            :options="chartOptions('Distribuição de Rendimento por Fonte (%)')" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de fontes de receita para exibir</p>
          </div>
        </div>
      </div>

      <!-- Distribuição de Despesas e Investimentos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Distribuição de Despesas por Categoria</h3>
          <GChart v-if="expenseCategories.length" type="PieChart" :data="expenseCategories"
            :options="chartOptions('Distribuição de Despesas por Categoria (%)')" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de categorias de despesa para exibir</p>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Distribuição de Investimentos por Tipo</h3>
          <GChart v-if="investmentByType.length" type="PieChart" :data="investmentByType"
            :options="chartOptions('Distribuição de Investimentos por Tipo (%)')" />
          <div v-else class="flex justify-center items-center h-64 text-gray-500">
            <p>Sem dados de tipos de investimento para exibir</p>
          </div>
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
import { useThemeStore } from "@/stores/theme";

export default {
  methods: {
    exportToPDF() {
      toast.info("A Gerar PDF, por favor aguarde...");
      
      // Temporarily remove dark mode class for PDF generation if it exists
      const isDarkMode = document.documentElement.classList.contains('dark-mode');
      if (isDarkMode) {
        document.documentElement.classList.remove('dark-mode');
      }
      
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

        doc.addImage(imgData, "PNG", 40, 0, imgWidth, imgHeight);

        // Guarda o PDF
        doc.save("Smart4Finances_Relatório_Financeiro.pdf");
        toast.success("Relatório descarregado com sucesso!");
        
        // Restore dark mode if it was active
        if (isDarkMode) {
          document.documentElement.classList.add('dark-mode');
        }
      }).catch(error => {
        console.error("Erro ao gerar o PDF:", error);
        toast.error("Erro ao gerar o relatório PDF");
        
        // Restore dark mode in case of error too
        if (isDarkMode) {
          document.documentElement.classList.add('dark-mode');
        }
      });
    },
    
    sendEmail() {
      toast.info("A Preparar o email, por favor aguarde...");
      
      // Temporarily remove dark mode class for PDF generation if it exists
      const isDarkMode = document.documentElement.classList.contains('dark-mode');
      if (isDarkMode) {
        document.documentElement.classList.remove('dark-mode');
      }
      
      const content = this.$refs.content;
      html2canvas(content, {
        allowTaint: true,
        useCORS: true,
        scale: 1, // Para envio por e-mail, manter a escala menor para reduzir o tamanho do arquivo
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
        
        // Adicionar título na capa
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.text("Relatório Financeiro", doc.internal.pageSize.width / 2, logoY + logoHeight + 20, { align: "center" });
        
        // Adicionar data na capa
        doc.setFontSize(14);
        const today = new Date();
        const dateStr = today.toLocaleDateString('pt-PT');
        doc.text(`Gerado em: ${dateStr}`, doc.internal.pageSize.width / 2, logoY + logoHeight + 40, { align: "center" });

        doc.addPage();

        const imgWidth = 220;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        doc.addImage(imgData, "PNG", 40, 0, imgWidth, imgHeight);

        const pdfBlob = doc.output("blob"); // Gera um blob do PDF

        const formData = new FormData();
        formData.append("file", pdfBlob, "Smart4Finances_Relatório_Financeiro.pdf");

        axios.post("/send-email", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
          .then(() => {
            toast.success("E-mail enviado com sucesso!");
            // Restore dark mode if it was active
            if (isDarkMode) {
              document.documentElement.classList.add('dark-mode');
            }
          })
          .catch((error) => {
            toast.error("Erro ao enviar e-mail.");
            console.error("Erro ao enviar email", error);
            // Restore dark mode in case of error
            if (isDarkMode) {
              document.documentElement.classList.add('dark-mode');
            }
          });
      }).catch(error => {
        toast.error("Erro ao preparar o relatório para e-mail");
        console.error("Erro ao preparar o relatório", error);
        
        // Restore dark mode in case of error
        if (isDarkMode) {
          document.documentElement.classList.add('dark-mode');
        }
      });
    }
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
    const themeStore = useThemeStore();
    const coin = ref(authStore.user?.data?.coin);  

    const chartTitles = computed(() => ({
    lineChart: `Receitas, Despesas e Investimentos Mensais (${coin.value})`,
    incomeChart: `Receitas Mensais (${coin.value})`,
    expenseChart: `Despesas Mensais (${coin.value})`,
    investmentChart: `Investimentos Mensais (${coin.value})`
  }));

    const getTotalValue = (dataArray) => {
      if (!dataArray || dataArray.length <= 1) return '0.00';
      
      let total = 0;
      for (let i = 1; i < dataArray.length; i++) {
        total += dataArray[i][1];
      }
      return total.toFixed(2);
    };

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
        toast.error("Erro ao carregar dados estatísticos");
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
      height: window.innerWidth < 768 ? 300 : 400, // Altura dinâmica baseada na largura do ecra
      legend: { position: "bottom" },
      is3D: false,
      pieHole: 0.4,  // Isso cria o efeito de "donut"
      backgroundColor: "transparent", // Fundo transparente
      chartArea: { width: '80%', height: '70%' },
      fontName: 'Arial',
      titleTextStyle: {
        fontSize: 16,
        bold: true,
        color: themeStore.darkMode ? '#f0f0f0' : '#333' // Dynamic color based on theme
      },
      colors: ['#4285F4', '#EA4335', '#34A853', '#FBBC05', '#FF6D01', '#46BDC6'],
      animation: {
        startup: true,
        duration: 1000,
        easing: 'out'
      },
      // Additional options for better dark mode compatibility
      hAxis: {
        textStyle: {
          color: themeStore.darkMode ? '#e0e0e0' : '#333'
        },
        gridlines: {
          color: themeStore.darkMode ? '#444' : '#ccc'
        }
      },
      vAxis: {
        textStyle: {
          color: themeStore.darkMode ? '#e0e0e0' : '#333'
        },
        gridlines: {
          color: themeStore.darkMode ? '#444' : '#ccc'
        }
      },
      legendTextStyle: {
        color: themeStore.darkMode ? '#e0e0e0' : '#333'
      }
    });

    // Add an isDarkMode computed property to directly check dark mode state
    const isDarkMode = computed(() => {
      return themeStore.darkMode;
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
      chartOptions,
      formatPieChartData,
      lineChartData,
      coin,
      chartTitles,
      getTotalValue,
      isDarkMode,
    };
  },
};
</script>