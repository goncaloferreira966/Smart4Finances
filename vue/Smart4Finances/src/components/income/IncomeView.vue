<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4" style="color: black;">Detalhes da Receita</h2>
    <div v-if="income">
      <p><strong>Data:</strong> {{ income.date }}</p>
      <p><strong>Fonte:</strong> {{ income.source }}</p>
      <p><strong>Valor:</strong> {{ income.amount  + " " + coin }}</p>
      <p v-if="income.recurring_interval != 'null'">
        <strong>Intervalo Recorrente:</strong>
        <span v-if="income.recurring_interval">
          {{ income.recurring_interval }} {{ income.recurring_interval_unit }}
        </span>
        <span v-else>Nenhum</span>
      </p>
      <div v-if="income.receipt">
        <img :src="receiptUrl(income.receipt)" alt="Recibo" class="w-full max-w-md mt-4"/>
      </div>
      <button @click="editIncome" class="w-full mt-4 bg-blue-500 text-white px-4 py-2 rounded">
        <i class="bi bi-pen"></i>
        Editar Receita
      </button>
      <br>
      <button @click="backList" class="w-full mt-4 bg-red-500 text-white px-4 py-2 rounded">
        <i class="bi bi-arrow-left-short"></i>
        Voltar
      </button>
    </div>
    <div v-else>
      A carregar...
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useAuthStore } from "@/stores/auth";

export default {
  props: {
    IncomeId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      income: null,
      coin: "",
    };
  },
  created() {
    this.loadIncome();
    const authStore = useAuthStore();
    this.coin = (authStore.user?.data?.coin);  
  },
  methods: {
    loadIncome() {
      axios.get(`/incomes/${this.IncomeId}`)
        .then(response => {
          this.income = response.data;
        })
        .catch(error => {
          console.error(error);
        });
    },
    receiptUrl(receiptPath) {
      return import.meta.env.VITE_API_DOMAIN + '/storage/' + receiptPath;
    },
    editIncome() {
      this.$emit('editIncome', this.income.id);
    },
    backList() {
      this.$emit('backIncome');
    }
  }
};
</script>

<style scoped>
/* Ajusta os estilos conforme necessário */
</style>
