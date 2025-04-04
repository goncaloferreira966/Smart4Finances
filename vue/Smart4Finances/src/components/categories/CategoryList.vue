<template>
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg" style="margin-top: 7vh; margin-bottom: 7vh;">
        <h2 class="text-2xl font-bold mb-6 text-center">Gerenciar Categorias</h2>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-grow">
                <input type="text" v-model="searchQuery" placeholder="Pesquisar categorias..."
                    class="p-2 border rounded flex-grow" @input="applyFiltersDebounced" />
                <button @click="searchQuery = ''" class="bg-gray-200 p-2 rounded hover:bg-gray-300">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <button @click="showAddCategoryModal = true"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                <i class="bi bi-plus-circle"></i> Nova Categoria
            </button>
        </div>

        <div v-if="isLoading" class="text-center py-8">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <div v-else-if="categories.length === 0" class="text-center py-8 text-gray-500">
            <i class="bi bi-emoji-frown text-4xl"></i>
            <p class="mt-2">Nenhuma categoria encontrada.</p>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Nome</th>
                        <th class="py-3 px-6 text-left">Tipo</th>
                        <th class="py-3 px-6 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <tr v-for="category in filteredCategories" :key="category.id"
                        class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left">{{ category.name }}</td>
                        <td class="py-3 px-6 text-left">{{ getCategoryTypeName(category.type) }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <button @click="editCategory(category)"
                                    class="transform hover:text-blue-500 hover:scale-110 mr-3">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button @click="confirmDeleteCategory(category)"
                                    class="transform hover:text-red-500 hover:scale-110">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal para adicionar/editar categoria -->
        <div v-if="showAddCategoryModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">{{ editMode ? 'Editar Categoria' : 'Nova Categoria' }}</h3>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="categoryName">
                        Nome da Categoria
                    </label>
                    <input id="categoryName" type="text" v-model="newCategory.name"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Nome da categoria" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="categoryType">
                        Tipo de Categoria
                    </label>
                    <select id="categoryType" v-model="newCategory.type"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="expense">Despesa</option>
                        <option value="income">Receita</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2">
                    <button @click="showAddCategoryModal = false"
                        class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button @click="saveCategory" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        {{ editMode ? 'Atualizar' : 'Adicionar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de confirmação de exclusão -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">Confirmar Exclusão</h3>
                <p class="mb-4">Tem certeza que deseja excluir a categoria <strong>{{ categoryToDelete?.name
                        }}</strong>?</p>
                <p class="mb-4 text-red-500">Esta ação não pode ser desfeita e pode afetar despesas e receitas que usam
                    esta categoria.</p>

                <div class="flex justify-end space-x-2">
                    <button @click="showDeleteModal = false"
                        class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button @click="deleteCategory" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import debounce from 'lodash/debounce';
import { toast } from 'vue3-toastify';

export default {
    name: 'CategoryList',
    data() {
        return {
            categories: [],
            filteredCategories: [],
            searchQuery: '',
            isLoading: true,
            showAddCategoryModal: false,
            showDeleteModal: false,
            editMode: false,
            categoryToDelete: null,
            newCategory: {
                id: null,
                name: '',
                type: 'expense' // 'expense' ou 'income'
            }
        };
    },
    created() {
        this.applyFiltersDebounced = debounce(this.applyFilters, 300);
    },
    mounted() {
        this.loadCategories();
    },
    methods: {
        loadCategories() {
            this.isLoading = true;
            axios.get('/categories')
                .then(response => {
                    this.categories = response.data;
                    this.applyFilters();
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Erro ao carregar categorias:', error);
                    toast.error('Erro ao carregar categorias. Tente novamente.');
                    this.isLoading = false;
                });
        },
        applyFilters() {
            if (!this.searchQuery) {
                this.filteredCategories = [...this.categories];
            } else {
                const query = this.searchQuery.toLowerCase();
                this.filteredCategories = this.categories.filter(category =>
                    category.name.toLowerCase().includes(query)
                );
            }
        },
        getCategoryTypeName(type) {
            return type === 'expense' ? 'Despesa' : type === 'income' ? 'Receita' : type;
        },
        editCategory(category) {
            this.editMode = true;
            this.newCategory = {
                id: category.id,
                name: category.name,
                type: category.type
            };
            this.showAddCategoryModal = true;
        },
        saveCategory() {
            if (!this.newCategory.name.trim()) {
                toast.error('O nome da categoria é obrigatório.');
                return;
            }

            const categoryData = {
                name: this.newCategory.name.trim(),
                type: this.newCategory.type
            };

            if (this.editMode) {
                // Atualizar categoria existente
                axios.put(`/categories/${this.newCategory.id}`, categoryData)
                    .then(response => {
                        toast.success('Categoria atualizada com sucesso!');
                        this.loadCategories();
                        this.showAddCategoryModal = false;
                        this.resetNewCategory();
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar categoria:', error);
                        toast.error('Erro ao atualizar a categoria. Tente novamente.');
                    });
            } else {
                // Adicionar nova categoria
                axios.post('/categories', categoryData)
                    .then(response => {
                        toast.success('Categoria adicionada com sucesso!');
                        this.loadCategories();
                        this.showAddCategoryModal = false;
                        this.resetNewCategory();
                    })
                    .catch(error => {
                        console.error('Erro ao adicionar categoria:', error);
                        toast.error('Erro ao adicionar a categoria. Tente novamente.');
                    });
            }
        },
        confirmDeleteCategory(category) {
            this.categoryToDelete = category;
            this.showDeleteModal = true;
        },
        deleteCategory() {
            if (!this.categoryToDelete) return;

            axios.delete(`/categories/${this.categoryToDelete.id}`)
                .then(response => {
                    toast.success('Categoria excluída com sucesso!');
                    this.loadCategories();
                    this.showDeleteModal = false;
                    this.categoryToDelete = null;
                })
                .catch(error => {
                    console.error('Erro ao excluir categoria:', error);

                    // Tratamento de erro específico se a categoria estiver em uso
                    if (error.response && error.response.status === 422) {
                        toast.error('Esta categoria não pode ser excluída porque está sendo usada em despesas ou receitas.');
                    } else {
                        toast.error('Erro ao excluir a categoria. Tente novamente.');
                    }
                });
        },
        resetNewCategory() {
            this.editMode = false;
            this.newCategory = {
                id: null,
                name: '',
                type: 'expense'
            };
        }
    }
};
</script>

<style scoped>
.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>