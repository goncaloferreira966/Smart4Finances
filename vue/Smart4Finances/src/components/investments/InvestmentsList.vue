<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 100vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Os Meus Investimentos</h2>

    <!-- Filtros -->
    <div class="mb-4">
      <div class="flex mb-2">
        <div class="mr-5 ml-2">
          <input type="number" v-model="filters.minPrice" placeholder="Valor mínimo" class="mr-2 p-2 border rounded"
            style="width: 15vh;" />
          <input type="number" v-model="filters.maxPrice" placeholder="Valor máximo" class="p-2 border rounded"
            style="width: 15vh;" />
        </div>
        <div class="flex mb-2 mr-5 ml-2">
          <input type="date" v-model="filters.startDate" class="mr-2 p-2 border rounded" />
          <input type="date" v-model="filters.endDate" class="p-2 border rounded" />
        </div>
      </div>
      <button @click="addInvestment" class="bg-green-500 text-white px-4 py-2 rounded">
        <i class="bi bi-plus-lg"></i>
      </button>
      <button v-if="selectedInvestments.length > 0" @click="deleteSelectedInvestments" class="bg-red-500 text-white px-4 py-2 rounded ml-2">
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
          <th class="border px-2 py-1">Tipo</th>
          <th class="border px-2 py-1">ROI</th>
          <th class="border px-2 py-1">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="investment in investments" :key="investment.id">
          <td class="px-2 py-1 text-center">
            <input type="checkbox" :value="investment.id" v-model="selectedInvestments"/>
          </td>
          <td class="px-2 py-1">{{ formatDate(investment.created_at) }}</td>
          <td class="px-2 py-1">{{ investment.amount + ' ' + coin }}</td>
          <td class="px-2 py-1">{{ investment.type }}</td>
          <td class="px-2 py-1">{{ investment.roi }} %</td>
          <td class="px-2 py-1">
            <!-- Botão de delete individual -->
            <button @click="deleteInvestment(investment.id)" class="bg-red-500 text-white px-2 py-1 rounded">
              <i class="bi bi-trash"></i>
            </button>
            <!-- Botão de visualizar -->
            <button @click="viewInvestment(investment.id)" class="bg-blue-500 text-white px-2 py-1 rounded ml-1">
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
        <p class="mb-4">Tem certeza que deseja apagar o(s) investimentos(s) selecionado(s)?</p>
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
  data() {
    return {
      coin: '',
      investments: [],
      page: 1,
      selectedInvestments: [],
      perPage: 10,
      loadingMore: false,
      filters: {
        minPrice: '',
        maxPrice: '',
        startDate: '',
        endDate: '',
      },
      showDeleteModal: false,
      deletionTarget: null
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
    'filters.endDate': 'applyFiltersDebounced'
  },
  methods: {
    loadInvestments(reset = false) {
      if (reset) {
        this.page = 1;
        this.investments = [];
        this.selectedInvestments = [];
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
            this.investments = this.investments.concat(newInvestments);
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
        this.loadInvestments();
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
      this.loadInvestments(true);
    },
    applyFiltersDebounced: debounce(function () {
      this.applyFilters();
    }, 500),
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
    addInvestment(){
      this.$emit("addInvestment");
    },
  }
};
</script>