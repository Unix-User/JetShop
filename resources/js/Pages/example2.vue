<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import ProductsTable from "@/Components/ProductsTable.vue";
import { onMounted, ref, reactive, watch } from "vue";
import axios from "axios";

const orders = ref([]);
const pagination = ref({});
const clients = ref([]);
const products = ref([]);
const productSearch = ref("");
const productSearchResults = ref([]);

const orderForm = reactive({
    id: null,
    client_id: "",
    total_price: 0,
    products: [],
});

const isFormVisible = ref(false);
const isEditing = ref(false);

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
    try {
        const response = await axios.get(`/orders/list?page=${page}`);
        orders.value = response.data.data;
        pagination.value = response.data;
        localStorage.setItem("currentPage", page.toString());
    } catch (error) {
        console.error("Failed to fetch orders:", error);
    }
};

const saveOrder = async () => {
    try {
        const productDetails = orderForm.products.map((product) => ({
            product_id: product.id,
            quantity: product.quantity,
            unit_price: product.price,
        }));
        console.log(orderForm.products);
        console.log(productDetails);

        const response = await axios.post("/order", {
            client_id: orderForm.client_id,
            total_price: orderForm.total_price,
            order_details: productDetails,
        });

        if (response.status === 201) {
            alert("Order saved successfully!");
            fetchOrders(getCurrentPage()); // Refresh the orders list
        } else {
            alert("Failed to save the order.");
        }
    } catch (error) {
        console.error("Error saving order:", error);
        alert("Error saving order. Please check the console for more details.");
    }
};
const fetchClients = async () => {
    try {
        const response = await axios.get("/clients/list");
        clients.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch clients:", error);
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get("/products/list");
        products.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch products:", error);
    }
};

watch(productSearch, (newValue) => {
    productSearchResults.value = filterProductsBySearch(newValue);
});

const filterProductsBySearch = (searchTerm) => {
    return searchTerm === ""
        ? products.value
        : products.value.filter((product) =>
              product.name.toLowerCase().includes(searchTerm.toLowerCase())
          );
};

const handleDataUpdate = (updatedData) => {
    console.log("Updated data received:", updatedData);
};

const openOrderForm = (order = null, editMode = false) => {
    setOrderForm(order, editMode);
    isFormVisible.value = true;
};

const setOrderForm = (order, editMode) => {
    orderForm.id = order?.id || null;
    orderForm.client_id = order?.client_id || "";
    orderForm.total_price = order?.total_price || 0;
    orderForm.products = order?.products || [];
    isEditing.value = editMode;
};

const addProductToOrder = (product) => {
    const existingProduct = orderForm.products.find((p) => p.id === product.id);
    if (existingProduct) {
        existingProduct.quantity += 1;
    }
};

const removeProductFromOrder = (productId) => {
    const index = orderForm.products.findIndex((p) => p.id === productId);
    if (index !== -1) {
        orderForm.products.splice(index, 1);
    }
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

        <!-- modal -->
        <div
            v-if="isFormVisible"
            class="fixed inset-0 bg-gray-700 bg-opacity-75 overflow-y-auto h-full w-full"
            @click.self="closeForm"
        >
            <div
                class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800"
            >
                <div class="mt-3 text-center">
                    <button
                        @click="closeForm"
                        class="absolute top-0 right-0 p-2 text-white bg-red-600 hover:bg-red-800 rounded-md"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 384 512"
                            class="h-4 w-4 fill-current"
                        >
                            <!-- SVG content unchanged -->
                            <path
                                d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"
                            />
                        </svg>
                    </button>
                    <div
                        v-if="orderForm.id"
                        class="text-lg leading-6 font-medium text-gray-800 dark:text-white"
                    >
                        Edit Order
                    </div>
                    <div
                        v-else
                        class="text-lg leading-6 font-medium text-gray-800 dark:text-white"
                    >
                        Add Order
                    </div>
                    <div class="mt-2 px-7 py-3">
                        <select
                            v-model="orderForm.client_id"
                            :disabled="!isEditing"
                            class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white"
                        >
                            <option
                                v-for="client in clients"
                                :value="client.id"
                            >
                                {{ client.name }}
                            </option>
                        </select>

                        <div class="overflow-x-auto">
                            <ProductsTable
                                :product-search-results="productSearchResults"
                                @update-data="handleDataUpdate"
                                :add-product-to-order="addProductToOrder"
                                :remove-product-from-order="
                                    removeProductFromOrder
                                "
                            />
                        </div>
                    </div>
                    <div class="items-center px-4 py-3" v-if="isEditing">
                        <button
                            @click="saveOrder"
                            class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            Save
                        </button>
                        <button
                            @click="closeForm"
                            class="mt-3 px-4 py-2 bg-gray-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
                                        {{
                                            clients.find(
                                                (c) => c.id === order.client_id
                                            ).name
                                        }}
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
                    <div class="px-5 py-5 flex justify-between">
                        <button
                            @click="fetchPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                        >
                            Previous
                        </button>
                        <span
                            >Page {{ pagination.current_page }} of
                            {{ pagination.last_page }}</span
                        >
                        <button
                            @click="fetchPage(pagination.current_page + 1)"
                            :disabled="
                                pagination.current_page >= pagination.last_page
                            "
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
