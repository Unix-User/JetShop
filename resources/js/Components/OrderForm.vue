<template>
    <div class="order-form">
        <div class="fixed inset-0 bg-gray-700 bg-opacity-75 overflow-y-auto h-full w-full" @click.self="closeForm">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <button @click="closeForm" class="absolute top-0 right-0 p-2 text-white bg-red-600 hover:bg-red-800 rounded-md" aria-label="Close form">
                        Close
                    </button>
                    <div v-if="orderForm.id" class="text-lg leading-6 font-medium text-gray-800 dark:text-white">
                        Edit Order
                    </div>
                    <div v-else class="text-lg leading-6 font-medium text-gray-800 dark:text-white">
                        Add Order
                    </div>
                    <div class="mt-2 px-7 py-3">
                        <select v-model="orderForm.client_id" :disabled="!isEditing" class="mb-2 p-1 w-full bg-white dark:bg-gray-900 text-gray-800 dark:text-white rounded-md">
                            <option v-for="client in clients" :value="client.id">
                                {{ client.name }}
                            </option>
                        </select>
                        <ProductsTable :products="orderForm.products" @remove-product="removeProductFromOrder" ref="productsTable" />
                        <!-- Form to add data to the table -->
                        <div class="flex justify-between items-center mt-4">
                            <button @click="toggleCameraVisibility" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                                Scan QR Code
                            </button>
                            <button @click="addEmptyProductRow" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-md">
                                Add Product
                            </button>
                        </div>
                        <transition name="fade">
                            <div v-if="isCameraVisible" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
                                <div class="bg-white p-4 rounded-lg">
                                    <QrcodeReader @decoded="handleDecodedProduct" @close="toggleCameraVisibility" />
                                    <button @click="toggleCameraVisibility" class="mt-2 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Close</button>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <div class="items-center px-4 py-3" v-if="isEditing">
                        <button @click="saveOrder" class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Save
                        </button>
                        <button @click="closeForm" class="mt-3 px-4 py-2 bg-gray-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ProductsTable from "@/Components/ProductsTable.vue"; // Importa o componente ProductsTable
import QrcodeReader from "@/Components/QrcodeReader.vue"; // Importa o componente QrcodeReader
import { ref, reactive, watch, onMounted } from 'vue'; // Importa funções do Vue
import axios from 'axios'; // Import axios for API requests

export default {
    components: {
        ProductsTable, // Registra o componente ProductsTable
        QrcodeReader // Registra o componente QrcodeReader
    },
    props: {
        clients: Array, // Define a propriedade 'clients' como um array
        isFormVisible: Boolean, // Define a propriedade 'isFormVisible' como um booleano
        isEditing: Boolean, // Define a propriedade 'isEditing' como um booleano
        products: Array // Define a propriedade 'products' como um array
    },
    setup(props, { emit }) {
        const orderForm = reactive({ // Cria um objeto reativo para o formulário de pedido
            client_id: '',
            products: []
        });

        const newProduct = ref({ // Cria uma referência reativa para um novo produto
            name: '',
            price: 0,
            quantity: 0
        });

        const isCameraVisible = ref(false);

        const handleDecodedProduct = async (decodedString) => { // Função para lidar com o produto decodificado
            try {
                const response = await axios.get(`/api/products/${decodedString}`);
                newProduct.value = {
                    name: response.data.name,
                    price: response.data.price,
                    quantity: 1 // Default quantity
                };
                addProductToOrder(); // Adiciona o produto ao pedido
                toggleCameraVisibility(); // Fecha a modal da câmera
            } catch (error) {
                console.error('Failed to fetch product details:', error);
            }
        };

        const addProductToOrder = () => { // Função para adicionar um produto ao pedido
            const productToAdd = { ...newProduct.value }; // Copia os valores do novo produto
            orderForm.products.push(productToAdd); // Adiciona o novo produto ao array de produtos do pedido
            emit('update-products', orderForm.products); // Emite um evento para atualizar os produtos
            newProduct.value = { name: '', price: 0, quantity: 0 }; // Reseta os valores do novo produto
        };

        const addEmptyProductRow = () => { // Função para adicionar uma linha vazia para preenchimento manual
            orderForm.products.push({ name: '', price: 0, quantity: 1 });
        };

        const removeProductFromOrder = (product) => { // Função para remover um produto do pedido
            const index = orderForm.products.indexOf(product); // Encontra o índice do produto no array
            if (index > -1) {
                orderForm.products.splice(index, 1); // Remove o produto do array
                emit('update-products', orderForm.products); // Emite um evento para atualizar os produtos
            }
        };

        const toggleCameraVisibility = () => {
            isCameraVisible.value = !isCameraVisible.value;
        };

        watch(() => props.isFormVisible, (newVal, oldVal) => { // Observa mudanças na visibilidade do formulário
            if (oldVal === true && newVal === false) {
                orderForm.client_id = ''; // Reseta o id do cliente
                orderForm.products = []; // Reseta os produtos
            }
        });

        const closeForm = () => { // Função para fechar o formulário
            emit('close'); // Emite um evento para fechar o formulário
        };

        return {
            orderForm,
            newProduct,
            addProductToOrder,
            addEmptyProductRow,
            handleDecodedProduct,
            removeProductFromOrder,
            closeForm,
            isCameraVisible,
            toggleCameraVisibility
        };
    }
};
</script>

<style scoped>
.order-form {
    /* Styling for the order form */
}
</style>
