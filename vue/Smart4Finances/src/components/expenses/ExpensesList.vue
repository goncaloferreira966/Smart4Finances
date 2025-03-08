<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Minhas Despesas</h2>

    <!-- Filtros -->
    <div class="mb-4">
      <div class="flex mb-2">
        <div class="mr-5 ml-2">
          <label class="block ">Categoria:</label>
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
          <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded" />
          <input type="date" v-model="filters.endDate" class="p-2 border rounded" />
        </div>
      </div>
      <button @click="addexpense" class="bg-green-500 text-white px-4 py-2 rounded"><i class="bi bi-plus-lg"></i></button>
      <!-- Botão de filtro removido para aplicação automática -->
    </div>

    <!-- Tabela de despesas -->
    <table class="w-full border">
      <thead>
        <tr>
          <th class="border px-2 py-1">Data</th>
          <th class="border px-2 py-1">Categoria</th>
          <th class="border px-2 py-1">Valor</th>
          <th class="border px-2 py-1">Descrição</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="expense in expenses" :key="expense.id">
          <td class="border px-2 py-1">{{ expense.date }}</td>
          <td class="border px-2 py-1">{{ getCategoryName(expense.category_id) }}</td>
          <td class="border px-2 py-1">{{ expense.amount }}</td>
          <td class="border px-2 py-1">{{ expense.description }}</td>
          <td class="border px-2 py-1">
            <button @click="viewExpense(expense.id)" class="bg-blue-500 text-white px-2 py-1 rounded">
               <i class="bi bi-eye-fill"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loadingMore" class="mt-4 text-center">Carregando mais...</div>
  </div>
</template>

<script>
import axios from 'axios';
import debounce from 'lodash.debounce';

export default {
  data() {
    return {
      expenses: [],
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
      }
    };
  },
  created() {
    this.loadCategories();
    this.loadExpenses();
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    // Sempre que algum filtro mudar, dispara a função de aplicar filtros automaticamente
    'filters.category': 'applyFiltersDebounced',
    'filters.minPrice': 'applyFiltersDebounced',
    'filters.maxPrice': 'applyFiltersDebounced',
    'filters.startDate': 'applyFiltersDebounced',
    'filters.endDate': 'applyFiltersDebounced'
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
      console.log('passei aqui');
      axios.get('/expenses', { params })
        .then(response => {
          // O payload retornado contém a propriedade "data" com as despesas
          const payload = response.data;
          const newExpenses = payload.data;
          if (newExpenses && newExpenses.length > 0) {
            this.expenses = this.expenses.concat(newExpenses);
            this.page++;
          }
          // Opcional: Se a página atual for maior que a última página, podemos parar o infinite scroll
          if (this.page > payload.last_page) {
            this.loadingMore = false;
          } else {
            this.loadingMore = false;
          }
        })
        .catch(error => {
          console.error(error);
          this.loadingMore = false;
        });
    },
    handleScroll() {
      const scrollY = window.scrollY;
      const visible = window.innerHeight;
      const pageHeight = document.documentElement.scrollHeight;
      if (scrollY + visible >= pageHeight - 100 && !this.loadingMore) {
        this.loadingMore = true;
        this.loadExpenses();
      }
    },
    // Função chamada para aplicar os filtros reiniciando a listagem
    applyFilters() {
      this.loadExpenses(true);
    },
    // Função debounced para evitar múltiplas requisições consecutivas enquanto o usuário interage com os filtros
    applyFiltersDebounced: debounce(function () {
      console.log('Aplicando filtros...');
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
    }
  }
};
</script>

<style scoped>
/* Ajuste os estilos conforme necessário */
</style>
