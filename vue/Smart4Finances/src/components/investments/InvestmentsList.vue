<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 70%;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Os Meus Investimentos</h2>
    <button
      @click="toggleView"
      class="mb-6 bg-gray-800 text-gray-100 font-medium py-3 px-6 rounded-lg 
            shadow-[5px_5px_10px_rgba(0,0,0,0.3),-5px_-5px_10px_rgba(255,255,255,0.05)] 
            hover:shadow-[3px_3px_6px_rgba(0,0,0,0.3),-3px_-3px_6px_rgba(255,255,255,0.05)] 
            active:shadow-inset transition-all duration-300 border border-gray-700"
    >
      <span class="flex items-center gap-2">
        <i class="bi" :class="showInvestments ? 'bi-bar-chart-line' : 'bi-cash-stack'"></i>
        {{ showInvestments ? 'Mercados Financeiros' : 'Os Meus Investimentos' }}
      </span>
    </button>
    <div v-if="showInvestments">
      <!-- Filtros com design melhorado -->
      <div class="mb-6">
        <div class="filters-container">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
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
            <button @click="addInvestment" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
              <i class="bi bi-plus-lg mr-1"></i> Novo Investimento
            </button>
            <!-- Botão global de deletar aparece se houver algum investimento selecionado -->
            <button v-if="selectedInvestments.length > 0" @click="deleteSelectedInvestments" 
                  class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
              <i class="bi bi-trash mr-1"></i> Eleminar Selecionados ({{ selectedInvestments.length }})
            </button>
          </div>
        </div>
      </div>

      <!-- Tabela de investimentos -->
      <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
              <th class="border px-4 py-3 text-center w-10">
                <input type="checkbox" @click="toggleSelectAll" :checked="allSelected" />
              </th>
              <th class="border px-4 py-3 text-left">Data</th>
              <th class="border px-4 py-3 text-right">Valor</th>
              <th class="border px-4 py-3 text-left">Tipo</th>
              <th class="border px-4 py-3 text-right">ROI</th>
              <th class="border px-4 py-3 text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="investment in investments" :key="investment.id" class="border-b hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-center">
                <input type="checkbox" :value="investment.id" v-model="selectedInvestments" />
              </td>
              <td class="px-4 py-3">{{ formatDate(investment.created_at) }}</td>
              <td class="px-4 py-3 text-right font-medium">{{ formatAmount(investment.amount) }} {{ coin }}</td>
              <td class="px-4 py-3">{{ investment.type }}</td>
              <td class="px-4 py-3 text-right font-medium">{{ investment.roi }} %</td>
              <td class="px-4 py-3">
                <div class="flex gap-2 justify-center">
                  <button @click="viewInvestment(investment.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                  <button @click="deleteInvestment(investment.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="investments.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                <i class="bi bi-inbox text-4xl block mb-2"></i>
                Nenhum investimento encontrado.
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
          <p class="mb-6">Tem certeza que deseja apagar {{ deletionTarget ? '1 investimento' : selectedInvestments.length + ' investimentos' }}?</p>
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
    <div v-else>
      <!-- Componente TradingView -->
      <tradingview />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import debounce from 'lodash.debounce';
import { useAuthStore } from '@/stores/auth';
import tradingview from './tradingview.vue';

export default {
  components: { tradingview },

  props: {
    reloadInvestmentsList: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      showInvestments: true,
      coin: '',
      investments: [],
      page: 1,
      selectedInvestments: [],
      perPage: 10,
      hasMoreData: true,
      loadingMore: false,
      filters: {
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: '',
      },
      showDeleteModal: false,
      deletionTarget: null,
      tradingview,
    };
  },
  created() {
    this.loadInvestments();
    const authStore = useAuthStore();
    this.coin = authStore.user?.data?.coin;
    window.addEventListener('scroll', this.handleScroll);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    'filters.minPrice': 'applyFiltersDebounced',
    'filters.maxPrice': 'applyFiltersDebounced',
    'filters.startDate': 'applyFiltersDebounced',
    'filters.endDate': 'applyFiltersDebounced',
    reloadInvestmentsList(newVal) {
      if (newVal) {
        this.loadInvestments(true); // força reset com a nova lógica
        this.$emit('update:reloadInvestmentsList', false); // limpa o flag no App.vue
      }
    },
  },
  computed: {
    allSelected() {
      return this.investments.length > 0 && this.selectedInvestments.length === this.investments.length;
    }
  },
  methods: {
    toggleView() {
      this.showInvestments = !this.showInvestments;
    },
    loadInvestments(reset = false) {
      if (reset) {
        this.page = 1;
        this.investments = [];
        this.selectedInvestments = [];
        this.hasMoreData = true;
      }

      if (!this.hasMoreData) {
        this.loadingMore = false;
        return;
      }

      const params = {
        page: this.page,
        perPage: this.perPage,
        minPrice: this.filters.minPrice,
        maxPrice: this.filters.maxPrice,
        startDate: this.filters.startDate,
        endDate: this.filters.endDate
      };

      axios.get('/investments', { params })
        .then(response => {
          const payload = response.data;
          const newInvestments = payload.data;

          if (newInvestments && newInvestments.length > 0) {
            const existingIds = new Set(this.investments.map(e => e.id));
            const uniqueNew = newInvestments.filter(e => !existingIds.has(e.id));
            if (uniqueNew.length > 0) {
              this.investments = this.investments.concat(uniqueNew);
              this.page++;
            } else {
              console.log('⚠️ Nenhum novo investimento único. Página não incrementada.');
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
        this.loadInvestments();
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
    applyFilters() {
      this.loadInvestments(true);
    },
    resetFilters() {
      this.filters = {
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: ''
      };
      this.applyFilters();
    },
    applyFiltersDebounced: debounce(function () {
      this.applyFilters();
    }, 500),
    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedInvestments = [];
      } else {
        this.selectedInvestments = this.investments.map(inv => inv.id);
      }
    },
    truncate(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    viewInvestment(investmentId) {
      this.$emit("InvestmentView", investmentId);
    },

    // Delete individual: define a target e abre a modal
    deleteInvestment(investmentId) {
      this.deletionTarget = investmentId;
      this.showDeleteModal = true;
    },
    deleteSelectedInvestments() {
      if (this.selectedInvestments.length > 0) {
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
        axios.delete(`/investments/${this.deletionTarget}`)
          .then(() => {
            this.investments = this.investments.filter(exp => exp.id !== this.deletionTarget);
            this.selectedInvestments = this.selectedInvestments.filter(id => id !== this.deletionTarget);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
            this.deletionTarget = null;
          });
      } else if (this.selectedInvestments.length > 0) {
        // Exclusão múltipla
        const deletePromises = this.selectedInvestments.map(id => axios.delete(`/investments/${id}`));
        Promise.all(deletePromises)
          .then(() => {
            this.investments = this.investments.filter(exp => !this.selectedInvestments.includes(exp.id));
            this.selectedInvestments = [];
            this.showDeleteModal = false;
          })
          .catch(error => {
            console.error(error);
            this.showDeleteModal = false;
          });
      }
    },
    addInvestment() {
      this.$emit("addInvestment");
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