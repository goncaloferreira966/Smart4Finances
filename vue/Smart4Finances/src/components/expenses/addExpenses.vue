<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50%;">
    <h2 class="text-2xl font-bold mb-4">{{ isEditMode ? 'Editar Despesa' : 'Registar Despesa' }}</h2>

    <label class="block mb-2">Carregar Recibo:</label>
    <input type="file" @change="onFileChange" accept="image/*" class="mb-4" />

    <div v-if="receiptPreview" class="mb-4">
      <img :src="receiptPreview" alt="Recibo" class="w-full rounded cursor-pointer" @click="openImageModal" />
    </div>

    <form @submit.prevent="submitExpense">
      <div class="mb-4">
        <div class="flex justify-between items-center mb-2">
          <label>Categoria:</label>
          <button type="button" @click="showAddCategoryModal = true" class="text-blue-500 hover:text-blue-700">
            <i class="bi bi-plus-circle"></i> Nova Categoria
          </button>
        </div>
        <input type="text" v-model="searchQuery" placeholder="Pesquisar categoria..."
          class="w-full p-2 border rounded mb-2" />
        <div class="border rounded mb-4" style="max-height: 200px; overflow-y: auto;">
          <div v-for="category in filteredCategories" :key="category.id" 
              class="p-2 cursor-pointer hover:bg-gray-200 flex justify-between items-center"
              @click="selectCategory(category)">
            <span>{{ category.name }}</span>
            <div class="flex items-center">
              <button v-if="category.user_id !== null" type="button" @click.stop="editCategoryName(category)" 
                  class="text-blue-500 hover:text-blue-700 mr-2" title="Editar categoria">
                <i class="bi bi-pencil"></i>
              </button>
              <button v-if="category.user_id !== null" type="button" @click.stop="confirmDeleteCategory(category)" 
                  class="text-red-500 hover:text-red-700" title="Excluir categoria">
                <i class="bi bi-trash"></i>
              </button>
              <span v-else class="text-gray-400 text-xs italic">Categoria geral</span>
            </div>
          </div>
          <div v-if="filteredCategories.length === 0" class="p-2 text-gray-500">
            Nenhuma categoria encontrada.
          </div>
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

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 bthouver">
        <i class="bi bi-check2-circle"></i>
        {{ isEditMode ? 'Atualizar' : 'Confirmar' }}
      </button>
    </form>
    <button @click="isEditMode ? viewExpense(expense.id) : ExpensesList()" class="mt-4 w-full bg-red-500 text-white py-2 rounded hover:bg-black">
      <i class="bi bi-arrow-left-short"></i>
      Voltar
    </button>

    <!-- Modal de visualização de categorias -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">Todas as Categorias</h3>
        
        <div class="mb-4 flex items-center">
          <input type="text" v-model="modalSearchQuery" placeholder="Pesquisar categoria..." 
                 class="flex-grow p-2 border rounded mr-2" />
          <button @click="showAddCategoryModal = true; showModal = false;" 
                  class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600">
            <i class="bi bi-plus-circle"></i> Nova
          </button>
        </div>
        
        <div class="overflow-y-auto max-h-96">
          <table class="min-w-full bg-white">
            <thead>
              <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Nome</th>
                <th class="py-3 px-6 text-center">Ações</th>
              </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
              <tr v-for="category in modalFilteredCategories" :key="category.id" 
                  class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left">
                  {{ category.name }}
                  <span v-if="category.user_id === null" class="ml-2 text-xs italic text-blue-500">(geral)</span>
                  <span v-else class="ml-2 text-xs italic text-green-500">(personalizada)</span>
                </td>
                <td class="py-3 px-6 text-center">
                  <div class="flex item-center justify-center">
                    <button @click="selectCategoryFromModal(category)" 
                            class="transform hover:text-blue-500 hover:scale-110 mr-3">
                      <i class="bi bi-check-circle"></i>
                    </button>
                    <button v-if="category.user_id !== null" @click.stop="editCategoryName(category)"
                            class="transform hover:text-blue-500 hover:scale-110 mr-3">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button v-if="category.user_id !== null" @click="confirmDeleteCategory(category)"
                            class="transform hover:text-red-500 hover:scale-110">
                      <i class="bi bi-trash"></i>
                    </button>
                    <span v-else class="text-gray-400 text-xs italic">Não editável</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="flex justify-end mt-4">
          <button @click="showModal = false" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Fechar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para adicionar/editar categoria -->
    <div v-if="showAddCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">{{ editingCategory ? 'Editar Categoria' : 'Nova Categoria' }}</h3>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="categoryName">
            Nome da Categoria
          </label>
          <input id="categoryName" type="text" v-model="newCategory.name"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              placeholder="Nome da categoria" />
        </div>
        
        <div class="flex justify-end space-x-2">
          <button @click="cancelCategoryEdit"
              class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
            Cancelar
          </button>
          <button @click="saveCategory" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
            {{ editingCategory ? 'Atualizar' : 'Adicionar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão de categoria -->
    <div v-if="showDeleteCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Confirmar Exclusão</h3>
        <p class="mb-4">Tem certeza que deseja excluir a categoria <strong>{{ categoryToDelete?.name }}</strong>?</p>
        
        <div class="flex justify-end space-x-2">
          <button @click="showDeleteCategoryModal = false"
              class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
            Cancelar
          </button>
          <button @click="deleteCategory" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de visualização da imagem com zoom e pan -->
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
      @click="closeImageModal">
      <button @click.stop="closeImageModal" class="absolute top-4 right-4 text-white text-2xl">
        <i class="bi bi-x-circle"></i>
      </button>
      <div @click.stop class="max-w-3xl max-h-[80vh] overflow-hidden select-none" @wheel="onWheel">
        <img :src="receiptPreview" alt="Ampliação do Recibo" :style="imageStyle" @mousedown="onMouseDown"
          @mousemove="onMouseMove" @mouseup="onMouseUp" @mouseleave="onMouseUp" class="max-w-none" />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import Tesseract from 'tesseract.js';

export default {
  props: {
    expenseId: {
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
      zoomLevel: 1,
      offsetX: 0,
      offsetY: 0,
      isDragging: false,
      dragStartX: 0,
      dragStartY: 0,
      startOffsetX: 0,
      startOffsetY: 0,
      // Novos campos para gerenciamento de categorias
      showAddCategoryModal: false,
      showDeleteCategoryModal: false,
      categoryToDelete: null,
      newCategory: {
        name: '',
        id: null
      },
      editingCategory: false
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
    this.loadCategories();
    if (this.expenseId) {
      this.isEditMode = true;
      this.loadExpense();
    }
  },
  methods: {
    loadCategories() {
      axios.get('/categories')
        .then(response => {
          this.categories = response.data;
        })
        .catch(error => {
          console.error('Erro ao buscar categorias:', error);
          toast.error('Erro ao carregar categorias');
        });
    },
    loadExpense() {
      axios.get(`/expenses/${this.expenseId}`)
        .then(response => {
          this.expense = response.data;
          this.receiptPreview = response.data.receipt ? import.meta.env.VITE_API_DOMAIN + '/storage/' + response.data.receipt : null;
          this.expense.recurring_interval = response.data.recurring_interval != 'null' ? response.data.recurring_interval : '0';
          // Se as categorias já foram carregadas, atualiza o searchQuery
          if (this.categories.length > 0) {
            const cat = this.categories.find(c => c.id == this.expense.category_id);
            if (cat) {
              this.searchQuery = cat.name;
            }
          }
        })
        .catch(error => {
          console.error('Erro ao carregar despesa:', error);
          toast.error('Erro ao carregar dados da despesa');
        });
    },
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        toast.success("Imagem aceite!");
        this.expense.receipt = file;
        this.receiptPreview = URL.createObjectURL(file);
        this.processImage(file);
      }
    },
    processImage(file) {
      this.processing = true;
      const reader = new FileReader();
      reader.onload = (e) => {
        Tesseract.recognize(e.target.result, 'por', {
          logger: (m) => {
            console.log(m);
          }
        }).then(({ data: { text } }) => {
          this.processing = false;
          console.log("Texto extraído:", text);

          // Extrair valor
          const valueMatch = text.match(/(?:EUR|€|EURO)?\s*(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2}))/i);
          if (valueMatch && valueMatch[1]) {
            const extractedValue = valueMatch[1].replace(',', '.');
            this.expense.amount = this.formatAmountValue(extractedValue);
          }

          // Extrair data
          const dateStr = this.extractDate(text);
          if (dateStr) {
            const formattedDate = this.formatDate(dateStr);
            if (formattedDate) {
              this.expense.date = formattedDate;
            }
          } else {
            // Se não conseguir extrair a data, usar a data atual
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            this.expense.date = `${year}-${month}-${day}`;
          }

          // Extrair descrição (primeiras linhas do texto ou algo relevante)
          const description = this.cleanValue(text.split('\n')[0]);
          if (description) {
            this.expense.description = description.substring(0, 100); // Limitar a 100 caracteres
          }
        }).catch(error => {
          this.processing = false;
          console.error("Erro ao processar a imagem:", error);
        });
      };
      reader.readAsDataURL(file);
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
      if (!isNaN(num)) {
        this.expense.amount = num.toFixed(2);
      } else {
        this.expense.amount = '0.00';
      }
    },
    formatAmountValue(value) {
      let num = parseFloat(value.replace(',', '.'));
      return !isNaN(num) ? num.toFixed(2) : '0.00';
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

      if (this.isEditMode) {
        axios.put(`/expenses/${this.expenseId}`, formData, {
          headers: { 'Content-Type': 'application/json' }
        }).then(response => {
          toast.success("Despesa atualizada com sucesso!");
          this.$emit("ExpensesList", { reload: true } );
        }).catch(error => {
          this.errorMessage = 'Erro ao atualizar despesa. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      } else {
        axios.post('/expenses', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        }).then(response => {
          toast.success("Despesa registada com sucesso!");
          this.$emit("ExpensesList", { reload: true } );
        }).catch(error => {
          this.errorMessage = 'Erro ao submeter despesa. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      }
    },
    // Métodos para gerenciamento de categoria
    saveCategory() {
      if (!this.newCategory.name.trim()) {
        toast.error('O nome da categoria é obrigatório');
        return;
      }

      const categoryData = {
        name: this.newCategory.name.trim()
      };

      if (this.editingCategory) {
        // Atualiza categoria existente
        axios.put(`/categories/${this.newCategory.id}`, categoryData)
          .then(response => {
            toast.success('Categoria atualizada com sucesso!');
            this.loadCategories();
            this.cancelCategoryEdit();
          })
          .catch(error => {
            console.error('Erro ao atualizar categoria:', error);
            toast.error('Erro ao atualizar categoria. Tente novamente.');
          });
      } else {
        // Adiciona nova categoria
        axios.post('/categories', categoryData)
          .then(response => {
            toast.success('Categoria adicionada com sucesso!');
            this.loadCategories();
            this.cancelCategoryEdit();
          })
          .catch(error => {
            console.error('Erro ao adicionar categoria:', error);
            toast.error('Erro ao adicionar categoria. Tente novamente.');
          });
      }
    },
    editCategoryName(category) {
      // Não permite editar categorias do sistema
      if (category.user_id === null) {
        toast.error('Categorias do sistema não podem ser editadas');
        return;
      }
      
      event.stopPropagation(); // Impede que o clique propague
      this.editingCategory = true;
      this.newCategory.id = category.id;
      this.newCategory.name = category.name;
      this.showAddCategoryModal = true;
      this.showModal = false; // Fecha o modal de visualização, se estiver aberto
    },
    cancelCategoryEdit() {
      this.showAddCategoryModal = false;
      this.editingCategory = false;
      this.newCategory = {
        name: '',
        id: null
      };
    },
    confirmDeleteCategory(category) {
      // Impede exclusão de categorias do sistema (user_id nulo)
      if (category.user_id === null) {
        toast.error('Categorias do sistema não podem ser excluídas');
        return;
      }
      
      event.stopPropagation(); // Impede que o clique propague para o item da categoria
      this.categoryToDelete = category;
      this.showDeleteCategoryModal = true;
    },
    deleteCategory() {
      if (!this.categoryToDelete || this.categoryToDelete.user_id === null) {
        toast.error('Esta categoria não pode ser excluída');
        this.showDeleteCategoryModal = false;
        return;
      }

      axios.delete(`/categories/${this.categoryToDelete.id}`)
        .then(response => {
          toast.success('Categoria excluída com sucesso!');
          this.loadCategories();
          this.showDeleteCategoryModal = false;
          this.categoryToDelete = null;
          
          // Se a categoria excluída era a selecionada, limpa a seleção
          if (this.expense.category_id === this.categoryToDelete.id) {
            this.expense.category_id = '';
            this.searchQuery = '';
          }
        })
        .catch(error => {
          console.error('Erro ao excluir categoria:', error);
          
          // Verifica se o erro é devido a categoria em uso
          if (error.response && error.response.status === 422) {
            toast.error('Esta categoria não pode ser excluída porque está sendo usada em despesas.');
          } else {
            toast.error('Erro ao excluir a categoria. Tente novamente.');
          }
          
          this.showDeleteCategoryModal = false;
        });
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
    viewExpense(expenseId) {
      this.$emit("ExpenseView", expenseId);
    },
    ExpensesList() {
      this.$emit("ExpensesList", null);
    }
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
