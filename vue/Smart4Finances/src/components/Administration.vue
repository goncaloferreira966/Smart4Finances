<template>
  <div class="container mt-4 mb-5">
    <h2 style="color: lightseagreen;" class="text-center mt-4">Gestão de Utilizadores</h2>
    <div class="card mt-4">
    
      <div class="table-responsive mt-4">
        <table class="table table-striped table-hover">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Nickname</th>
              <th>Tipo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users.data" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.nickname }}</td>
              <td v-if="user.type == 'C'"><i class="bi bi-person"></i> Cliente</td>
              <td v-else><i class="bi bi-person-fill-gear"></i> Administrador</td>
              <td v-if="user.id != this.getUserIdFromToken()">
                <div v-if="user.type!='A'">
                  <button v-if="user.blocked" class="btn btn-success btn-sm m-1" @click="unblockUser(user.id)"><i
                      class="bi bi-person-plus"></i></button>
                  <button v-if="!user.blocked" class="btn btn-danger btn-sm m-1" @click="blockUser(user.id)"><i
                    class="bi bi-person-fill-slash"></i></button>
                </div>
                <button class="btn btn-danger btn-sm m-1" @click="deleteUser(user.id)"><i
                    class="bi bi-trash-fill"></i></button>
              </td>
              <td v-else>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-if="error" class="alert alert-danger text-center">{{ error }}</div>

    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mt-4 pagination-responsive">
        <!-- Botão para a página anterior, desativado se estiver na primeira página -->
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link" @click="changePage(currentPage - 1)">Anterior</button>
        </li>

        <!-- Exibe as 3 primeiras páginas -->
        <li v-if="currentPage > 4" class="page-item">
          <button class="page-link" @click="changePage(1)">1</button>
        </li>
        <li v-if="currentPage > 5" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <!-- Exibe as páginas ao redor da página atual -->
        <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: currentPage === page }">
          <button class="page-link" @click="changePage(page)">{{ page }}</button>
        </li>

        <!-- Exibe as reticências caso as páginas forem muito distantes -->
        <li v-if="currentPage < users.meta.last_page - 3" class="page-item disabled">
          <span class="page-link">...</span>
        </li>

        <!-- Exibe as 3 últimas páginas -->
        <li v-if="currentPage < users.meta.last_page - 4" class="page-item">
          <button class="page-link" @click="changePage(users.meta.last_page)">{{ users.meta.last_page }}</button>
        </li>

        <!-- Botão para a próxima página, desativado se estiver na última página -->
        <li class="page-item" :class="{ disabled: currentPage === users.meta.last_page }">
          <button class="page-link" @click="changePage(currentPage + 1)">Próximo</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      users: {
        data: [],
        meta: {
          current_page: 1,
          last_page: 1,
          total: 0,
        },
      },
      visiblePages: [],
      loading: false,
      error: null,
      currentPage: 1,
      perPage: 20, // Número de usuários por página
    };
  },
  created() {
    this.fetchUsers(this.currentPage);
  },
  methods: {
    changePage(page) {
      if (page > 0 && page <= this.users.meta.last_page) {
        this.fetchUsers(page);
        this.updateVisiblePages();
      }
  },
   // Função para atualizar as páginas visíveis dependendo da página atual
   updateVisiblePages() {
            const pageRange = 3; // Número de páginas ao redor da página atual
            let startPage = this.currentPage - pageRange;
            let endPage = this.currentPage + pageRange;

            // Ajusta os limites de páginas para garantir que pelo menos as 3 primeiras e 3 últimas páginas sejam visíveis
            if (startPage < 4) startPage = 4; // Exibe no mínimo as páginas 1, 2, 3
            if (endPage > this.totalPages - 3) endPage = this.totalPages - 3; // Exibe no máximo as últimas 3 páginas

            // Preenche o intervalo de páginas
            this.visiblePages = [];
            for (let page = startPage; page <= endPage; page++) {
                this.visiblePages.push(page);
            }
        },
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1])); // Decodifica o payload do token
        return payload.sub; // Retorna o ID do user (sub)
      }
      return null;
    },
    async fetchUsers(page) {
      this.loading = true;
      this.error = null;
      this.currentPage = page;
      try {
        const token = localStorage.getItem('AccessToken');
        axios.defaults.baseURL = `http://localhost/api`;
        const response = await axios.get(`/users?page=${this.currentPage}&per_page=${this.perPage}`, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });
        this.users = response.data;
      } catch (err) {
        this.error = 'Erro';
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
    async blockUser(userId) {
      if (confirm('Tem certeza que deseja bloquear este utilizador?')) {
        const user = this.users.data.find(user => user.id === userId);
        if (user) {
          user.blocked = true; // Atualiza imediatamente na interface
          try {
            const token = localStorage.getItem('AccessToken');
            axios.defaults.baseURL = `http://localhost/api`;
            await axios.patch(`/users/${userId}/block`, { blocked: true }, {
              headers: {
                Authorization: `Bearer ${token}`
              }
            });
          } catch (err) {
            this.error = 'Erro ao bloquear o utilizador';
            console.error(err);
          }
        }
      }
    },
    async unblockUser(userId) {
      if (confirm('Tem certeza que deseja desbloquear este utilizador?')) {
        const user = this.users.data.find(user => user.id === userId);
        if (user) {
          user.blocked = false; // Atualiza imediatamente na interface
          try {
            const token = localStorage.getItem('AccessToken');
            axios.defaults.baseURL = `http://localhost/api`;
            await axios.patch(`/users/${userId}/block`, { blocked: false }, {
              headers: {
                Authorization: `Bearer ${token}`
              }
            });
          } catch (err) {
            console.log(err)
            this.error = 'Erro ao desbloquear o utilizador';
            console.error(err);
          }
        }
      }
    },
    async deleteUser(userId) {
      if (confirm('Tem certeza que deseja remover este utilizador?')) {
        try {
          const token = localStorage.getItem('AccessToken');

          await axios.delete(`/users/${userId}`, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });
          this.users.data = this.users.data.filter(user => user.id !== userId);

          // Atualiza o número total de users
          this.users.meta.total -= 1;

          // Verifica se a página está vazia após a exclusão e ajusta a paginação se necessário
          if (this.users.data.length === 0 && this.currentPage > 1) {
            this.fetchUsers(this.currentPage - 1);
          }

        } catch (err) {
          this.error = 'Erro ao excluir Utilizador';
          console.error(err);
        }
      }
    },
  }

};
</script>

<style scoped>
.table-responsive {
  margin-top: 20px;
}

.spinner-border {
  margin: 20px;
}


.pagination-responsive {
  flex-wrap: wrap;
  /* Permite quebrar em várias linhas */
}

.pagination-responsive .page-item {
  min-width: 50px;
  /* Largura mínima dos botões */
  margin-bottom: 5px;
  /* Espaço entre linhas quando quebra */
}

.pagination-responsive .page-item .page-link {
  padding: 0.5rem 0.75rem;
  /* Ajusta o padding para telas pequenas */
}

.pagination-responsive .page-item.active .page-link {
  background-color: lightseagreen;
  border-color: lightseagreen;
  color: white;
}
</style>