<template>
    <nav class="navbar bg-blue-500 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold"><i class="bi bi-coin"></i> Smart4Finances</h1>
            <ul class="flex space-x-1 mt-2">
                <li>
                    <button @click="$emit('navigate', 'login')" class="hover:text-gray-200"><i class="bi bi-box-arrow-in-right"></i> Login</button>
                </li>
                <li>
                    <button @click="$emit('navigate', 'register')" class="hover:text-gray-200"><i class="bi bi-person-bounding-box"></i> Registar</button>
                </li>
                <li>
                    <button @click="$emit('navigate', 'test')" class="hover:text-gray-200"><i class="bi bi-person-bounding-box"></i> teste</button>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
export default {
    name: "NavbarLoginRegister",
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
