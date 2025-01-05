<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { onMounted, ref, reactive } from 'vue';
import axios from 'axios';

const sales = ref([]);
const pagination = ref({});
const clients = ref([]);
const products = ref([]);
const searchQuery = ref('');

onMounted(async () => {
  const savedPage = localStorage.getItem('currentPage') ? parseInt(localStorage.getItem('currentPage'), 10) : 1;
  await fetchPage(savedPage);
  await fetchClients();
  await fetchProducts();
});

const fetchPage = async (page) => {
  try {
    const response = await axios.get(`/sales/list?page=${page}&search=${searchQuery.value}`);
    sales.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    };
    localStorage.setItem('currentPage', page.toString());
  } catch (error) {
    console.error('Failed to fetch sales:', error);
  }
};

const fetchClients = async () => {
  try {
    const response = await axios.get('/clients/list');
    clients.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch clients:', error);
  }
};

const fetchProducts = async () => {
  try {
    const response = await axios.get('/products/list');
    products.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch products:', error);
  }
};

const handleSearch = async () => {
  await fetchPage(1);
};
</script>

<template>
  <AppLayout :title="'Sales'">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Sales
        </h2>
        <input v-model="searchQuery" @input="handleSearch" placeholder="Search sales..." class="border p-2 rounded">
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
              <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Client Name
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Product Name
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Quantity
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Total Price
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="sale in sales" :key="sale.id">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200">
                    {{ clients.find(c => c.id === sale.client_id).name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200">
                    {{ products.find(p => p.id === sale.product_id).name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200">
                    {{ sale.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200">
                    {{ sale.total_price }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="px-5 py-5 flex justify-between">
            <button @click="fetchPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">
              Previous
            </button>
            <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
            <button @click="fetchPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
