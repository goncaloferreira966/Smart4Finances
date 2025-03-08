<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Minhas Receitas</h2>

    <!-- Filtros -->
    <div class="mb-4">
      <div class="flex mb-2">
        <div class="mr-5 ml-2">
          <input type="text" v-model="filters.source" placeholder="Pesquisar fonte..." class="mb-2 p-2 border rounded" />
        </div>
        <div class="mr-5 ml-2">
          <input type="number" v-model="filters.minPrice" placeholder="Valor mínimo" class="mr-2 p-2 border rounded" style="width: 15vh;"/>
          <input type="number" v-model="filters.maxPrice" placeholder="Valor máximo" class="p-2 border rounded" style="width: 15vh;"/>
        </div>
        <div class="flex mb-2 mr-5 ml-2">
          <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded" />
          <input type="date" v-model="filters.endDate" class="p-2 border rounded" />
        </div>
      </div>
      <button @click="addIncome" class="bg-green-500 text-white px-4 py-2 rounded">
        <i class="bi bi-plus-lg"></i>
      </button>
      <!-- Botão de filtro removido para aplicação automática -->
    </div>

    <!-- Tabela de receitas -->
    <table class="w-full border">
      <thead>
        <tr>
          <th class="border px-2 py-1">Data</th>
          <th class="border px-2 py-1">Fonte</th>
          <th class="border px-2 py-1">Valor</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="income in incomes" :key="income.id">
          <td class="border px-2 py-1">{{ income.date }}</td>
          <td class="border px-2 py-1">{{ income.source }}</td>
          <td class="border px-2 py-1">{{ income.amount }}</td>
          <td class="border px-2 py-1">
            <button @click="viewIncome(income.id)" class="bg-blue-500 text-white px-2 py-1 rounded">
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
      incomes: [],
      page: 1,
      perPage: 15,
      loadingMore: false,
      filters: {
        source: '',
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: ''
      }
    };
  },
  created() {
    this.loadIncomes();
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    'filters.source': 'applyFiltersDebounced',
    'filters.minPrice': 'applyFiltersDebounced',
    'filters.maxPrice': 'applyFiltersDebounced',
    'filters.startDate': 'applyFiltersDebounced',
    'filters.endDate': 'applyFiltersDebounced'
  },
  methods: {
    loadIncomes(reset = false) {
      if (reset) {
        this.page = 1;
        this.incomes = [];
      }
      const params = {
        page: this.page,
        perPage: this.perPage,
        source: this.filters.source,
        minPrice: this.filters.minPrice,
        maxPrice: this.filters.maxPrice,
        startDate: this.filters.startDate,
        endDate: this.filters.endDate
      };
      axios.get('/incomes', { params })
        .then(response => {
          const payload = response.data;
          const newIncomes = payload.data;
          if (newIncomes && newIncomes.length > 0) {
            this.incomes = this.incomes.concat(newIncomes);
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
        this.loadIncomes();
      }
    },
    applyFilters() {
      this.loadIncomes(true);
    },
    applyFiltersDebounced: debounce(function () {
      this.applyFilters();
    }, 500),
    addIncome() {
      this.$emit("addIncome", null);
    },
    viewIncome(incomeId) {
      this.$emit("IncomeView", incomeId);
    }
  }
};
</script>

<style scoped>
/* Ajusta os estilos conforme necessário */
</style>
