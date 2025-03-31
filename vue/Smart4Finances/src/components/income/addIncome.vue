<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4">{{ isEditMode ? 'Editar Receita' : 'Registar Receita' }}</h2>
    
    <label class="block mb-2">Carregar Recibo:</label>
    <input type="file" @change="onFileChange" accept="image/*" class="mb-4" />
    
    <div v-if="receiptPreview" class="mb-4">
      <img :src="receiptPreview" alt="Recibo" class="w-full rounded cursor-pointer" @click="openImageModal" />
    </div>
    
    <form @submit.prevent="submitIncome">
      <label class="block mb-2">Fonte:</label>
      <input type="text" v-model="income.source" placeholder="Introduza a fonte da receita..." class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2">Valor:</label>
      <input type="text" v-model="income.amount" @blur="formatAmount" class="w-full p-2 border rounded mb-4" />
      
      <label class="block mb-2">Data:</label>
      <input type="date" v-model="income.date" class="w-full p-2 border rounded mb-4" />
      
      <label class="block mb-2">Intervalo Recorrente (opcional):</label>
      <div class="flex mb-4">
        <input type="text" v-model="income.recurring_interval" placeholder="0" class="flex-1 p-2 border rounded-l" />
        <select v-model="income.recurring_interval_unit" class="p-2 border rounded-r">
          <option value="dia">Dia</option>
          <option value="semana">Semana</option>
          <option value="mes">Mês</option>
          <option value="anual">Anual</option>
        </select>
      </div>
      
      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
        <i class="bi bi-check2-circle"></i>
        {{ isEditMode ? 'Atualizar' : 'Confirmar' }}
      </button>
    </form>
    <button @click="isEditMode ? viewExpense(income.id) : IncomeList()" class="w-full mt-4 bg-red-500 text-white px-4 py-2 rounded">
      <i class="bi bi-arrow-left-short"></i>
      Voltar
    </button>
    
    <!-- Modal de visualização da imagem -->
    <div v-if="showImageModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 z-50"
         @wheel.prevent="onWheel"
         @mousedown="onMouseDown"
         @mousemove="onMouseMove"
         @mouseup="onMouseUp"
         @mouseleave="onMouseUp">
      <div class="relative p-2 rounded overflow-hidden" style="background: transparent; max-width: 90vw; max-height: 90vh;">
        <img :src="receiptPreview" alt="Recibo Detalhado"
             :style="imageStyle"
             class="select-none" />
      </div>
      <button @click="closeImageModal" class="absolute top-4 right-4 px-4 py-2 bg-red-500 text-white rounded">
        Fechar
      </button>
    </div>
    
  </div>
</template>

<script>
import axios from 'axios';
import { Toast } from 'bootstrap';
import Tesseract from 'tesseract.js';
import { toast } from 'vue3-toastify';

export default {
  props: {
    IncomeId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      isEditMode: false,
      processing: false,
      receiptPreview: null,
      errorMessage: '',
      income: {
        source: '',
        amount: '0.00',
        date: '',
        recurring_interval: '0',
        recurring_interval_unit: 'dia',
        receipt: ''
      },
      // Variáveis para a visualização e manipulação da imagem
      showImageModal: false,
      zoomLevel: 1,
      offsetX: 0,
      offsetY: 0,
      isDragging: false,
      dragStartX: 0,
      dragStartY: 0,
      startOffsetX: 0,
      startOffsetY: 0
    };
  },
  computed: {
    imageStyle() {
      return {
        transform: `translate(${this.offsetX}px, ${this.offsetY}px) scale(${this.zoomLevel})`,
        transition: this.isDragging ? 'none' : 'transform 0.1s ease-out',
        cursor: this.isDragging ? 'grabbing' : 'grab'
      };
    }
  },
  mounted() {
    if (this.IncomeId) {
      this.isEditMode = true;
      this.loadIncome();
    }
  },
  methods: {
    loadIncome() {
      axios.get(`/incomes/${this.IncomeId}`)
        .then(response => {
          this.income = response.data;
          this.receiptPreview = response.data.receipt ? import.meta.env.VITE_API_DOMAIN + '/storage/' + response.data.receipt : null;
          this.income.recurring_interval = response.data.recurring_interval != 'null' ? response.data.recurring_interval : '0';
        })
        .catch(error => {
          toast.error('Erro ao carregar a receita. Tente novamente mais tarde.');
        });
    },
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        toast.success("Imagem aceite!");
        this.income.receipt = file;
        this.receiptPreview = URL.createObjectURL(file);
        this.processImage(file);
      }
    },
    async processImage(file) {
      this.processing = true;
      const toastId = toast.info("Processando imagem...", { autoClose: false });
      try {
        const result = await Tesseract.recognize(file, 'por', {
          logger: m => {
            if (m.status === 'recognizing text') {
              const progress = Math.floor(m.progress * 100);
              toast.update(toastId, { render: `Processando imagem... ${progress}%` });
            }
          }
        });
        toast.remove(toastId);
        // Extrai e formata o valor
        this.income.amount = this.formatAmountValue(this.extractTotal(result.data.text));
        const extractedDate = this.extractDate(result.data.text);
        if (extractedDate) {
          this.income.date = this.formatDate(extractedDate);
        }
        toast.success("Imagem processada com sucesso!");
      } catch (error) {
        toast.remove(toastId);
        toast.error("Erro ao processar imagem");
      }
      this.processing = false;
    },
    extractTotal(text) {
      const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
      const candidateKeywords = ['total', 'recebido', 'valor'];
      const excludedKeywords = ['iban', 'swift', 'bic'];
      const candidates = lines.filter(line => {
        const lower = line.toLowerCase();
        return candidateKeywords.some(k => lower.includes(k)) &&
               !excludedKeywords.some(e => lower.includes(e));
      });
      
      let candidateLine;
      if (candidates.length > 0) {
        const nonSubtotal = candidates.filter(line => !line.toLowerCase().includes('subtotal'));
        candidateLine = nonSubtotal.length > 0 ? nonSubtotal[nonSubtotal.length - 1] : candidates[candidates.length - 1];
        const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
        const match = candidateLine.match(regex);
        if (match && match[1]) {
          return this.stripSymbols(this.cleanValue(match[1]));
        }
      }
      
      for (let i = lines.length - 1; i >= 0; i--) {
        const lower = lines[i].toLowerCase();
        if (!excludedKeywords.some(e => lower.includes(e))) {
          const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
          const match = lines[i].match(regex);
          if (match && match[1]) {
            return this.stripSymbols(this.cleanValue(match[1]));
          }
        }
      }
      
      return '0.00';
    },
    extractDate(text) {
      const dateRegex = /(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/;
      const match = text.match(dateRegex);
      return match && match[1] ? match[1] : '';
    },
    formatDate(dateStr) {
      const parts = dateStr.split(/[\/\-]/);
      if (parts.length === 3) {
        let day = parts[0].padStart(2, '0');
        let month = parts[1].padStart(2, '0');
        let year = parts[2];
        if (year.length === 2) year = '20' + year;
        return `${year}-${month}-${day}`;
      }
      return dateStr;
    },
    cleanValue(value) {
      return value ? value.trim() : '';
    },
    stripSymbols(value) {
      return value.replace(/[^0-9.,]/g, '');
    },
    formatAmount() {
      let num = parseFloat(this.income.amount.replace(',', '.'));
      if (!isNaN(num)) {
        this.income.amount = num.toFixed(2);
      } else {
        this.income.amount = '0.00';
      }
    },
    formatAmountValue(value) {
      let num = parseFloat(value.replace(',', '.'));
      return !isNaN(num) ? num.toFixed(2) : '0.00';
    },
    submitIncome() {
      if (!this.income.date || !this.income.amount || !this.income.source) {
        this.errorMessage = 'Os campos Data, Valor e Fonte são obrigatórios.';
        toast.error(this.errorMessage);
        return;
      }
      
      this.formatAmount();
      if (this.income.recurring_interval === '0') {
        this.income.recurring_interval = null;
      }
      
      this.errorMessage = '';
      const formData = new FormData();
      formData.append('source', this.income.source);
      formData.append('amount', this.income.amount);
      formData.append('date', this.income.date);
      formData.append('recurring_interval', this.income.recurring_interval);
      formData.append('recurring_interval_unit', this.income.recurring_interval_unit);
      
      if (this.income.receipt) {
        formData.append('receipt', this.income.receipt);
      }
      
      if (this.isEditMode) {

        axios.put(`/incomes/${this.IncomeId}`, formData, {
          headers: { 'Content-Type': 'application/json' }
        }).then(response => {
          toast.success("Receita atualizada com sucesso!");
          this.$emit("IncomeList", true);
        }).catch(error => {
          this.errorMessage = 'Erro ao atualizar receita. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      } else {
        axios.post('/incomes', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        }).then(response => {
          toast.success("Receita registada com sucesso!");
          this.$emit("IncomeList", true);
        }).catch(error => {
          this.errorMessage = 'Erro ao submeter receita. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      }
    },
    openImageModal() {
      this.showImageModal = true;
      this.zoomLevel = 1;
      this.offsetX = 0;
      this.offsetY = 0;
    },
    closeImageModal() {
      this.showImageModal = false;
      this.zoomLevel = 1;
      this.offsetX = 0;
      this.offsetY = 0;
      this.isDragging = false;
    },
    onWheel(e) {
      e.preventDefault();
      const scaleAmount = 0.001;
      let newZoom = this.zoomLevel - e.deltaY * scaleAmount;
      newZoom = Math.max(newZoom, 1);
      const rect = e.currentTarget.getBoundingClientRect();
      const mouseX = e.clientX - rect.left;
      const mouseY = e.clientY - rect.top;
      const factor = newZoom / this.zoomLevel;
      this.offsetX = (this.offsetX - mouseX) * factor + mouseX;
      this.offsetY = (this.offsetY - mouseY) * factor + mouseY;
      this.zoomLevel = newZoom;
    },
    onMouseDown(e) {
      if (e.button !== 0) return;
      this.isDragging = true;
      this.dragStartX = e.clientX;
      this.dragStartY = e.clientY;
      this.startOffsetX = this.offsetX;
      this.startOffsetY = this.offsetY;
    },
    onMouseMove(e) {
      if (!this.isDragging) return;
      const dx = e.clientX - this.dragStartX;
      const dy = e.clientY - this.dragStartY;
      this.offsetX = this.startOffsetX + dx;
      this.offsetY = this.startOffsetY + dy;
    },
    onMouseUp() {
      this.isDragging = false;
    },
    viewExpense(incomeId) {
      this.$emit("IncomeView", incomeId);
    },  
    IncomeList() {
      this.$emit("IncomeList", null);
    }, 
  }
};
</script>

<style>
body {
  background-color: #f4f4f4;
}

.bthouver:hover {
  background-color: green;
}
</style>
