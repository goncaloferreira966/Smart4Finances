<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 70%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">As Minhas Receitas</h2>

    <!-- Filtros com design melhorado -->
    <div class="mb-6">
      <div class="filters-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Fonte:</label>
            <input type="text" v-model="filters.source" placeholder="Pesquisar fonte..." 
                 class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
          </div>
          
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Valor:</label>
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
          <button @click="addIncome" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
            <i class="bi bi-plus-lg mr-1"></i> Nova Receita
          </button>
          <!-- Botão global de deletar aparece se houver alguma receita selecionada -->
          <button v-if="selectedIncomes.length > 0" @click="deleteSelectedIncomes" 
                 class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
            <i class="bi bi-trash mr-1"></i> Eleminar Selecionados ({{ selectedIncomes.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela de receitas -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[600px] border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
            <th class="border px-4 py-3 text-center w-10">
              <input type="checkbox" @click="toggleSelectAll" :checked="allSelected" />
            </th>
            <th class="border px-4 py-3 text-left">Data</th>
            <th class="border px-4 py-3 text-left">Fonte</th>
            <th class="border px-4 py-3 text-right">Valor</th>
            <th class="border px-4 py-3 text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="income in incomes" :key="income.id" class="border-b hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-center">
              <input type="checkbox" :value="income.id" v-model="selectedIncomes"/>
            </td>
            <td class="px-4 py-3">{{ formatDate(income.date) }}</td>
            <td class="px-4 py-3">{{ income.source }}</td>
            <td class="px-4 py-3 text-right font-medium">{{ formatAmount(income.amount) }} {{ coin }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-center">
                <button @click="viewIncome(income.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                  <i class="bi bi-eye-fill"></i>
                </button>
                <button @click="deleteIncome(income.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="incomes.length === 0">
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
              <i class="bi bi-inbox text-4xl block mb-2"></i>
              Nenhuma receita encontrada.
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
        <p class="mb-6">Tem certeza que deseja apagar {{ deletionTarget ? '1 receita' : selectedIncomes.length + ' receitas' }}?</p>
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
    this.incomes = [];
    this.page = 1;
    this.loadIncomes(true); // true para reset
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
  computed: {
    allSelected() {
      return this.incomes.length > 0 && this.selectedIncomes.length === this.incomes.length;
    }
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
    resetFilters() {
      this.filters = {
        source: '',
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: ''
      };
      this.applyFilters();
    },
    addIncome() {
      this.$emit("addIncome", null);
    },
    viewIncome(incomeId) {
      this.$emit("IncomeView", incomeId);
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
        this.selectedIncomes = [];
      } else {
        this.selectedIncomes = this.incomes.map(inc => inc.id);
      }
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
.filters-container {
  width: 100%;
}

@media (max-width: 640px) {
  button i {
    margin-right: 4px;
  }
}
</style>
