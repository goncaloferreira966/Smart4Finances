<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Detalhes da Despesa</h2>
    <div v-if="expense">
      <p><strong>Data:</strong> {{ expense.date }}</p>
      <p><strong>Categoria:</strong> {{ getCategoryName(expense.category_id) }}</p>
      <p><strong>Valor:</strong> {{ expense.amount }}</p>
      <p><strong>Descrição:</strong> {{ expense.description }}</p>
      <p><strong>Intervalo Recorrente:</strong> 
        <span v-if="expense.recurring_interval">
          {{ expense.recurring_interval }} {{ expense.recurring_interval_unit }}
        </span>
        <span v-else>Nenhum</span>
      </p>
      <div v-if="expense.receipt">
        <img :src="receiptUrl(expense.receipt)" alt="Recibo" class="w-full max-w-md mt-4"/>
      </div>
      <button @click="editExpense" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
        Editar Despesa
      </button>
    </div>
    <div v-else>
      Carregando...
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      expense: null,
      categories: []
    };
  },
  created() {
    this.loadCategories();
    this.loadExpense();
  },
  methods: {
    loadCategories() {
      axios.get('/categories').then(response => {
        this.categories = response.data;
      });
    },
    loadExpense() {
      const expenseId = this.$route.params.id;
      axios.get(`/expenses/${expenseId}`).then(response => {
        this.expense = response.data;
      }).catch(error => {
        console.error(error);
      });
    },
    getCategoryName(categoryId) {
      const cat = this.categories.find(c => c.id === categoryId);
      return cat ? cat.name : '';
    },
    receiptUrl(receiptPath) {
      return axios.defaults.baseURL + '/storage/' + receiptPath;
    },
    editExpense() {
      this.$router.push({ name: 'AddExpenses', query: { id: this.expense.id } });
    }
  }
};
</script>

<style scoped>
/* Ajuste os estilos conforme necessário */
</style>
