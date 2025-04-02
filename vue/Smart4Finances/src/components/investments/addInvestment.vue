<template>
  <div class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg"
    style="margin-top: 7vh; margin-bottom: 7vh; min-width: 50vh;">
    <h2 class="text-2xl font-bold mb-4">{{ isEditMode ? 'Editar Investimento' : 'Registar Investimento' }}</h2>

    <form @submit.prevent="submitInvestment">
      <label class="block mb-2 mt-2">Tipo:</label>
      <select class="form-select" v-model="investment.type">
        <option value="" disabled selected>Selecione um Tipo de Investimento</option>
        <option value="Ações">Ações</option>
        <option value="ETF">ETF (Fundos de Índice)</option>
        <option value="Fundos de Investimento">Fundos de Investimento</option>
        <option value="P2P Lending">Empréstimos P2P</option>
        <option value="Criptomoedas">Criptomoedas</option>
        <option value="Obrigações">Obrigações</option>
        <option value="PPR">Plano Poupança Reforma (PPR)</option>
        <option value="Depósitos a Prazo">Depósitos a Prazo</option>
        <option value="Certificados de Tesouro/Aforro">Certificados de Tesouro/Aforro</option>
        <option value="Imobiliário">Investimento Imobiliário</option>
        <option value="Commodities">Commodities (Ouro, Petróleo, etc.)</option>
        <option value="Startups/VC">Startups/Venture Capital</option>
        <option value="Derivados">Derivados (Opções, Futuros)</option>
        <option value="Forex">Mercado Cambial (Forex)</option>
        <option value="REITs">REITs (Fundos Imobiliários)</option>
        <option value="Seguros de Capitalização">Seguros de Capitalização</option>
      </select>

      <label class="block mb-2 mt-2">Valor:</label>
      <input type="text" v-model="investment.amount" @blur="formatAmount" class="w-full p-2 border rounded mb-4" />

      <label class="block mb-2 mt-2">ROI(%):</label>
      <input min="0" max="100" type="number" v-model="investment.roi" class="w-full p-2 border rounded mb-4" />

      <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 bthouver">
        <i class="bi bi-check2-circle"></i>
        {{ isEditMode ? 'Atualizar' : 'Confirmar' }}
      </button>
    </form>

    <button @click="isEditMode ? viewInvestment(investment.id) : InvestmentList()"
      class="mt-4 w-full bg-red-500 text-white py-2 rounded hover:bg-black">
      <i class="bi bi-arrow-left-short"></i>
      Voltar
    </button>
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';

export default {
  props: {
    investmentId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      isEditMode: false,
      errorMessage: '',
      investment: {
        type: '',
        amount: '0.00',
        roi: ''
      },
    };
  },
  mounted() {
    if (this.investmentId) {
      this.isEditMode = true;
      this.loadInvestment();
    }
  },
  methods: {
    loadInvestment() {
      axios.get(`/investments/${this.investmentId}`)
        .then(response => {
          this.investment = response.data;
        })
        .catch(error => {
          console.error('Erro ao carregar investimento:', error);
        });
    },
    submitInvestment() {
      if (!this.investment.type || !this.investment.amount || !this.investment.roi) {
        this.errorMessage = 'Os campos Tipo, Valor e ROI são obrigatórios.';
        toast.error(this.errorMessage);
        return;
      }

      this.errorMessage = '';
      const formData = new FormData();
      formData.append('type', this.investment.type);
      formData.append('amount', this.investment.amount);
      formData.append('roi', this.investment.roi);

      if (this.isEditMode) {
        axios.put(`/investments/${this.investmentId}`, formData, {
          headers: { 'Content-Type': 'application/json' }
        }).then(response => {
          toast.success("Investimento atualizado com sucesso!");
          this.$emit("InvestmentList", { reload: true });
        }).catch(error => {
          this.errorMessage = 'Erro ao atualizar investimento. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      } else {
        axios.post('/investments', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        }).then(response => {
          toast.success("Investimento registado com sucesso!");
          this.$emit("InvestmentList", { reload: true });
        }).catch(error => {
          this.errorMessage = 'Erro ao submeter investimento. Verifique os dados e tente novamente.';
          toast.error(this.errorMessage);
        });
      }
    },
    viewInvestment(investmentId) {
      this.$emit("InvestmentView", investmentId);
    },
    InvestmentList() {
      this.$emit("InvestmentList", null);
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
