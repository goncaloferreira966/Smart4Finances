<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">As Minhas Receitas</h2>

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
      <!-- Botão global de deleção para os selecionados -->
      <button v-if="selectedIncomes.length > 0" @click="deleteSelectedIncomes" class="bg-red-500 text-white px-4 py-2 rounded ml-2">
        <i class="bi bi-trash"></i> Eleminar Selecionados
      </button>
    </div>

    <!-- Tabela de receitas -->
    <table class="w-full">
      <thead>
        <tr>
          <th class="px-2 py-1 text-center"></th>
          <th class="border px-2 py-1">Data</th>
          <th class="border px-2 py-1">Fonte</th>
          <th class="border px-2 py-1">Valor</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="income in incomes" :key="income.id">
          <td class="px-2 py-1 text-center">
            <input type="checkbox" :value="income.id" v-model="selectedIncomes"/>
          </td>
          <td class="px-2 py-1">{{ income.date }}</td>
          <td class="px-2 py-1">{{ income.source }}</td>
          <td class="px-2 py-1">{{ income.amount + " " + coin}}</td>
          <td class="px-2 py-1">
            <!-- Botão de delete individual -->
            <button @click="deleteIncome(income.id)" class="bg-red-500 text-white px-2 py-1 rounded">
              <i class="bi bi-trash"></i>
            </button>
            <!-- Botão de visualizar -->
            <button @click="viewIncome(income.id)" class="bg-blue-500 text-white px-2 py-1 rounded ml-1">
              <i class="bi bi-eye-fill"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loadingMore" class="mt-4 text-center">Carregando mais...</div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg">
        <p class="mb-4">Tem certeza que deseja deletar a(s) receita(s) selecionada(s)?</p>
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
  data() {
    return {
      coin: "",
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
      },
      selectedIncomes: [],
      showDeleteModal: false,
      deletionTarget: null // Se definido, é exclusão individual; caso contrário, será exclusão múltipla
    };
  },
  created() {
    this.loadIncomes();
    window.addEventListener('scroll', this.handleScroll);
    const authStore = useAuthStore();
    this.coin = (authStore.user?.data?.coin);  
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
        this.selectedIncomes = [];
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
    },
    // Abre modal para exclusão individual
    deleteIncome(incomeId) {
      this.deletionTarget = incomeId;
      this.showDeleteModal = true;
    },
    // Abre modal para exclusão múltipla
    deleteSelectedIncomes() {
      if (this.selectedIncomes.length > 0) {
        this.deletionTarget = null; // indica deleção múltipla
        this.showDeleteModal = true;
      }
    },
    cancelDeletion() {
      this.showDeleteModal = false;
      this.deletionTarget = null;
    },
    confirmDeletion() {
      if (this.deletionTarget) {
        // Exclusão individual
        axios.delete(`/incomes/${this.deletionTarget}`)
          .then(() => {
            this.incomes = this.incomes.filter(inc => inc.id !== this.deletionTarget);
            this.selectedIncomes = this.selectedIncomes.filter(id => id !== this.deletionTarget);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          });
      } else if (this.selectedIncomes.length > 0) {
        // Exclusão múltipla
        const deletePromises = this.selectedIncomes.map(id => axios.delete(`/incomes/${id}`));
        Promise.all(deletePromises)
          .then(() => {
            this.incomes = this.incomes.filter(inc => !this.selectedIncomes.includes(inc.id));
            this.selectedIncomes = [];
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
