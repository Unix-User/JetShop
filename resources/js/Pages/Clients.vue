<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { onMounted, ref, reactive } from 'vue';
import axios from 'axios';

const clients = ref([]);
const pagination = ref({});
const clientForm = reactive({
  id: null,
  name: '',
  address: '',
  email: '',
  phone: ''
});
const isFormVisible = ref(false);
const isEditing = ref(false);

onMounted(async () => {
  const savedPage = localStorage.getItem('currentPage') ? parseInt(localStorage.getItem('currentPage'), 10) : 1;
  await fetchPage(savedPage);
});

const fetchPage = async (page) => {
  try {
    const response = await axios.get(`/clients/list?page=${page}`);
    clients.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    };
    localStorage.setItem('currentPage', page.toString());
  } catch (error) {
    console.error('Failed to fetch clients:', error);
  }
};

const openClientForm = (client = null, editMode = false) => {
  if (client) {
    clientForm.id = client.id;
    clientForm.name = client.name;
    clientForm.address = client.address;
    clientForm.email = client.email;
    clientForm.phone = client.phone;
    isEditing.value = editMode;
  } else {
    clientForm.id = null;
    clientForm.name = '';
    clientForm.address = '';
    clientForm.email = '';
    clientForm.phone = '';
    isEditing.value = true; // Enable editing for new client
  }
  isFormVisible.value = true;
};

const saveClient = async () => {
  if (!isEditing.value) return;
  try {
    const method = clientForm.id ? 'put' : 'post';
    const url = clientForm.id ? `/clients/${clientForm.id}` : '/clients';
    await axios[method](url, {
      name: clientForm.name,
      address: clientForm.address,
      email: clientForm.email,
      phone: clientForm.phone
    });
    // Fetch the last page to show the newly added client
    if (!clientForm.id) {
      await fetchPage(pagination.value.last_page);
    } else {
      await fetchPage(pagination.value.current_page); // Fetch the current page from pagination state
    }
    alert('Client saved successfully!');
    isFormVisible.value = false;
    isEditing.value = false;
  } catch (error) {
    console.error('Failed to save client:', error);
    alert('Failed to save client.');
  }
};

const removeClient = async (client) => {
  if (!confirm(`Are you sure you want to remove ${client.name}?`)) {
    return;
  }
  try {
    await axios.delete(`/clients/${client.id}`);
    clients.value = clients.value.filter(c => c.id !== client.id);
    alert('Client removed successfully!');
  } catch (error) {
    console.error('Failed to remove client:', error);
    alert('Failed to remove client.');
  }
};

const closeForm = () => {
  isFormVisible.value = false;
  isEditing.value = false;
};
</script>

<template>
  <AppLayout :title="'Clients'">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Clients
        </h2>
        <button @click="openClientForm(null, true)" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">
          Add Client
        </button>
      </div>
    </template>

    <div v-if="isFormVisible" class="fixed inset-0 bg-gray-700 bg-opacity-75 overflow-y-auto h-full w-full" @click.self="closeForm">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
          <button @click="closeForm" class="absolute top-0 right-0 p-2 text-white bg-red-600 hover:bg-red-800 rounded-md">
            Close
          </button>
          <div v-if="clientForm.id" class="text-lg leading-6 font-medium text-gray-800 dark:text-white">Edit Client</div>
          <div v-else class="text-lg leading-6 font-medium text-gray-800 dark:text-white">Add Client</div>
          <div class="mt-2 px-7 py-3">
            <input type="text" v-model="clientForm.name" :readonly="!isEditing" placeholder="Name" class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
            <input type="text" v-model="clientForm.address" :readonly="!isEditing" placeholder="Address" class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
            <input type="email" v-model="clientForm.email" :readonly="!isEditing" placeholder="Email" class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
            <input type="tel" v-model="clientForm.phone" :readonly="!isEditing" placeholder="Phone" class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
          </div>
          <div class="items-center px-4 py-3" v-if="isEditing">
            <button @click="saveClient" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500">
              Save
            </button>
            <button @click="closeForm" class="mt-3 px-4 py-2 bg-gray-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
              <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Name
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider hidden lg:table-cell">
                    Address
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider hidden md:table-cell">
                    Email
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider hidden sm:table-cell">
                    Phone
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="client in clients" :key="client.id" @click="openClientForm(client, false)" class="cursor-pointer">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200">
                    {{ client.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200 hidden lg:table-cell">
                    {{ client.address }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200 hidden md:table-cell">
                    {{ client.email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-200 hidden sm:table-cell">
                    {{ client.phone }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button @click.stop="openClientForm(client, true)" class="text-blue-400 hover:text-blue-600">Edit</button>
                    <button @click.stop="removeClient(client)" class="text-red-400 hover:text-red-600 ml-4">Remove</button>
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

