<template>
<div class="w-full sm:max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 70%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Os Meus Limites Mensais</h2>

    <!-- Filtros -->
    <div class="mb-4">
      <div class="flex mb-2">
        <div class="mr-5 ml-2">
          <input type="number" v-model="filters.min_limit" placeholder="Valor mínimo" class="mr-2 p-2 border rounded"
            style="width: 15vh;" />
          <input type="number" v-model="filters.max_limit" placeholder="Valor máximo" class="p-2 border rounded"
            style="width: 15vh;" />
        </div>
        <div class="flex mb-2 mr-5 ml-2">
          <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded" />
          <input type="date" v-model="filters.endDate" class="p-2 border rounded" />
        </div>
      </div>
      <button @click="addBudget" class="bg-green-500 text-white px-4 py-2 rounded">
        <i class="bi bi-plus-lg"></i>
      </button>
      <button v-if="selectedBudgets.length > 0" @click="deleteSelectedBudgets" class="bg-red-500 text-white px-4 py-2 rounded ml-2">
        <i class="bi bi-trash"></i> Eleminar Selecionados
      </button>
    </div>

    <!-- Tabela de investimentos -->
    <table class="w-full">
      <thead>
        <tr>
          <th class="border px-2 py-1"></th>
          <th class="border px-2 py-1">Data</th>
          <th class="border px-2 py-1">Valor</th>
          <th class="border px-2 py-1">Categoria</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="budget in budgets" :key="budget.id">
          <td class="px-2 py-1 text-center">
            <input type="checkbox" :value="budget.id" v-model="selectedBudgets"/>
          </td>
          <td class="px-2 py-1">{{ formatDate(budget.created_at) }}</td>
          <td class="px-2 py-1">{{ budget.limit_amount + ' ' + coin }}</td>
          <td class="px-2 py-1">{{ budget.category }}</td>
          <td class="px-2 py-1">
            <!-- Botão de delete individual -->
            <button @click="deleteBudget(budget.id)" class="bg-red-500 text-white px-2 py-1 rounded">
              <i class="bi bi-trash"></i>
            </button>
            <!-- Botão de visualizar -->
            <button @click="viewBudget(budget.id)" class="bg-blue-500 text-white px-2 py-1 rounded ml-1">
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
        <p class="mb-4">Tem certeza que deseja apagar o(s) limites(s) selecionado(s)?</p>
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

          console.log(payload);

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
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
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