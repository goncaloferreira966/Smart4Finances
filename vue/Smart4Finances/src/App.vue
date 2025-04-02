<template>
  <div id="app">
    <Navbar :isLoggedIn="isLoggedIn" :activeForm="currentSection" @navigate="navigateTo" @logout="logout" />

    <!-- Área pública -->
    <div v-if="!isLoggedIn">
      <div v-if="currentSection === 'resetPassword'">
        <ResetPassword :token="resetToken" :email="resetEmail" @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'login'">
        <Login @login-success="handleLoginSuccess" @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'register'">
        <Register @register-success="handleRegisterSuccess" @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'forgotPassword'">
        <ForgotPassword @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'emailConfirmed'">
        <EmailConfirmed @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'emailVerificationError'">
        <EmailVerificationError @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'emailAlreadyVerified'">
        <EmailAlreadyVerified @navigate="navigateTo" />
      </div>
      <div v-else>
        <Login @login-success="handleLoginSuccess" @navigate="navigateTo" />
      </div>
    </div>

    <!-- Área protegida -->
    <div v-else>
      <div v-if="currentSection === 'profile'">
        <Profile @editer="handleEditUser" @logout="logout" />
      </div>
      <div v-else-if="currentSection === 'editUser'">
        <EditUser @update-success="handleUpdateSuccess" @update-cancel="handleUpdateCancel" />
      </div>
      <div v-else-if="currentSection === 'administration'">
        <Administration />
      </div>
      <div v-else-if="currentSection === 'notifications'">
        <Notifications />
      </div>
      <div v-else-if="currentSection === 'addExpenses'">
        <addExpenses :expenseId="id" @ExpensesList="handleExpensesList" @ExpenseView="handleExpenseView"/>
      </div>
      <div v-else-if="currentSection === 'ExpensesList'">
        <ExpensesList
          :reloadExpensesList="reloadExpensesList"
          @update:reloadExpensesList="reloadExpensesList = $event"
          @ExpenseView="handleExpenseView"
          @addexpense="handleExpensEdit"
        />
      </div>
      <div v-else-if="currentSection === 'ExpenseView'">
        <ExpenseView @BackExpense="handleExpensesList" @editExpense="handleExpensEdit" :expenseId="id" />
      </div>
      <div v-else-if="currentSection === 'addIncome'">
        <addIncome :IncomeId="id" @IncomeList="handleIncomeList" @IncomeView="handleIncomeView"/>
      </div>
      <div v-else-if="currentSection === 'IncomeList'">
        <IncomeList @IncomeView="handleIncomeView" @addIncome="handleIncomeEdit" />
      </div>
      <div v-else-if="currentSection === 'IncomeView'">
        <IncomeView @BackIncome="handleIncomeList" @editIncome="handleIncomeEdit" :IncomeId="id" />
      </div>
      <div v-else-if="currentSection === 'dashboardadmin'">
        <DashboardAdmin />
      </div>
      <div v-else-if="currentSection === 'dashboardclient'">
        <DashboardClient />
      </div>
      <div v-else-if="currentSection === 'InvestmentList'">
        <InvestmentsList
          :reloadInvestmentsList="reloadInvestmentsList"
          @update:reloadInvestmentsList="reloadInvestmentsList = $event"
          @InvestmentView="handleInvestmentView"
          @addInvestment="handleInvestmentEdit"
        />
        </div>
      <div v-else-if="currentSection === 'InvestmentView'">
        <InvestmentView @BackInvestment="handleInvestmentList" @editInvestment="handleInvestmentEdit" :investmentId="id"/>
      </div>
      <div v-else-if="currentSection === 'addInvestment'">
        <addInvestment @InvestmentList="handleInvestmentList" @InvestmentView="handleInvestmentView" :investmentId="id"/>
      </div>
      <div v-else-if="currentSection === 'BudgetList'">
        <BudgetsList  :reloadBudgetsList="reloadBudgetsList"
          @update:reloadBudgetsList="reloadBudgetsList = $event"
          @BudgetView="handleBudgetView"
          @addBudget="handleBudgetEdit"/>
      </div>
      <div v-else-if="currentSection === 'BudgetView'">
        <BudgetView @BackBudget="handleBudgetList" @editBudget="handleBudgetEdit" :budgetId="id"/>
      </div>
    </div>

    <footer class="footer">
      <p style="color:#DAA520">&copy; {{ currentYear }} Smart4Finances. Todos os direitos reservados.</p>
      <div class="footer-links">
        <a href="#">Política de Privacidade</a>
        <a href="#">Termos de Serviço</a>
        <a href="#">Suporte</a>
      </div>
    </footer>
  </div>
</template>

<script>
import Login from './components/user/Login.vue';
import Navbar from './components/NavBar/Navbar.vue';
import Register from './components/user/Register.vue';
import Profile from './components/user/Profile.vue';
import EditUser from './components/user/EditUser.vue';
import addExpenses from './components/expenses/addExpenses.vue';
import Administration from './components/user/Administration.vue';
import Notifications from './components/Notifications.vue';
import DashboardAdmin from './components/DashboardAdmin.vue';
import DashboardClient from './components/DashboardClient.vue';
import ExpensesList from './components/expenses/ExpensesList.vue';
import ExpenseView from './components/expenses/ExpenseView.vue';
import addIncome from './components/income/addIncome.vue';
import IncomeList from './components/income/IncomeList.vue';
import IncomeView from './components/income/IncomeView.vue';
import ForgotPassword from './components/password_mail/forgotPassword.vue';
import ResetPassword from './components/password_mail/ResetPassword.vue';
import InvestmentsList from './components/investments/InvestmentsList.vue';
import InvestmentView from './components/investments/InvestmentView.vue';
import addInvestment from './components/investments/addInvestment.vue';
import BudgetsList from './components/budgets/BudgetsList.vue';
import BudgetView from './components/budgets/BudgetView.vue';

import EmailConfirmed from './components/password_mail/EmailConfirmed.vue';
import EmailVerificationError from './components/password_mail/EmailVerificationError.vue';
import EmailAlreadyVerified from './components/password_mail/EmailAlreadyVerified.vue';
import { toast } from 'vue3-toastify';
import axios from 'axios';

export default {
  components: {
    Login,
    ForgotPassword,
    ResetPassword,
    Navbar,
    Register,
    Profile,
    EditUser,
    Administration,
    Notifications,
    DashboardAdmin,
    DashboardClient,
    addExpenses,
    ExpensesList,
    ExpenseView,
    addIncome,
    IncomeList,
    IncomeView,
    EmailConfirmed,
    EmailVerificationError,
    EmailAlreadyVerified,
    InvestmentsList,
    InvestmentView,
    addInvestment,
    BudgetsList,
    BudgetView,
  },
  data() {
    return {
      isLoggedIn: false,
      isRegistering: false,
      currentSection: 'login',
      id: null,
      resetToken: null,
      resetEmail: null,
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const email = urlParams.get('email');
    console.log(token);
    if (token && email) {
      axios.post('/password/validate-token', {
        token,
        email
      },{headers: { 'Content-Type': 'multipart/form-data' }})
      .then((response) => {
        if (response.data.valid) {
          this.resetToken = token;
          this.resetEmail = email;
          this.currentSection = 'resetPassword';
        } else {
          this.currentSection = 'emailVerificationError';
        }
        window.history.replaceState({}, document.title, '/');
      })
      .catch(() => {
        console.log('error');
        this.currentSection = 'emailVerificationError';
        window.history.replaceState({}, document.title, '/');
      });
    }

    if (urlParams.get('email_verified') === 'true') {
      localStorage.removeItem('AccessToken');
      this.currentSection = 'emailConfirmed';
      window.history.replaceState({}, document.title, '/');
    }

    if (urlParams.get('email_verified') === 'invalid') {
      this.currentSection = 'emailVerificationError';
      window.history.replaceState({}, document.title, '/');
    }

    if (urlParams.get('email_verified') === 'already') {
      this.currentSection = 'emailAlreadyVerified';
      window.history.replaceState({}, document.title, '/');
    }
  },
  methods: {
    handleLoginSuccess() {
      this.isLoggedIn = true;
      this.currentSection = 'profile';
    },
    logout() {
      this.isLoggedIn = false;
    },
    navigateTo(section) {
      this.currentSection = section;
    },
    handleRegisterSuccess() {
      this.isRegistering = false;
      this.currentSection = 'login';
      toast.success("Registo realizado com sucesso! Verifique o seu e-mail.");
    },
    handleEditUser() {
      this.isRegistering = false;
      this.currentSection = 'editUser';
    },
    handleUpdateSuccess() {
      this.currentSection = 'profile';
      toast.success("Dados atualizados com sucesso!");
    },
    handleUpdateCancel() {
      this.currentSection = 'profile';
    },
    handleExpensesList(payload) {
      this.currentSection = 'ExpensesList';
      if (payload?.reload) {
        this.reloadExpensesList = true;
      }
    },
    handleExpenseView(id) {
      this.id = id;
      this.currentSection = 'ExpenseView';
    },
    handleInvestmentView(id) {
      this.id = id;
      this.currentSection = 'InvestmentView';
    },
    handleBudgetView(id) {
      this.id = id;
      this.currentSection = 'BudgetView';
    },
    handleExpensEdit(id) {
      this.id = id;
      this.currentSection = 'addExpenses';
    },
    handleInvestmentEdit(id){
      this.id = id;
      this.currentSection = 'addInvestment';
    },
    handleBudgetEdit(id){
      this.id = id;
      this.currentSection = 'addBudget';
    },
    handleIncomeList(payload) {
      this.currentSection = 'IncomeList';
      if (payload?.reload) {
        this.reloadIncomeList = true;
      }
    },
    handleInvestmentList(payload) {
      this.currentSection = 'InvestmentList';
      if (payload?.reload) {
        this.reloadInvestmentsList  = true; 
      }
    },
    handleBudgetList(payload) {
      this.currentSection = 'BudgetList';
      if (payload?.reload) {
        this.reloadBudgetList = true;
      }
    },
    handleIncomeView(id) {
      this.id = id;
      this.currentSection = 'IncomeView';
    },
    handleBudgetView(id) {
      this.id = id;
      this.currentSection = 'BudgetView';
    },
    handleIncomeEdit(id) {
      this.id = id;
      this.currentSection = 'addIncome';
    },
  },
};
</script>