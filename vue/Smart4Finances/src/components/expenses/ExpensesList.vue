<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">As Minhas Despesas</h2>

    <!-- Filtros -->
    <div class="mb-4">
      <div class="flex mb-2">
        <div class="mr-5 ml-2">
          <label class="block">Categoria:</label>
          <select v-model="filters.category" class="mb-2">
            <option value="">Todas</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div class="mr-5 ml-2">
          <input type="number" v-model="filters.minPrice" placeholder="Preço mínimo" class="mr-2 p-2 border rounded" style="width: 15vh;"/>
          <input type="number" v-model="filters.maxPrice" placeholder="Preço máximo" class="p-2 border rounded" style="width: 15vh;"/>
        </div>
        <div class="flex mb-2 mr-5 ml-2">
          <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded"/>
          <input type="date" v-model="filters.endDate" class="p-2 border rounded"/>
        </div>
      </div>
      <button @click="addexpense" class="bg-green-500 text-white px-4 py-2 rounded">
        <i class="bi bi-plus-lg"></i>
      </button>
      <!-- Botão global de deletar aparece se houver alguma despesa selecionada -->
      <button v-if="selectedExpenses.length > 0" @click="deleteSelectedExpenses" class="bg-red-500 text-white px-4 py-2 rounded ml-2">
        <i class="bi bi-trash"></i> Eleminar Selecionados
      </button>
    </div>

    <!-- Tabela de despesas -->
    <table class="w-full">
      <thead>
        <tr>
          <th class="border px-2 py-1"></th>
          <th class="border px-2 py-1">Data</th>
          <th class="border px-2 py-1">Categoria</th>
          <th class="border px-2 py-1">Valor</th>
          <th class="border px-2 py-1">Descrição</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="expense in expenses" :key="expense.id">
          <td class="px-2 py-1 text-center">
            <input type="checkbox" :value="expense.id" v-model="selectedExpenses"/>
          </td>
          <td class="px-2 py-1">{{ expense.date }}</td>
          <td class="px-2 py-1">{{ getCategoryName(expense.category_id) }}</td>
          <td class="px-2 py-1">{{ expense.amount + " "+coin}}</td>
          <td class="px-2 py-1">{{ truncate(expense.description, 20) }}</td>
          <td class="px-2 py-1">
            <!-- Botão de delete individual -->
            <button @click="deleteExpense(expense.id)" class="bg-red-500 text-white px-2 py-1 rounded">
              <i class="bi bi-trash"></i>
            </button>
            <!-- Botão de visualizar -->
            <button @click="viewExpense(expense.id)" class="bg-blue-500 text-white px-2 py-1 rounded ml-1">
              <i class="bi bi-eye-fill"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loadingMore" class="mt-4 text-center">A Carregar mais...</div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg">
        <p class="mb-4">Tem certeza que deseja apagar a(s) despesa(s) selecionada(s)?</p>
        <div class="flex justify-end">
          <button @click="cancelDeletion" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancelar</button>
          <button @click="confirmDeletion" class="bg-red-500 text-white px-4 py-2 rounded">Confirmar</button>
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
    }
  }
};
</script>

<style scoped>
/* Estilos para o modal */
.fixed {
  position: fixed;
}
.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
