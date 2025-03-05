<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Minhas Despesas</h2>
    
    <!-- Filtros -->
    <div class="mb-4">
      <label class="block">Categoria:</label>
      <select v-model="filters.category" class="mb-2">
        <option value="">Todas</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <div class="flex mb-2">
        <input type="number" v-model="filters.minPrice" placeholder="Preço mínimo" class="mr-2 p-2 border rounded"/>
        <input type="number" v-model="filters.maxPrice" placeholder="Preço máximo" class="p-2 border rounded"/>
      </div>
      <div class="flex mb-2">
        <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded"/>
        <input type="date" v-model="filters.endDate" class="p-2 border rounded"/>
      </div>
      <button @click="applyFilters" class="bg-blue-500 text-white px-4 py-2 rounded">Filtrar</button>
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
            <button @click="viewExpense(expense.id)" class="bg-green-500 text-white px-2 py-1 rounded">
              Ver
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
export default {
  data() {
    return {
      expenses: [],
      categories: [],
      page: 1,
      perPage: 10,
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
      axios.get('/expenses', { params })
        .then(response => {
          if(response.data.length > 0) {
            this.expenses = this.expenses.concat(response.data);
            this.page++;
          }
          this.loadingMore = false;
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
    applyFilters() {
      this.loadExpenses(true);
    },
    getCategoryName(categoryId) {
      const cat = this.categories.find(c => c.id === categoryId);
      return cat ? cat.name : '';
    },
    viewExpense(expenseId) {
      this.$router.push({ name: 'ExpenseView', params: { id: expenseId } });
    }
  }
};
</script>

<style scoped>
/* Ajuste os estilos conforme necessário */
</style>
