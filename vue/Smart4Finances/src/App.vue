<template>
  <div id="app">
    <!-- Exibir o componente Login apenas se o usuário não estiver logado -->
    <!--<NavbarLoginRegister :activeForm="currentSection" @navigate="navigateTo" @logout="logout" />-->
    <Navbar :isLoggedIn="isLoggedIn" :activeForm="currentSection" @navigate="navigateTo" @logout="logout" />
    <div v-if="!isLoggedIn">
      <div v-if="currentSection === 'login'">
        <Login @login-success="handleLoginSuccess" @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'register'">
        <Register @register-success="handleRegisterSuccess" @navigate="navigateTo" />
      </div>
      <div v-else-if="currentSection === 'forgotPassword'">
        <ForgotPassword @navigate="navigateTo" />
      </div>
      <div v-else>
        <Login @login-success="handleLoginSuccess" @navigate="navigateTo" />
      </div>
    </div>

    <!-- Conteúdo da aplicação principal -->
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
        <addExpenses :expenseId="id" @ExpensesList="handleExpensesList" />
      </div>
      <div v-else-if="currentSection === 'ExpensesList'">
        <ExpensesList @ExpenseView="handleExpenseView" @addexpense="handleExpensEdit" />
      </div>
      <div v-else-if="currentSection === 'ExpenseView'">
        <ExpenseView @BackExpense="handleExpensesList" @editExpense="handleExpensEdit" :expenseId="id" />
      </div>
      <div v-else-if="currentSection === 'addIncome'">
        <addIncome :IncomeId="id" @IncomeList="handleIncomeList" />
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
import { toast } from 'vue3-toastify';

export default {
  components: {
    Login,
    ForgotPassword,
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
  },
  data() {
    return {
      isLoggedIn: false,
      isRegistering: false,
      currentSection: 'login',
      id: null,
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
  methods: {
    handleLoginSuccess() {
      this.isLoggedIn = true;
      this.currentSection = 'profile';
    },
    logout() {
      this.isLoggedIn = false;
      //toast.success("Logout realizado com sucesso!");
    },
    navigateTo(section) {
      this.currentSection = section;
    },
    handleRegisterSuccess() {
      this.isRegistering = false;
      this.currentSection = 'login';
      toast.success("Registo realizado com sucesso! Faça login.");
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
    // Expenses
    handleExpensesList() {
      this.currentSection = 'ExpensesList';
    },
    handleExpenseView(id) {
      this.id = id;
      this.currentSection = 'ExpenseView';
    },
    handleExpensEdit(id) {
      this.id = id;
      this.currentSection = 'addExpenses';
    },
    // Income
    // TODO: 
    handleIncomeList() {
      this.currentSection = 'IncomeList';
    },
    handleIncomeView(id) {
      this.id = id;
      this.currentSection = 'IncomeView';
    },
    handleIncomeEdit(id) {
      this.id = id;
      this.currentSection = 'addIncome';
    },
  },
};
</script>