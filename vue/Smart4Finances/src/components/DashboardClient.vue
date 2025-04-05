<template>
  <div class="container mx-auto p-6 bg-white shadow-md rounded-lg mb-10" style="margin-top: 7vh; margin-bottom: 7vh;">
    <h2 class="text-2xl font-bold mb-6" style="color: black;">Dashboard Financeiro</h2>
    
    <div id="content" ref="content" class="stats">
      <!-- Filtros responsivos com design melhorado -->
      <div class="filters-container mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div class="w-full">
            <label class="block mb-2 text-gray-700 font-medium">Ano:</label>
            <input v-model="year" type="number" 
                  class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                  placeholder="Ano" />
          </div>
          
          <div class="w-full">
            <label class="block mb-2 text-gray-700 font-medium">Mês:</label>
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
        <div class="bg-blue-50 p-5 rounded-lg shadow-sm border border-blue-100">
          <h3 class="text-lg font-semibold text-blue-700 mb-2 flex items-center">
            <i class="bi bi-cash-coin mr-2"></i> Receitas
          </h3>
          <p class="text-2xl font-bold text-blue-900">{{ getTotalValue(incomeData) }} {{ coin }}</p>
        </div>
        
        <div class="bg-red-50 p-5 rounded-lg shadow-sm border border-red-100">
          <h3 class="text-lg font-semibold text-red-700 mb-2 flex items-center">
            <i class="bi bi-credit-card mr-2"></i> Despesas
          </h3>
          <p class="text-2xl font-bold text-red-900">{{ getTotalValue(expenseData) }} {{ coin }}</p>
        </div>
        
        <div class="bg-green-50 p-5 rounded-lg shadow-sm border border-green-100">
          <h3 class="text-lg font-semibold text-green-700 mb-2 flex items-center">
            <i class="bi bi-graph-up-arrow mr-2"></i> Investimentos
          </h3>
          <p class="text-2xl font-bold text-green-900">{{ getTotalValue(investmentData) }} {{ coin }}</p>
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

export default {
  methods: {
    exportToPDF() {
      toast.info("Gerando PDF, por favor aguarde...");
      
      // Criar um documento PDF
      const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4",
      });

      // Definindo a capa preta
      doc.setFillColor(0, 0, 0);
      doc.rect(0, 0, doc.internal.pageSize.width, doc.internal.pageSize.height, 'F');

      // Adicionando o logotipo
      const logoWidth = 100;
      const logoHeight = 80;
      const logoX = (doc.internal.pageSize.width - logoWidth) / 2; // Centralizado
      const logoY = 50; 
      doc.addImage(logo, 'PNG', logoX, logoY, logoWidth, logoHeight);
      
      // Adicionar título na capa
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(24);
      doc.text("Relatório Financeiro", doc.internal.pageSize.width / 2, logoY + logoHeight + 20, { align: "center" });
      
      // Adicionar data na capa
      doc.setFontSize(14);
      const today = new Date();
      const dateStr = today.toLocaleDateString('pt-PT');
      doc.text(`Gerado em: ${dateStr}`, doc.internal.pageSize.width / 2, logoY + logoHeight + 40, { align: "center" });

      // Função para adicionar gráficos ao PDF
      const addGraphsToPDF = async () => {
        try {
          // Nova página para o resumo
          doc.addPage();
          
          // Adicionar cabeçalho
          doc.setFillColor(41, 128, 185); // Azul para o cabeçalho
          doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(16);
          doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
          
          // Título do relatório
          doc.setTextColor(0, 0, 0);
          doc.setFontSize(18);
          doc.text("Resumo Financeiro", doc.internal.pageSize.width / 2, 30, { align: "center" });
          
          // Adicionar informações do filtro
          doc.setFontSize(12);
          let filterText = `Ano: ${this.year}`;
          if (this.month) {
            const months = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
            filterText += ` | Mês: ${months[this.month - 1]}`;
          } else {
            filterText += " | Todos os meses";
          }
          doc.text(filterText, doc.internal.pageSize.width / 2, 40, { align: "center" });
          
          // Linha separadora
          doc.setDrawColor(220, 220, 220);
          doc.line(20, 45, doc.internal.pageSize.width - 20, 45);
          
          // Adicionar estatísticas principais
          let yPos = 60;
          
          // Título da seção
          doc.setFontSize(14);
          doc.setTextColor(41, 128, 185);
          doc.text("Estatísticas Principais", 20, yPos);
          yPos += 10;
          
          // Receitas
          doc.setTextColor(52, 152, 219); // Azul para receitas
          doc.setFontSize(12);
          doc.text("Receitas Totais:", 30, yPos);
          doc.setFontSize(12);
          doc.setTextColor(0, 0, 0);
          doc.text(`${this.getTotalValue(this.incomeData)} ${this.coin}`, 100, yPos);
          yPos += 10;
          
          // Despesas
          doc.setTextColor(231, 76, 60); // Vermelho para despesas
          doc.setFontSize(12);
          doc.text("Despesas Totais:", 30, yPos);
          doc.setTextColor(0, 0, 0);
          doc.text(`${this.getTotalValue(this.expenseData)} ${this.coin}`, 100, yPos);
          yPos += 10;
          
          // Investimentos
          doc.setTextColor(46, 204, 113); // Verde para investimentos
          doc.setFontSize(12);
          doc.text("Investimentos Totais:", 30, yPos);
          doc.setTextColor(0, 0, 0);
          doc.text(`${this.getTotalValue(this.investmentData)} ${this.coin}`, 100, yPos);
          yPos += 20;
          
          // Linha separadora
          doc.setDrawColor(220, 220, 220);
          doc.line(20, yPos - 10, doc.internal.pageSize.width - 20, yPos - 10);
          
          // Adicionar distribuição de despesas por categoria
          doc.setFontSize(14);
          doc.setTextColor(41, 128, 185);
          doc.text("Distribuição de Despesas", 20, yPos);
          yPos += 10;
          
          if (this.expenseCategories.length > 1) {
            for (let i = 1; i < this.expenseCategories.length; i++) {
              const category = this.expenseCategories[i][0];
              const value = this.expenseCategories[i][1];
              const percentage = (value / parseFloat(this.getTotalValue(this.expenseData)) * 100).toFixed(1);
              
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(10);
              doc.text(`${category}: ${value} ${this.coin} (${percentage}%)`, 30, yPos);
              yPos += 7;
              
              // Verifica se precisa de uma nova página
              if (yPos > 280) {
                doc.addPage();
                // Adicionar cabeçalho na nova página
                doc.setFillColor(41, 128, 185);
                doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(16);
                doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
                yPos = 30;
              }
            }
          } else {
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(10);
            doc.text("Sem dados de categorias de despesa para exibir", 30, yPos);
            yPos += 10;
          }
          
          // Adicionar gráficos ao PDF
          // Capturar e adicionar o gráfico de linha
          const lineChart = document.querySelector('.bg-white:nth-child(4) .google-visualization-chart');
          if (lineChart) {
            doc.addPage();
            doc.setFillColor(41, 128, 185);
            doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16);
            doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
            
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Resumo Financeiro", 20, 30);
            
            const canvas = await html2canvas(lineChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, 40, 190, 100);
          }
          
          // Capturar e adicionar os gráficos de área (receitas e despesas)
          const incomeChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(5) .bg-white:nth-child(1) .google-visualization-chart');
          const expenseChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(5) .bg-white:nth-child(2) .google-visualization-chart');
          
          if (incomeChart || expenseChart) {
            doc.addPage();
            doc.setFillColor(41, 128, 185);
            doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16);
            doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
            
            let yPosition = 30;
            
            if (incomeChart) {
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Receitas Mensais", 20, yPosition);
              
              const canvas = await html2canvas(incomeChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
              yPosition += 120;
            }
            
            if (expenseChart) {
              // Se não couber na página, adiciona uma nova
              if (yPosition > 160) {
                doc.addPage();
                doc.setFillColor(41, 128, 185);
                doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(16);
                doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
                yPosition = 30;
              }
              
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Despesas Mensais", 20, yPosition);
              
              const canvas = await html2canvas(expenseChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            }
          }
          
          // Capturar e adicionar os gráficos de área (investimentos) e gráfico de pizza (rendimentos)
          const investmentChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(6) .bg-white:nth-child(1) .google-visualization-chart');
          const incomeSourceChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(6) .bg-white:nth-child(2) .google-visualization-chart');
          
          if (investmentChart || incomeSourceChart) {
            doc.addPage();
            doc.setFillColor(41, 128, 185);
            doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16);
            doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
            
            let yPosition = 30;
            
            if (investmentChart) {
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Investimentos Mensais", 20, yPosition);
              
              const canvas = await html2canvas(investmentChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
              yPosition += 120;
            }
            
            if (incomeSourceChart) {
              // Se não couber na página, adiciona uma nova
              if (yPosition > 160) {
                doc.addPage();
                doc.setFillColor(41, 128, 185);
                doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(16);
                doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
                yPosition = 30;
              }
              
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Distribuição de Rendimento por Fonte", 20, yPosition);
              
              const canvas = await html2canvas(incomeSourceChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            }
          }
          
          // Capturar e adicionar os gráficos de pizza (despesas por categoria e investimentos por tipo)
          const expenseCategoryChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(7) .bg-white:nth-child(1) .google-visualization-chart');
          const investmentTypeChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(7) .bg-white:nth-child(2) .google-visualization-chart');
          
          if (expenseCategoryChart || investmentTypeChart) {
            doc.addPage();
            doc.setFillColor(41, 128, 185);
            doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(16);
            doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
            
            let yPosition = 30;
            
            if (expenseCategoryChart) {
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Distribuição de Despesas por Categoria", 20, yPosition);
              
              const canvas = await html2canvas(expenseCategoryChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
              yPosition += 120;
            }
            
            if (investmentTypeChart) {
              // Se não couber na página, adiciona uma nova
              if (yPosition > 160) {
                doc.addPage();
                doc.setFillColor(41, 128, 185);
                doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(16);
                doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
                yPosition = 30;
              }
              
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(14);
              doc.text("Distribuição de Investimentos por Tipo", 20, yPosition);
              
              const canvas = await html2canvas(investmentTypeChart);
              const imgData = canvas.toDataURL('image/png');
              doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            }
          }
          
          // Salvar o PDF depois de adicionar todos os gráficos
          doc.save("Smart4Finances_Relatório_Financeiro.pdf");
          toast.success("Relatório descarregado com sucesso!");
        } catch (error) {
          console.error("Erro ao gerar o PDF:", error);
          toast.error("Erro ao gerar o relatório PDF");
        }
      };
      
      // Chamar a função para adicionar os gráficos
      addGraphsToPDF();
    },
    sendEmail: async function() {
      try {
        toast.info("Preparando o email, por favor aguarde...");
        
        // Criar um novo documento PDF
        const doc = new jsPDF({
          orientation: "portrait",
          unit: "mm",
          format: "a4",
        });

        // Definindo a capa preta
        doc.setFillColor(0, 0, 0);
        doc.rect(0, 0, doc.internal.pageSize.width, doc.internal.pageSize.height, 'F');

        // Adicionando o logotipo
        const logoWidth = 100;
        const logoHeight = 80;
        const logoX = (doc.internal.pageSize.width - logoWidth) / 2; // Centralizado
        const logoY = 50; 
        doc.addImage(logo, 'PNG', logoX, logoY, logoWidth, logoHeight);
        
        // Adicionar título na capa
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.text("Relatório Financeiro", doc.internal.pageSize.width / 2, logoY + logoHeight + 20, { align: "center" });
        
        // Adicionar data na capa
        doc.setFontSize(14);
        const today = new Date();
        const dateStr = today.toLocaleDateString('pt-PT');
        doc.text(`Gerado em: ${dateStr}`, doc.internal.pageSize.width / 2, logoY + logoHeight + 40, { align: "center" });

        // Nova página para o resumo
        doc.addPage();
        
        // Adicionar cabeçalho
        doc.setFillColor(41, 128, 185); // Azul para o cabeçalho
        doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(16);
        doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
        
        // Título do relatório
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(18);
        doc.text("Resumo Financeiro", doc.internal.pageSize.width / 2, 30, { align: "center" });
        
        // Adicionar informações do filtro
        doc.setFontSize(12);
        let filterText = `Ano: ${this.year}`;
        if (this.month) {
          const months = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
          filterText += ` | Mês: ${months[this.month - 1]}`;
        } else {
          filterText += " | Todos os meses";
        }
        doc.text(filterText, doc.internal.pageSize.width / 2, 40, { align: "center" });
        
        // Linha separadora
        doc.setDrawColor(220, 220, 220);
        doc.line(20, 45, doc.internal.pageSize.width - 20, 45);
        
        // Adicionar estatísticas principais
        let yPos = 60;
        
        // Título da seção
        doc.setFontSize(14);
        doc.setTextColor(41, 128, 185);
        doc.text("Estatísticas Principais", 20, yPos);
        yPos += 10;
        
        // Receitas
        doc.setTextColor(52, 152, 219); // Azul para receitas
        doc.setFontSize(12);
        doc.text("Receitas Totais:", 30, yPos);
        doc.setFontSize(12);
        doc.setTextColor(0, 0, 0);
        doc.text(`${this.getTotalValue(this.incomeData)} ${this.coin}`, 100, yPos);
        yPos += 10;
        
        // Despesas
        doc.setTextColor(231, 76, 60); // Vermelho para despesas
        doc.setFontSize(12);
        doc.text("Despesas Totais:", 30, yPos);
        doc.setTextColor(0, 0, 0);
        doc.text(`${this.getTotalValue(this.expenseData)} ${this.coin}`, 100, yPos);
        yPos += 10;
        
        // Investimentos
        doc.setTextColor(46, 204, 113); // Verde para investimentos
        doc.setFontSize(12);
        doc.text("Investimentos Totais:", 30, yPos);
        doc.setTextColor(0, 0, 0);
        doc.text(`${this.getTotalValue(this.investmentData)} ${this.coin}`, 100, yPos);
        yPos += 20;
        
        // Linha separadora
        doc.setDrawColor(220, 220, 220);
        doc.line(20, yPos - 10, doc.internal.pageSize.width - 20, yPos - 10);
        
        // Adicionar distribuição de despesas por categoria
        doc.setFontSize(14);
        doc.setTextColor(41, 128, 185);
        doc.text("Distribuição de Despesas", 20, yPos);
        yPos += 10;
        
        if (this.expenseCategories.length > 1) {
          for (let i = 1; i < this.expenseCategories.length; i++) {
            const category = this.expenseCategories[i][0];
            const value = this.expenseCategories[i][1];
            const percentage = (value / parseFloat(this.getTotalValue(this.expenseData)) * 100).toFixed(1);
            
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(10);
            doc.text(`${category}: ${value} ${this.coin} (${percentage}%)`, 30, yPos);
            yPos += 7;
            
            // Verifica se precisa de uma nova página
            if (yPos > 280) {
              doc.addPage();
              // Adicionar cabeçalho na nova página
              doc.setFillColor(41, 128, 185);
              doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
              doc.setTextColor(255, 255, 255);
              doc.setFontSize(16);
              doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
              yPos = 30;
            }
          }
        } else {
          doc.setTextColor(0, 0, 0);
          doc.setFontSize(10);
          doc.text("Sem dados de categorias de despesa para exibir", 30, yPos);
          yPos += 10;
        }
        
        // Adicionar gráficos ao PDF
        // Capturar e adicionar o gráfico de linha
        const lineChart = document.querySelector('.bg-white:nth-child(4) .google-visualization-chart');
        if (lineChart) {
          doc.addPage();
          doc.setFillColor(41, 128, 185);
          doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(16);
          doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
          
          doc.setTextColor(0, 0, 0);
          doc.setFontSize(14);
          doc.text("Resumo Financeiro", 20, 30);
          
          const canvas = await html2canvas(lineChart);
          const imgData = canvas.toDataURL('image/png');
          doc.addImage(imgData, 'PNG', 10, 40, 190, 100);
        }
        
        // Capturar e adicionar os gráficos de área (receitas e despesas)
        const incomeChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(5) .bg-white:nth-child(1) .google-visualization-chart');
        const expenseChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(5) .bg-white:nth-child(2) .google-visualization-chart');
        
        if (incomeChart || expenseChart) {
          doc.addPage();
          doc.setFillColor(41, 128, 185);
          doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(16);
          doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
          
          let yPosition = 30;
          
          if (incomeChart) {
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Receitas Mensais", 20, yPosition);
            
            const canvas = await html2canvas(incomeChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            yPosition += 120;
          }
          
          if (expenseChart) {
            // Se não couber na página, adiciona uma nova
            if (yPosition > 160) {
              doc.addPage();
              doc.setFillColor(41, 128, 185);
              doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
              doc.setTextColor(255, 255, 255);
              doc.setFontSize(16);
              doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
              yPosition = 30;
            }
            
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Despesas Mensais", 20, yPosition);
            
            const canvas = await html2canvas(expenseChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
          }
        }
        
        // Capturar e adicionar os gráficos de área (investimentos) e gráfico de pizza (rendimentos)
        const investmentChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(6) .bg-white:nth-child(1) .google-visualization-chart');
        const incomeSourceChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(6) .bg-white:nth-child(2) .google-visualization-chart');
        
        if (investmentChart || incomeSourceChart) {
          doc.addPage();
          doc.setFillColor(41, 128, 185);
          doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(16);
          doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
          
          let yPosition = 30;
          
          if (investmentChart) {
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Investimentos Mensais", 20, yPosition);
            
            const canvas = await html2canvas(investmentChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            yPosition += 120;
          }
          
          if (incomeSourceChart) {
            // Se não couber na página, adiciona uma nova
            if (yPosition > 160) {
              doc.addPage();
              doc.setFillColor(41, 128, 185);
              doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
              doc.setTextColor(255, 255, 255);
              doc.setFontSize(16);
              doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
              yPosition = 30;
            }
            
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Distribuição de Rendimento por Fonte", 20, yPosition);
            
            const canvas = await html2canvas(incomeSourceChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
          }
        }
        
        // Capturar e adicionar os gráficos de pizza (despesas por categoria e investimentos por tipo)
        const expenseCategoryChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(7) .bg-white:nth-child(1) .google-visualization-chart');
        const investmentTypeChart = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2:nth-child(7) .bg-white:nth-child(2) .google-visualization-chart');
        
        if (expenseCategoryChart || investmentTypeChart) {
          doc.addPage();
          doc.setFillColor(41, 128, 185);
          doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(16);
          doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
          
          let yPosition = 30;
          
          if (expenseCategoryChart) {
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Distribuição de Despesas por Categoria", 20, yPosition);
            
            const canvas = await html2canvas(expenseCategoryChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
            yPosition += 120;
          }
          
          if (investmentTypeChart) {
            // Se não couber na página, adiciona uma nova
            if (yPosition > 160) {
              doc.addPage();
              doc.setFillColor(41, 128, 185);
              doc.rect(0, 0, doc.internal.pageSize.width, 20, 'F');
              doc.setTextColor(255, 255, 255);
              doc.setFontSize(16);
              doc.text("SMART4FINANCES", doc.internal.pageSize.width / 2, 12, { align: "center" });
              yPosition = 30;
            }
            
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(14);
            doc.text("Distribuição de Investimentos por Tipo", 20, yPosition);
            
            const canvas = await html2canvas(investmentTypeChart);
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, yPosition + 10, 190, 100);
          }
        }

        // Gera um blob do PDF  
        const pdfBlob = doc.output("blob");

        const formData = new FormData();
        formData.append("file", pdfBlob, "Smart4Finances_Relatório_Financeiro.pdf");

        const response = await axios.post("/send-email", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        
        toast.success("E-mail enviado com sucesso!");
      } catch (error) {
        toast.error("Erro ao enviar o E-mail");
        console.error("Erro ao enviar email", error);
      }
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
      chartArea: { width: '90%', height: '70%' },
      fontName: 'Arial',
      titleTextStyle: {
        fontSize: 16,
        bold: true,
        color: '#333'
      },
      colors: ['#4285F4', '#EA4335', '#34A853', '#FBBC05', '#FF6D01', '#46BDC6'],
      animation: {
        startup: true,
        duration: 1000,
        easing: 'out'
      }
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
    };
  },
};
</script>

<style scoped>
.filters-container {
  width: 100%;
}

@media (max-width: 640px) {
  button i {
    margin-right: 4px;
  }
}
</style>
