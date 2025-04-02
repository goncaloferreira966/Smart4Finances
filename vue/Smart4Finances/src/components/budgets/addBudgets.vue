<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4">{{ isEditMode ? 'Editar Orçamento Mensal' : 'Registar  Orçamento Mensal' }}</h2>

    <form @submit.prevent="submitBudget">
      <label class="block mb-2">Categoria:</label>
      <input type="text" v-model="searchQuery" placeholder="Pesquisar categoria..."
        class="w-full p-2 border rounded mb-2" />
      <div class="border rounded mb-4" style="max-height: 200px; overflow-y: auto;">
        <div v-for="category in filteredCategories" :key="category.id" class="p-2 cursor-pointer hover:bg-gray-200"
          @click="selectCategory(category)">
          {{ category.name }}
        </div>
        <div v-if="filteredCategories.length === 0" class="p-2 text-gray-500">
          Nenhuma categoria encontrada.
        </div>
      </div>

      <button type="button" @click="showModal = true" style="background-color:black; color:#DAA520"
        class="mb-4 py-2 px-4 rounded hover:bg-gray-600">
        Ver todas as categorias
      </button>

      <!-- Select oculto para manter o valor selecionado -->
      <select v-model="budget.category_id" class="hidden">
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>

      <label class="block mb-2">Valor:</label>
      <input type="text" v-model="budget.limit_amount" @blur="formatAmount" class="w-full p-2 border rounded mb-4" />

    

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 bthouver">
        <i class="bi bi-check2-circle"></i>
        {{ isEditMode ? 'Atualizar' : 'Confirmar' }}
      </button>
    </form>
    <button @click="isEditMode ? viewBudget(budget.id) : BudgetsList()" class="mt-4 w-full bg-red-500 text-white py-2 rounded hover:bg-black">
      <i class="bi bi-arrow-left-short"></i>
      Voltar
    </button>

    <!-- Modal para seleção de categoria -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg w-11/12 max-w-md">
        <h3 class="text-xl font-bold mb-4">Selecione uma Categoria</h3>
        <input type="text" v-model="modalSearchQuery" placeholder="Pesquisar categoria..."
          class="w-full p-2 border rounded mb-2" />
        <div class="border rounded mb-4" style="max-height: 300px; overflow-y: auto;">
          <div v-for="category in modalFilteredCategories" :key="category.id"
            class="p-2 cursor-pointer hover:bg-gray-200" @click="selectCategoryFromModal(category)">
            {{ category.name }}
          </div>
          <div v-if="modalFilteredCategories.length === 0" class="p-2 text-gray-500">
            Nenhuma categoria encontrada.
          </div>
        </div>
        <button @click="showModal = false" style="background-color:black; color:#DAA520"
          class="w-full py-2 rounded hover:bg-gray-600">
          Fechar
        </button>
      </div>
    </div>


  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
  props: {
    budgetId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      isEditMode: false,
      errorMessage: '',
      budget: {
        category_id: '',
        limit_amount: '0.00',
      },
      categories: [],
      searchQuery: '',
      modalSearchQuery: '',
      showModal: false,
    };
  },
  computed: {
    filteredCategories() {
      if (!this.searchQuery) return this.categories;
      return this.categories.filter(category =>
        category.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    modalFilteredCategories() {
      if (!this.modalSearchQuery) return this.categories;
      return this.categories.filter(category =>
        category.name.toLowerCase().includes(this.modalSearchQuery.toLowerCase())
      );
    },
  },
  mounted() {
    axios.get('/categories')
      .then(response => {
        this.categories = response.data;
      })
      .catch(error => {
        console.error('Erro ao buscar categorias:', error);
      });
    if (this.budgetId) {
      this.isEditMode = true;
      this.loadBudget();
    }

  },
  methods: {
    loadBudget() {
      axios.get(`/budgets/${this.budgetId}`)
        .then(response => {
          this.budget = response.data;
     
          // Se as categorias já foram carregadas, atualiza o searchQuery
          if (this.categories.length > 0) {
            const cat = this.categories.find(c => c.id == this.budget.category_id);
            if (cat) {
              this.searchQuery = cat.name;
            }
          }
        })
        .catch(error => {
          console.error('Erro ao carregar orçamento:', error);
        });
      },
      selectCategory(category) {
        this.budget.category_id = category.id; 
        this.searchQuery = category.name;
      },
      selectCategoryFromModal(category) {
        this.budget.category_id = category.id;
        this.searchQuery = category.name;
        this.showModal = false;
      },
    submitBudget() {
      if (!this.budget.limit_amount || !this.budget.category_id) {
        this.errorMessage = 'Os campos Valor e Categoria são obrigatórios.';
        toast.error(this.errorMessage);
        return;
      }

      this.errorMessage = '';
      const formData = new FormData();
      formData.append('category_id', this.budget.category_id);
      formData.append('limit_amount', this.budget.limit_amount);


      if (this.isEditMode) {
        axios.put(`/budgets/${this.budgetId}`, formData, {
          headers: { 'Content-Type': 'application/json' }
        }).then(response => {
          toast.success("Orçamento atualizado com sucesso!");
          this.$emit("BudgetsList", { reload: true } );
        }).catch(error => {
          this.errorMessage = 'Erro ao atualizar orçamento. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      } else {
        axios.post('/budgets', formData, {
          headers: { 'Content-Type': 'application/json' }
        }).then(response => {
          toast.success("Orçamento registado com sucesso!");
          this.$emit("BudgetsList", { reload: true } );
        }).catch(error => {
          this.errorMessage = 'Erro ao submeter orçamento. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      }
    },
    viewBudget(budgetId) {
      this.$emit("BudgetView", budgetId);
    },
    BudgetsList() {
      this.$emit("BudgetsList", null);
    }
  }
};
</script>

<style>
body {
  background-color: #f4f4f4;
}

.bthouver:hover {
  background-color: green;
}
</style>
