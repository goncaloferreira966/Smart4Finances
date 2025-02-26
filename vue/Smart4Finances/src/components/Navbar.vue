<template>
    <nav class="navbar bg-blue-500 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold"><i class="bi bi-coin"></i> Smart4Finances</h1>
            <ul class="flex space-x-1 mt-2">
                <li>
                    <button @click="$emit('navigate', 'profile')" class="hover:text-gray-200"><i
                            class="bi bi-person"></i> {{ formattedNickname }}</button>
                </li>
                <!-- Botão de Terminar Sessão -->
                <li>
                    <button @click="handleLogout" class="hover:text-red-500"><i class="bi bi-box-arrow-right"></i>
                        Sair</button>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
import { useAuthStore } from "@/stores/auth"; // Importa a store Pinia

export default {
    name: "Navbar",
    data() {
        return {
            userRole: "", // Armazena o tipo de user
        };
    },
    computed: {
        formattedNickname() {
            const authStore = useAuthStore();
            const nickname = authStore.user?.data?.nickname || ""; // Fallback para evitar erro caso o nome não esteja presente
            return nickname.charAt(0).toUpperCase() + nickname.slice(1);
        },
    },

    mounted() {
        const authStore = useAuthStore();
        this.userRole = authStore.user.data.type;

    },
    methods: {
        async handleLogout() {
            const authStore = useAuthStore(); // Obtém a instância da store
            await authStore.logout();
            this.$emit("logout"); // Emite o evento de logout para redirecionamento
        }
    }
};
</script>

<style scoped>
/* Estilização personalizada para a navbar */
.navbar {
    background-color: lightseagreen;
    /* Cor de fundo personalizada */
    color: white;
    /* Cor do texto */
}

.navbar h1 {
    color: white;
    /* Cor personalizada para o título */
}

.navbar ul {
    display: flex;
    gap: 5px;
}

.navbar ul li {
    list-style: none;
}

.navbar ul li button {
    color: white;
    /* Cor inicial do texto nos botões */
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: color 0.3s ease;
    /* Transição para o efeito hover */
}

.navbar ul li button:hover {
    color: beige;
    /* Cor de destaque ao passar o mouse */
}
</style>
