<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 70%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">As Minhas Despesas</h2>

    <!-- Filtros com design melhorado -->
    <div class="mb-6">
      <div class="filters-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Categoria:</label>
            <select v-model="filters.category" class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
              <option value="">Todas as categorias</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Preço:</label>
            <div class="flex gap-2">
              <input type="number" v-model="filters.minPrice" placeholder="Mínimo" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"/>
              <input type="number" v-model="filters.maxPrice" placeholder="Máximo" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"/>
            </div>
          </div>
          
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Período:</label>
            <div class="flex gap-2">
              <input type="date" v-model="filters.startDate" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"/>
              <input type="date" v-model="filters.endDate" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"/>
            </div>
          </div>
        </div>
        
        <div class="flex justify-center gap-3 mt-4">
          <button @click="applyFilters" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
            <i class="bi bi-funnel mr-1"></i> Filtrar
          </button>
          <button @click="resetFilters" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
            <i class="bi bi-x-circle mr-1"></i> Limpar Filtros
          </button>
          <button @click="addexpense" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
            <i class="bi bi-plus-lg mr-1"></i> Nova Despesa
          </button>
          <!-- Botão global de deletar aparece se houver alguma despesa selecionada -->
          <button v-if="selectedExpenses.length > 0" @click="deleteSelectedExpenses" 
                 class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
            <i class="bi bi-trash mr-1"></i> Eleminar Selecionados ({{ selectedExpenses.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela de despesas -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[600px] border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
            <th class="border px-4 py-3 text-center w-10">
              <input type="checkbox" @click="toggleSelectAll" :checked="allSelected" />
            </th>
            <th class="border px-4 py-3 text-left">Data</th>
            <th class="border px-4 py-3 text-left">Categoria</th>
            <th class="border px-4 py-3 text-right">Valor</th>
            <th class="border px-4 py-3 text-left">Descrição</th>
            <th class="border px-4 py-3 text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="expense in expenses" :key="expense.id" class="border-b hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-center">
              <input type="checkbox" :value="expense.id" v-model="selectedExpenses"/>
            </td>
            <td class="px-4 py-3">{{ formatDate(expense.date) }}</td>
            <td class="px-4 py-3">{{ getCategoryName(expense.category_id) }}</td>
            <td class="px-4 py-3 text-right font-medium">{{ formatAmount(expense.amount) }} {{ coin }}</td>
            <td class="px-4 py-3">{{ truncate(expense.description, 30) }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-center">
                <button @click="viewExpense(expense.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                  <i class="bi bi-eye-fill"></i>
                </button>
                <button @click="deleteExpense(expense.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="expenses.length === 0">
            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
              <i class="bi bi-inbox text-4xl block mb-2"></i>
              Nenhuma despesa encontrada.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="loadingMore" class="mt-4 text-center">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
      <p class="mt-2 text-gray-600">A Carregar mais...</p>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <h3 class="text-xl font-bold mb-2">Confirmar Exclusão</h3>
        <p class="mb-6">Tem certeza que deseja apagar {{ deletionTarget ? '1 despesa' : selectedExpenses.length + ' despesas' }}?</p>
        <div class="flex justify-end gap-3">
          <button @click="cancelDeletion" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
            Cancelar
          </button>
          <button @click="confirmDeletion" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
            Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import debounce from 'lodash.debounce';
import { useAuthStore } from "@/stores/auth";

export default {
  props: {
    reloadExpensesList: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      coin: "",
      expenses: [],
      hasMoreData: true,
      categories: [],
      page: 1,
      perPage: 15,
      loadingMore: false,
      filters: {
        category: '',
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: ''
      },
      selectedExpenses: [],
      showDeleteModal: false,
      deletionTarget: null
    };
  },
  created() {
    this.incomes = [];
    this.page = 1;
    this.loadCategories();
    this.loadExpenses(true);
    const authStore = useAuthStore();
    this.coin = (authStore.user?.data?.coin);  
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    'filters.category': 'applyFiltersDebounced',
    'filters.minPrice': 'applyFiltersDebounced',
    'filters.maxPrice': 'applyFiltersDebounced',
    'filters.startDate': 'applyFiltersDebounced',
    'filters.endDate': 'applyFiltersDebounced',
    reloadExpensesList(newVal) {
    if (newVal) {
      this.loadExpenses(true);
      this.$emit('update:reloadExpensesList', false); // para resetar o estado no pai
    }
  }
  },
  methods: {
    loadCategories() {
      axios.get('/categories')
        .then(response => {
          this.categories = response.data;
        });
    },
    loadExpenses(reset = false) {
      if (reset) {
        this.page = 1;
        this.expenses = [];
        this.selectedExpenses = [];
        this.hasMoreData = true;
      }

      if (!this.hasMoreData) {
        this.loadingMore = false;
        return;
      }

      const params = {
        page: this.page,
        perPage: this.perPage,
        category: this.filters.category,
        minPrice: this.filters.minPrice,
        maxPrice: this.filters.maxPrice,
        startDate: this.filters.startDate,
        endDate: this.filters.endDate
      };
      axios.get('/expenses', { params })
        .then(response => {
          const payload = response.data;
          const newExpenses = payload.data;

          if (newExpenses && newExpenses.length > 0) {
            const existingIds = new Set(this.expenses.map(e => e.id));
            const uniqueNewExpenses = newExpenses.filter(e => !existingIds.has(e.id));
            
            if (uniqueNewExpenses.length > 0) {
              this.expenses = this.expenses.concat(uniqueNewExpenses);
              this.page++;
            } else {
            }
          } else {
            this.hasMoreData = false;
          }

          this.loadingMore = false;
        })
        .catch(error => {
          this.loadingMore = false;
        });
    },
    handleScroll() {
      const scrollY = window.scrollY;
      const visible = window.innerHeight;
      const pageHeight = document.documentElement.scrollHeight;

      if (scrollY + visible >= pageHeight - 100 && !this.loadingMore && this.hasMoreData) {
        this.loadingMore = true;
        this.loadExpenses();
      }
    },
    applyFilters() {
      this.loadExpenses(true);
    },
    applyFiltersDebounced: debounce(function () {
      this.applyFilters();
    }, 500),
    addexpense() {
      this.$emit("addexpense", null);
    },
    getCategoryName(categoryId) {
      const cat = this.categories.find(c => c.id === categoryId);
      return cat ? cat.name : '';
    },
    viewExpense(expenseId) {
      this.$emit("ExpenseView", expenseId);
    },
    // Delete individual: define a target e abre a modal
    deleteExpense(expenseId) {
      this.deletionTarget = expenseId;
      this.showDeleteModal = true;
    },
    // Delete múltipla: abre a modal se houver despesas selecionadas
    deleteSelectedExpenses() {
      if (this.selectedExpenses.length > 0) {
        this.deletionTarget = null; // indica deleção múltipla
        this.showDeleteModal = true;
      }
    },
    cancelDeletion() {
      this.showDeleteModal = false;
      this.deletionTarget = null;
    },
    truncate(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    confirmDeletion() {
      if (this.deletionTarget) {
        // Exclusão individual
        axios.delete(`/expenses/${this.deletionTarget}`)
          .then(() => {
            this.expenses = this.expenses.filter(exp => exp.id !== this.deletionTarget);
            this.selectedExpenses = this.selectedExpenses.filter(id => id !== this.deletionTarget);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          });
      } else if (this.selectedExpenses.length > 0) {
        // Exclusão múltipla
        const deletePromises = this.selectedExpenses.map(id => axios.delete(`/expenses/${id}`));
        Promise.all(deletePromises)
          .then(() => {
            this.expenses = this.expenses.filter(exp => !this.selectedExpenses.includes(exp.id));
            this.selectedExpenses = [];
            this.showDeleteModal = false;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
          });
      }
    },
    resetFilters() {
      this.filters = {
        category: '',
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: ''
      };
      this.applyFilters();
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('pt-PT');
    },
    formatAmount(amount) {
      return parseFloat(amount).toFixed(2);
    },
    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedExpenses = [];
      } else {
        this.selectedExpenses = this.expenses.map(e => e.id);
      }
    }
  },
  computed: {
    allSelected() {
      return this.expenses.length > 0 && this.selectedExpenses.length === this.expenses.length;
    }
  }
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
