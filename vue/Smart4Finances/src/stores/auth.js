import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {

    //const user = ref({}); // Inicializa com um objeto vazio para evitar 'null'
    const user = ref(null); // Inicializa com um objeto vazio para evitar 'null'
    const token = ref('')

    const userId = computed(() => {
        return user.value ? user.value.id : -1
    })

    const userName = computed(() => {
        return user.value ? user.value.name : ''
    })

    const getFirstLastName = (fullName) => {
        const names = fullName.trim().split(' ')
        const firstName = names[0] ?? ''
        const lastName = names.length > 1 ? names[names.length - 1] : ''
        return (firstName + ' ' + lastName).trim()
    }

    const userFirstLastName = computed(() => {
        return getFirstLastName(userName.value)
    })

    const userEmail = computed(() => {
        return user.value ? user.value.email : ''
    })

    // This function is "private" - not exported by the store
    const clearUser = () => {
        user.value = null
        token.value = ''
        localStorage.removeItem('AccessToken')
        axios.defaults.headers.common.Authorization = ''
    }

    const login = async (credentials) => {

        try {
            const responseLogin = await axios.post('/login', credentials)
            token.value = responseLogin.data.access_token;
            localStorage.setItem('AccessToken', token.value)
            const userId = getUserIdFromToken();
            axios.defaults.headers.common.Authorization = 'Bearer ' + token.value
            const responseUser = await axios.get(`/users/${userId}`)
            user.value = responseUser.data
            return user.value
        } catch (e) {
            clearUser()
            return false
        }
    }

    // Login usando o token armazenado no localStorage
    const loginWithToken = async () => {
        const storedToken = localStorage.getItem("AccessToken");
        if (!storedToken) {
            return false; // Retorna false se não houver token
        }

        try {
            axios.defaults.headers.common.Authorization = 'Bearer ' + storedToken;

            const userId = getUserIdFromToken();
            const responseUser = await axios.get(`/users/${userId}`);
            user.value = responseUser.data;
            return user.value;
        } catch (error) {
            console.error(error);
            clearUser();
            return false;
        }
    }


    // Login usando o token armazenado no localStorage
    const atualizar = async () => {
        const storedToken = localStorage.getItem("AccessToken");
        if (!storedToken) {
            return false; // Retorna false se não houver token
        }

        try {
            axios.defaults.headers.common.Authorization = 'Bearer ' + storedToken;

            const userId = getUserIdFromToken();
            const responseUser = await axios.get(`/users/${userId}`);
            user.value = responseUser.data;
            return user.value;
        } catch (error) {
            console.error(error);
            clearUser();
            return false;
        }
    }

    const getUserIdFromToken = () => {
        const token = localStorage.getItem("AccessToken");
        if (token) {
            const payload = JSON.parse(atob(token.split(".")[1])); // Decodifica o payload do token
            return payload.sub; // Retorna o ID do user (sub)
        }
        return null;
    }

    const logout = async () => {
        try {   
            await axios.post('/logout', {}, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem("AccessToken")}`
                }
            })     
            localStorage.removeItem('AccessToken')
            clearUser()
            return true
        } catch (e) {
            clearUser()
            return false
        }
    }

    return {
        user, userId, userName, userFirstLastName, userEmail,token,
        login,atualizar, loginWithToken, logout, getFirstLastName
    }
})