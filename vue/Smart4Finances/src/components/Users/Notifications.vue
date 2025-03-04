<template>
  <div class="notifications-page mb-5">
    <h2 class="card-title" style="color: black;">Notificações</h2>
    <div class="controls mt-4">
      <button @click="fetchNotifications('unread')" :disabled="loading" :class="{ active: activeTab === 'unread' }" class="btn btn-outline-danger">
        <i class="bi bi-bell-fill"></i> Não Lidas
      </button>
      <button @click="fetchNotifications('all')" :disabled="loading" :class="{ active: activeTab === 'all' }" class="btn btn-outline-primary">
        <i class="bi bi-bell-fill"></i> Todas as Notificações
      </button>
    </div>
    <div v-if="loading" class="loading mt-3">A Carregar notificações...</div>
    <div v-else class="mt-4">
      <div v-if="notifications.length === 0" class="alert alert-info">
        Não há notificações disponíveis.
      </div>
      <ul v-else class="list-group">
        <li v-for="notification in notifications" :key="notification.id" class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <p><strong>Mensagem:</strong> {{ notification.data.message || 'Sem mensagem disponível' }}</p>
            <p><strong>Data:</strong> {{ formatDate(notification.created_at) }}</p>
          </div>
          <div>
            <button v-if="!notification.read_at" @click="markAsRead(notification.id)" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-bell-slash-fill"></i> Marcar como lida
            </button>
            <span v-else class="text-success"><i class="bi bi-eye-fill"></i> Lida</span>
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
        toast.success("Notificação marcada como lida!");
        this.fetchNotifications("all");
      } catch (error) {
        console.error("Erro ao marcar como lida:", error);
        toast.error("Erro ao marcar notificação como lida.");
      }
    },
    formatDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric", hour: "2-digit", minute: "2-digit" };
      return new Date(date).toLocaleDateString("pt-PT", options);
    },
  },
  mounted() {
    this.fetchNotifications("all");
  },
};
</script>
