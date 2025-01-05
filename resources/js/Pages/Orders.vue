<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import OrderForm from "@/Components/OrderForm.vue";
import { onMounted, ref, reactive, watch } from "vue";
import axios from "axios";

const isFormVisible = ref(false);
const isEditing = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');
const orders = ref([]);
const pagination = ref({});
const clients = ref([]); // Define clients as a reactive reference
const products = ref([]); // Define products as a reactive reference

const initializePage = async () => {
    const currentPage = getCurrentPage();
    await Promise.all([
        fetchOrders(currentPage),
        fetchClients(),
        fetchProducts(),
    ]);
};

onMounted(initializePage);

const getCurrentPage = () => {
    return localStorage.getItem("currentPage")
        ? parseInt(localStorage.getItem("currentPage"), 10)
        : 1;
};

const fetchOrders = async (page) => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/orders/list?page=${page}`); // Corrigir a rota para /orders/list
        orders.value = response.data.data; // Adjust according to the actual response structure
        pagination.value = response.data; // This should include pagination links as HTML
    } catch (error) {
        errorMessage.value = 'Failed to fetch orders.';
    } finally {
        isLoading.value = false;
    }
};

const fetchClients = async () => {
    try {
        const response = await axios.get("/clients/list");
        clients.value = response.data.data; // Properly set clients data
    } catch (error) {
        console.error("Failed to fetch clients:", error);
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get("/products/list");
        products.value = response.data.data; // Properly set products data
    } catch (error) {
        console.error("Failed to fetch products:", error);
    }
};

const openOrderForm = (order = null, editMode = false) => {
    isFormVisible.value = true;
    isEditing.value = editMode;
};

const closeForm = () => {
    isFormVisible.value = false;
    isEditing.value = false;
};
</script>

<template>
    <AppLayout :title="'Orders'">
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Orders
                </h2>
                <button
                    @click="openOrderForm(null, true)"
                    class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded"
                >
                    Add Order
                </button>
            </div>
        </template>

        <OrderForm
            v-if="isFormVisible"
            :clients="clients"
            :products="products"
            :is-editing="isEditing"
            @close-form="closeForm"
            @save-order="fetchOrders(getCurrentPage)"
            @close="isFormVisible = false"
        />

        <div v-if="isLoading">Loading...</div>
        <div v-if="errorMessage">{{ errorMessage }}</div>

        <!-- table -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto"
                        >
                            <thead class="bg-gray-200 dark:bg-gray-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                    >
                                        Client Name
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                    >
                                        Total Price
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="order in orders"
                                    :key="order.id"
                                    @click="openOrderForm(order, false)"
                                    class="cursor-pointer"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                    >
                                        {{ order.client_name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200"
                                    >
                                        {{ order.total_price }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <button
                                            @click.stop="
                                                openOrderForm(order, true)
                                            "
                                            class="text-blue-400 hover:text-blue-600"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click.stop="removeOrder(order)"
                                            class="text-red-400 hover:text-red-600 ml-4"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="pagination">
                        <div v-html="pagination.links"></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

