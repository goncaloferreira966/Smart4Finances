<template>
  <div class="container mt-4 mb-5">
    <h2 class="card-title" style="color: black;">Dashboard</h2>
    
  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue3-toastify';
import { useAuthStore } from "@/stores/auth";

export default {
  data() {
    return { 
      apiDomain: import.meta.env.VITE_API_DOMAIN,
    };
  },
  created() {
   
  },
  mounted() {
    const authStore = useAuthStore();
    this.userRole = authStore.user.data.type;
  },
  methods: {
    getUserIdFromToken() {
      const token = localStorage.getItem("AccessToken");
      if (token) {
        const payload = JSON.parse(atob(token.split(".")[1]));
        return payload.sub;
      }
      return null;
    },
  },
};
</script>