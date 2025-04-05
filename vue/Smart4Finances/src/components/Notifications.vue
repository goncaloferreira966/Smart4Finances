<template>
  <div class="notifications-page mb-5">
    <h2 class="card-title" style="color: black;">Notificações</h2>
    <div class="controls mt-4">
      <button style="border-top-right-radius: 0; border-bottom-right-radius: 0;" @click="fetchNotifications('unread')"
        :disabled="loading" :class="{ active: activeTab === 'unread' }">
        <i class="bi bi-bell-fill text-danger"></i> Não Lidas
      </button>
      <button style="border-top-left-radius: 0; border-bottom-left-radius: 0;" @click="fetchNotifications('all')"
        :disabled="loading" :class="{ active: activeTab === 'all' }">
        <i class="bi bi-bell-fill "></i> Todas as Notificações
      </button>

    </div>
    <div v-if="loading" class="loading">A Carregar notificações...</div>
    <div v-else class="mt-4">
      <div v-if="notifications.length === 0" class="no-notifications">
        <p>Não há notificações disponíveis.</p>
      </div>
      <ul v-else class="notifications-list">
        <li v-for="notification in notifications" :key="notification.id" class="notification-item">
          <div class="row">
            <div class="col-md-8">
              <p><strong>Mensagem:</strong> {{ notification.data.message || 'Sem mensagem disponível' }}</p>
              <p><strong>Data:</strong> {{ formatDate(notification.created_at) }}</p>
            </div>
            <div class="col-md-4">
              <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                class="mark-as-read-btn text-danger mt-3">
                <i class="bi bi-bell-slash-fill"></i> Marcar como lida
              </button>
              <p v-else class="read-status text-success mt-3"><i class="bi bi-eye-fill"></i> Lida</p>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { toast } from 'vue3-toastify';

export default {
  name: "NotificationsPage",
  data() {
    return {
      notifications: [],
      loading: false,
      activeTab: "all",
    };
  },
  methods: {
    async fetchNotifications(type) {
      this.activeTab = type;
      this.loading = true;
      try {
        const token = localStorage.getItem("AccessToken");
        const endpoint = type === "unread" ? "/notifications/unread" : "/notifications";
        const response = await axios.get(endpoint, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.notifications = type === "unread" ? response.data.unread_notifications : response.data.notifications;
      } catch (error) {
        console.error("Erro ao buscar notificações:", error);
        toast.error("Erro ao buscar notificações.");
        this.notifications = [];
      } finally {
        this.loading = false;
      }
    },
    async markAsRead(id) {
      try {
        const token = localStorage.getItem("AccessToken");
        await axios.post(`/notifications/${id}/mark-as-read`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.notifications = this.notifications.filter(n => n.id !== id);
        toast.success("Notificação marcada como lida!");
        this.fetchNotifications("all");
      } catch (error) {
        console.error("Erro ao marcar como lida:", error);
        toast.error("Erro ao marcar notificação como lida.");
      }
    },
    formatDate(date) {
      const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      };
      return new Date(date).toLocaleDateString("pt-PT", options);
    },
  },
  mounted() {
    this.fetchNotifications("all");
  },
};
</script>







<style scoped>
.notifications-page {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.controls button {
  padding: 8px 16px;
  font-size: 14px;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
  background-color: #fff;
  color: #555;
  transition: background-color 0.3s, color 0.3s;
}

.controls button:hover:not(:disabled) {
  background-color: #f0f0f0;
  color: #000;
}

.controls button.active {
  background-color: black;
  color: white;
  border-color: black;
}

button:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

button:hover:not(:disabled) {
  background-color: #f0f0f0;
}
</style>