<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh;  margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4">Registar Despesa</h2>
    
    <label class="block mb-2">Carregar Recibo:</label>
    <input type="file" @change="onFileChange" accept="image/*" class="mb-4" />
    
    <!-- Pré-visualização com clique para abrir modal -->
    <div v-if="receiptPreview" class="mb-4">
      <img :src="receiptPreview" alt="Recibo" class="w-full rounded cursor-pointer" @click="openImageModal" />
    </div>
    
    <form @submit.prevent="submitExpense">
      <label class="block mb-2">Categoria:</label>
      <input type="text" v-model="searchQuery" placeholder="Pesquisar categoria..." class="w-full p-2 border rounded mb-2" />
      <div class="border rounded mb-4" style="max-height: 200px; overflow-y: auto;">
        <div v-for="category in filteredCategories" :key="category.id" 
             class="p-2 cursor-pointer hover:bg-gray-200"
             @click="selectCategory(category)">
          {{ category.name }}
        </div>
        <div v-if="filteredCategories.length === 0" class="p-2 text-gray-500">
          Nenhuma categoria encontrada.
        </div>
      </div>
      
      <button type="button" @click="showModal = true" style="background-color:black; color:#DAA520"
              class="mb-4 py-2 px-4 rounded hover:bg-gray-600">
        Ver todas as categorias
      </button>
      
      <!-- Select oculto para manter o valor selecionado -->
      <select v-model="expense.category_id" class="hidden">
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>
      
      <label class="block mb-2">Valor:</label>
      <input type="text" v-model="expense.amount" @blur="formatAmount" class="w-full p-2 border rounded mb-4" />
      
      <label class="block mb-2">Descrição:</label>
      <input type="text" v-model="expense.description" class="w-full p-2 border rounded mb-4" />
      
      <label class="block mb-2">Data:</label>
      <input type="date" v-model="expense.date" class="w-full p-2 border rounded mb-4" />
      
      <label class="block mb-2">Intervalo Recorrente (opcional):</label>
      <div class="flex mb-4">
        <input type="text" v-model="expense.recurring_interval" placeholder="0" class="flex-1 p-2 border rounded-l" />
        <select v-model="expense.recurring_interval_unit" class="p-2 border rounded-r">
          <option value="dia">Dia</option>
          <option value="semana">Semana</option>
          <option value="mes">Mês</option>
          <option value="anual">Anual</option>
        </select>
      </div>
      
      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600" >
        Confirmar
      </button>
    </form>
    
    <!-- Modal para seleção de categoria (não alterado) -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg w-11/12 max-w-md">
        <h3 class="text-xl font-bold mb-4">Selecione uma Categoria</h3>
        <input type="text" v-model="modalSearchQuery" placeholder="Pesquisar categoria..." class="w-full p-2 border rounded mb-2" />
        <div class="border rounded mb-4" style="max-height: 300px; overflow-y: auto;">
          <div v-for="category in modalFilteredCategories" :key="category.id" 
               class="p-2 cursor-pointer hover:bg-gray-200"
               @click="selectCategoryFromModal(category)">
            {{ category.name }}
          </div>
          <div v-if="modalFilteredCategories.length === 0" class="p-2 text-gray-500">
            Nenhuma categoria encontrada.
          </div>
        </div>
        <button @click="showModal = false" style="background-color:black; color:#DAA520"
                class="w-full py-2 rounded hover:bg-gray-600">
          Fechar
        </button>
      </div>
    </div>
    
    <!-- Modal de visualização da imagem com zoom e panning via mouse -->
    <div v-if="showImageModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 z-50"
         @wheel.prevent="onWheel"
         @mousedown="onMouseDown"
         @mousemove="onMouseMove"
         @mouseup="onMouseUp"
         @mouseleave="onMouseUp">
      <!-- Container sem fundo branco para não "esconder" a imagem -->
      <div class="relative p-2 rounded overflow-hidden" style="background: transparent; max-width: 90vw; max-height: 90vh;">
        <img :src="receiptPreview" alt="Recibo Detalhado"
             :style="imageStyle"
             class="select-none" />
      </div>
      <!-- Botão para fechar o modal -->
      <button @click="closeImageModal" class="absolute top-4 right-4 px-4 py-2 bg-red-500 text-white rounded">
        Fechar
      </button>
    </div>
    
  </div>
</template>

<script>
import axios from 'axios';
import Tesseract from 'tesseract.js';
import { toast } from 'vue3-toastify';

export default {
  data() {
    return {
      processing: false,
      receiptPreview: null,
      errorMessage: '',
      expense: {
        category_id: '',
        amount: '0.00',
        description: '',
        date: '',
        recurring_interval: '0',
        recurring_interval_unit: 'dia',
        receipt: ''
      },
      categories: [],
      searchQuery: '',
      modalSearchQuery: '',
      showModal: false,
      showImageModal: false,
      // Propriedades para zoom e panning
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
    filteredCategories() {
      if (!this.searchQuery) return this.categories;
      return this.categories.filter(category =>
        category.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    modalFilteredCategories() {
      if (!this.modalSearchQuery) return this.categories;
      return this.categories.filter(category =>
        category.name.toLowerCase().includes(this.modalSearchQuery.toLowerCase())
      );
    },
    imageStyle() {
      return {
        transform: `translate(${this.offsetX}px, ${this.offsetY}px) scale(${this.zoomLevel})`,
        transition: this.isDragging ? 'none' : 'transform 0.1s ease-out',
        cursor: this.isDragging ? 'grabbing' : 'grab'
      };
    }
  },
  mounted() {
    axios.get('/categories')
      .then(response => {
        this.categories = response.data;
      })
      .catch(error => {
        console.error('Erro ao buscar categorias:', error);
      });
  },
  methods: {
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        toast.success("Imagem aceite!");
        this.expense.receipt = file;
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
        this.expense.amount = this.extractTotal(result.data.text);
        this.formatAmount();
        const extractedDate = this.extractDate(result.data.text);
        if (extractedDate) {
          this.expense.date = this.formatDate(extractedDate);
        }
        toast.success("Imagem processada com sucesso!");
      } catch (error) {
        console.error('Erro ao processar imagem:', error);
        toast.remove(toastId);
        toast.error("Erro ao processar imagem");
      }
      this.processing = false;
    },
    extractTotal(text) {
      console.log(text);
      const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
      const candidateKeywords = ['total', 'pagar', 'pagamento'];
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
      
      const subtotalLine = lines.find(line => line.toLowerCase().includes('subtotal'));
      if (subtotalLine) {
        const regex = /(?:r\$|\$|€|£|eur)?\s*(\d+(?:[,.]\d+)?)/i;
        const match = subtotalLine.match(regex);
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
      value.replace(/[^0-9.,]/g, '');
      return value;
    },
    selectCategory(category) {
      this.expense.category_id = category.id;
      this.searchQuery = category.name;
    },
    selectCategoryFromModal(category) {
      this.expense.category_id = category.id;
      this.searchQuery = category.name;
      this.showModal = false;
    },
    formatAmount() {
      let num = parseFloat(this.expense.amount.replace(',', '.'));
      this.expense.amount = num.toFixed(2);
    },
    submitExpense() {
      if (!this.expense.date || !this.expense.amount || !this.expense.category_id) {
        this.errorMessage = 'Os campos Data, Valor e Categoria são obrigatórios.';
        toast.error(this.errorMessage);
        return;
      }
      
      this.formatAmount();
      if (this.expense.recurring_interval === '0') {
        this.expense.recurring_interval = null;
      }
      
      this.errorMessage = '';
      const formData = new FormData();
      formData.append('category_id', this.expense.category_id);
      formData.append('amount', this.expense.amount);
      formData.append('description', this.expense.description);
      formData.append('date', this.expense.date);
      formData.append('recurring_interval', this.expense.recurring_interval);
      formData.append('recurring_interval_unit', this.expense.recurring_interval_unit);
      
      if (this.expense.receipt) {
        formData.append('receipt', this.expense.receipt);
      }
      
      axios.post('/expenses', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      }).then(response => {
        toast.success("Despesa registada com sucesso!");
      }).catch(error => {
        this.errorMessage = 'Erro ao submeter despesa. Verifique os dados e tente novamente.';
        toast.error(this.errorMessage);
      });
    },
    // Métodos para abrir/fechar o modal de imagem
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
    // Atualiza o zoom com a roda do mouse, mantendo o ponteiro como âncora
    onWheel(e) {
      e.preventDefault();
      const scaleAmount = 0.001; // Valor menor para suavizar o zoom
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
    // Métodos para panning via mouse drag
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
    }
  }
};
</script>

<style>
body {
  background-color: #f4f4f4;
}
</style>
