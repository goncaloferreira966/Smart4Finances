<template>
<div class="w-full sm:max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 70%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Os Meus Limites Mensais</h2>

    <!-- Filtros com design melhorado -->
    <div class="mb-6">
      <div class="filters-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
          <div>
            <label class="block mb-2 text-gray-700 font-medium">Valor:</label>
            <div class="flex gap-2">
              <input type="number" v-model="filters.min_limit" placeholder="Mínimo" 
                    class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"/>
              <input type="number" v-model="filters.max_limit" placeholder="Máximo" 
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
          <button @click="addBudget" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
            <i class="bi bi-plus-lg mr-1"></i> Novo Limite
          </button>
          <!-- Botão global de deletar aparece se houver algum limite selecionado -->
          <button v-if="selectedBudgets.length > 0" @click="deleteSelectedBudgets" 
                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
            <i class="bi bi-trash mr-1"></i> Eleminar Selecionados ({{ selectedBudgets.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela de limites mensais -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[600px] border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
            <th class="border px-4 py-3 text-center w-10">
              <input type="checkbox" @click="toggleSelectAll" :checked="allSelected" />
            </th>
            <th class="border px-4 py-3 text-left">Data</th>
            <th class="border px-4 py-3 text-right">Valor</th>
            <th class="border px-4 py-3 text-left">Categoria</th>
            <th class="border px-4 py-3 text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="budget in budgets" :key="budget.id" class="border-b hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-center">
              <input type="checkbox" :value="budget.id" v-model="selectedBudgets"/>
            </td>
            <td class="px-4 py-3">{{ formatDate(budget.created_at) }}</td>
            <td class="px-4 py-3 text-right font-medium">{{ formatAmount(budget.limit_amount) }} {{ coin }}</td>
            <td class="px-4 py-3">{{ budget.category }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2 justify-center">
                <button @click="viewBudget(budget.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                  <i class="bi bi-eye-fill"></i>
                </button>
                <button @click="deleteBudget(budget.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="budgets.length === 0">
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
              <i class="bi bi-inbox text-4xl block mb-2"></i>
              Nenhum limite encontrado.
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
        <p class="mb-6">Tem certeza que deseja apagar {{ deletionTarget ? '1 limite' : selectedBudgets.length + ' limites' }}?</p>
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
import { useAuthStore } from '@/stores/auth';

export default {
  props: {
    reloadBudgetsList: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      coin: '',
      budgets: [],
      page: 1,
      selectedBudgets: [],
      perPage: 10,
      hasMoreData: true,
      loadingMore: false,
      filters: {
        min_limit: '',
        max_limit: '',
        startDate: '',
        endDate: '',
      },
      showDeleteModal: false,
      deletionTarget: null
    };
  },
  created() {
    this.loadBudgets();
    const authStore = useAuthStore();
    this.coin = authStore.user?.data?.coin;
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    'filters.min_limit': 'applyFiltersDebounced',
    'filters.max_limit': 'applyFiltersDebounced',
    'filters.startDate': 'applyFiltersDebounced',
    'filters.endDate': 'applyFiltersDebounced',
    reloadBudgetsList(newVal) {
      if (newVal) {
        this.loadBudgets(true); // força reset com a nova lógica
        this.$emit('update:reloadBudgetsList', false); // limpa o flag no App.vue
      }
    },
  },
  computed: {
    allSelected() {
      return this.budgets.length > 0 && this.selectedBudgets.length === this.budgets.length;
    }
  },
  methods: {
    loadBudgets(reset = false) {
      if (reset) {
        this.page = 1;
        this.budgets = [];
        this.selectedBudgets = [];
        this.hasMoreData = true;
      }

      if (!this.hasMoreData) {
        this.loadingMore = false;
        return;
      }

      const params = {
        page: this.page,
        perPage: this.perPage,
        min_limit: this.filters.min_limit,
        max_limit: this.filters.max_limit,
        startDate: this.filters.startDate,
        endDate: this.filters.endDate
      };

      axios.get('/budgets', { params })
        .then(response => {
          const payload = response.data;
          const newBudgets = payload.data;

          if (newBudgets && newBudgets.length > 0) {
            const existingIds = new Set(this.budgets.map(e => e.id));
            const uniqueNew = newBudgets.filter(e => !existingIds.has(e.id));
            if (uniqueNew.length > 0) {
              this.budgets = this.budgets.concat(uniqueNew);
              this.page++;
            } else {
              console.log('⚠️ Nenhum novo limite único. Página não incrementada.');
            }
          } else {
            this.hasMoreData = false;
            console.log('⛔ Sem mais dados a carregar.');
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

      if (scrollY + visible >= pageHeight - 100 && !this.loadingMore && this.hasMoreData) {
        this.loadingMore = true;
        this.loadBudgets();
      }
    },
    formatDate(dateString) {
      const date = new Date(Date.parse(dateString));
      if (isNaN(date)) return 'Data inválida';
      return date.toLocaleDateString('pt-PT');
    },
    formatAmount(amount) {
      return parseFloat(amount).toFixed(2);
    },
    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedBudgets = [];
      } else {
        this.selectedBudgets = this.budgets.map(budget => budget.id);
      }
    },
    resetFilters() {
      this.filters = {
        min_limit: '',
        max_limit: '',
        startDate: '',
        endDate: ''
      };
      this.applyFilters();
    },
    applyFilters() {
      this.loadBudgets(true);
    },
    applyFiltersDebounced: debounce(function () {
      this.applyFilters();
    }, 500),
    truncate(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    viewBudget(budgetId) {
      this.$emit("BudgetView", budgetId);
    },

    // Delete individual: define a target e abre a modal
    deleteBudget(budgetId) {
      this.deletionTarget = budgetId;
      this.showDeleteModal = true;
    },
    deleteSelectedBudgets() {
      if (this.selectedBudgets.length > 0) {
        this.deletionTarget = null; // indica múltipla
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
        axios.delete(`/budgets/${this.deletionTarget}`)
          .then(() => {
            this.budgets = this.budgets.filter(exp => exp.id !== this.deletionTarget);
            this.selectedBudgets = this.selectedBudgets.filter(id => id !== this.deletionTarget);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          });
      } else if (this.selectedBudgets.length > 0) {
        // Exclusão múltipla
        const deletePromises = this.selectedBudgets.map(id => axios.delete(`/budgets/${id}`));
        Promise.all(deletePromises)
          .then(() => {
            this.budgets = this.budgets.filter(exp => !this.selectedBudgets.includes(exp.id));
            this.selectedBudgets = [];
            this.showDeleteModal = false;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
          });
      }
    },
    addBudget(){
      this.$emit("addBudget");
    },
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