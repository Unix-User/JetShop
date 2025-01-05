<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { onMounted, ref, reactive } from "vue";
import axios from "axios";
import QrcodeReader from "@/Components/QrcodeReader.vue";

const products = ref([]);
const pagination = ref({});
const productForm = reactive({
    id: null,
    name: "",
    price: "",
    description: "",
    code: "",
});
const isFormVisible = ref(false);
const isEditing = ref(false);
const isCameraVisible = ref(false);

onMounted(async () => {
    const savedPage = localStorage.getItem("currentPage")
        ? parseInt(localStorage.getItem("currentPage"), 10)
        : 1;
    await fetchPage(savedPage);
});

const fetchPage = async (page) => {
    try {
        const response = await axios.get(`/products/list?page=${page}`);
        products.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
        };
        localStorage.setItem("currentPage", page.toString());
    } catch (error) {
        console.error("Falha ao buscar os produtos:", error);
    }
};

const openProductForm = (product = null, editMode = false) => {
    if (product) {
        productForm.id = product.id;
        productForm.name = product.name;
        productForm.price = product.price;
        productForm.description = product.description;
        productForm.code = product.code;
        isEditing.value = editMode;
    } else {
        productForm.id = null;
        productForm.name = "";
        productForm.price = "";
        productForm.description = "";
        productForm.code = "";
        isEditing.value = true; // Habilitar edição para novo produto
    }
    isFormVisible.value = true;
};

const saveProduct = async () => {
    if (!isEditing.value) return;
    try {
        const method = productForm.id ? "put" : "post";
        const url = productForm.id
            ? `/products/${productForm.id}`
            : "/products";
        await axios[method](url, {
            name: productForm.name,
            price: productForm.price,
            description: productForm.description,
            code: productForm.code,
        });
        if (!productForm.id) {
            await fetchPage(pagination.value.last_page);
        } else {
            await fetchPage(pagination.value.current_page);
        }
        alert("Produto salvo com sucesso!");
        isFormVisible.value = false;
        isEditing.value = false;
    } catch (error) {
        console.error("Falha ao salvar o produto:", error);
        alert("Falha ao salvar o produto.");
    }
};

const removeProduct = async (product) => {
    if (!confirm(`Tem certeza de que deseja remover ${product.name}?`)) {
        return;
    }
    try {
        await axios.delete(`/products/${product.id}`);
        products.value = products.value.filter((p) => p.id !== product.id);
        alert("Produto removido com sucesso!");
    } catch (error) {
        console.error("Falha ao remover o produto:", error);
        alert("Falha ao remover o produto.");
    }
};

const truncateText = (text, length = 100) => {
    return text.length > length ? text.substring(0, length) + "..." : text;
};

const closeForm = () => {
    isFormVisible.value = false;
    isEditing.value = false;
    isCameraVisible.value = false;
};

const handleDecodedProduct = (decodedString) => {
    productForm.code = decodedString;
    isCameraVisible.value = false; // Fechar a câmera após decodificar
};

const openCamera = () => {
    isCameraVisible.value = true; // Abrir a câmera diretamente
};
</script>

<template>
    <AppLayout :title="'Produtos'">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Produtos
                </h2>
                <button
                    @click="openProductForm(null, true)"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Adicionar Produto
                </button>
            </div>
        </template>

        <div
            v-if="isFormVisible"
            class="fixed inset-0 bg-gray-700 bg-opacity-75 overflow-y-auto h-full w-full"
            @click.self="closeForm"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-md rounded-md bg-white dark:bg-gray-800"
            >
                <div class="text-center">
                    <h3
                        v-if="productForm.id"
                        class="text-lg font-medium text-gray-900 dark:text-white"
                    >
                        Editar Produto
                    </h3>
                    <h3
                        v-else
                        class="text-lg font-medium text-gray-900 dark:text-white"
                    >
                        Adicionar Produto
                    </h3>
                    <div class="mt-4">
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Nome</label
                        >
                        <input
                            type="text"
                            id="name"
                            v-model="productForm.name"
                            :readonly="!isEditing"
                            placeholder="Nome do Produto"
                            class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div class="mt-4">
                        <label
                            for="price"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Preço</label
                        >
                        <input
                            type="text"
                            id="price"
                            v-model="productForm.price"
                            :readonly="!isEditing"
                            placeholder="Preço do Produto"
                            class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div class="mt-4">
                        <label
                            for="code"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Código</label
                        >
                        <div class="relative mt-1">
                            <input
                                type="text"
                                id="code"
                                v-model="productForm.code"
                                :readonly="!isEditing"
                                placeholder="Código do Produto"
                                class="block w-full rounded-md shadow-sm dark:bg-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-10"
                            />
                            <button
                                type="button"
                                @click="openCamera"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-500 focus:outline-none"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512"
                                    class="h-5 w-5 fill-current"
                                >
                                    <path
                                        d="M149.1 64.8L138.7 96 64 96C28.7 96 0 124.7 0 160L0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64l-74.7 0L362.9 64.8C356.4 45.2 338.1 32 317.4 32L194.6 32c-20.7 0-39 13.2-45.5 32.8zM256 192a96 96 0 1 1 0 192 96 96 0 1 1 0-192z"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label
                            for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Descrição</label
                        >
                        <textarea
                            id="description"
                            v-model="productForm.description"
                            :readonly="!isEditing"
                            placeholder="Descrição do Produto"
                            rows="3"
                            class="mt-1 block w-full rounded-md shadow-sm dark:bg-gray-900 dark:text-white border-gray-300 dark:border-gray-700 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                    <div class="mt-6 flex justify-between">
                        <button
                            @click="closeForm"
                            type="button"
                            class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-800 dark:text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Cancelar
                        </button>
                        <button
                            v-if="isEditing"
                            @click="saveProduct"
                            type="button"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Salvar Produto
                        </button>
                    </div>
                    <div v-if="isCameraVisible" class="mt-4">
                        <QrcodeReader
                            @decoded="handleDecodedProduct"
                            @close="() => (isCameraVisible.value = false)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-200 dark:bg-gray-700">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                >
                                    Nome
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                >
                                    Preço
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                >
                                    Código
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                >
                                    Descrição
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                >
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="product in products"
                                :key="product.id"
                                @click="openProductForm(product, false)"
                                class="cursor-pointer"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                >
                                    {{ truncateText(product.name, 20) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                >
                                    {{ product.price }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                >
                                    {{ product.code }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                >
                                    {{ truncateText(product.description, 50) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <button
                                        @click.stop="
                                            openProductForm(product, true)
                                        "
                                        class="text-blue-400 hover:text-blue-600"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        @click.stop="removeProduct(product)"
                                        class="text-red-400 hover:text-red-600 ml-4"
                                    >
                                        Remover
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-5 py-5 flex justify-between">
                        <button
                            @click="fetchPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                        >
                            Anterior
                        </button>
                        <span
                            >Página {{ pagination.current_page }} de
                            {{ pagination.last_page }}</span
                        >
                        <button
                            @click="fetchPage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page >= pagination.last_page
                            "
                        >
                            Próxima
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.qr-code-scanner-input {
    position: relative;
}
</style>
