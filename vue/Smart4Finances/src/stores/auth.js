import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref('');
  const userRole = ref('');

  const userId = computed(() => user.value ? user.value.id : -1);
  const userName = computed(() => user.value ? user.value.name : '');
  
  const getFirstLastName = (fullName) => {
    const names = fullName.trim().split(' ');
    const firstName = names[0] ?? '';
    const lastName = names.length > 1 ? names[names.length - 1] : '';
    return (firstName + ' ' + lastName).trim();
  };

  const userFirstLastName = computed(() => getFirstLastName(userName.value));
  const userEmail = computed(() => user.value ? user.value.email : '');

  const clearUser = () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('AccessToken');
    axios.defaults.headers.common.Authorization = '';
  };

  const getUserIdFromToken = () => {
    const storedToken = localStorage.getItem("AccessToken");
    if (storedToken) {
      const payload = JSON.parse(atob(storedToken.split(".")[1]));
      return payload.sub;
    }
    return null;
  };

  const login = async (credentials) => {
    try {
      const responseLogin = await axios.post('/login', credentials);
      token.value = responseLogin.data.access_token;
      localStorage.setItem('AccessToken', token.value);
      const id = getUserIdFromToken();
      axios.defaults.headers.common.Authorization = 'Bearer ' + token.value;
      const responseUser = await axios.get(`/users/${id}`);
      user.value = responseUser.data;
      userRole.value = user.value.data.type;
      return user.value;
    } catch (e) {
      console.error(e);
      clearUser();
      return false;
    }
  };

  const loginWithToken = async () => {
    const storedToken = localStorage.getItem("AccessToken");
    if (!storedToken) {
      return false;
    }
    try {
      axios.defaults.headers.common.Authorization = 'Bearer ' + storedToken;
      const id = getUserIdFromToken();
      const responseUser = await axios.get(`/users/${id}`);
      user.value = responseUser.data;
      return user.value;
    } catch (error) {
      console.error(error);
      clearUser();
      return false;
    }
  };

  const atualizar = async () => {
    const storedToken = localStorage.getItem("AccessToken");
    if (!storedToken) {
      return false;
    }
    try {
      axios.defaults.headers.common.Authorization = 'Bearer ' + storedToken;
      const id = getUserIdFromToken();
      const responseUser = await axios.get(`/users/${id}`);
      user.value = responseUser.data;
      return user.value;
    } catch (error) {
      console.error(error);
      clearUser();
      return false;
    }
  };

  const logout = async () => {
    try {
      await axios.post('/logout', {}, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("AccessToken")}`
        }
      });
      localStorage.removeItem('AccessToken');
      clearUser();
      return true;
    } catch (e) {
      clearUser();
      return false;
    }
  };

  return {
    user,
    userId,
    userName,
    userFirstLastName,
    userEmail,
    token,
    userRole,
    clearUser,
    login,
    atualizar,
    loginWithToken,
    logout,
    getFirstLastName,
  };
}, {
  persist: true, // Ativa a persistência da store
});
