<template>
    <div class="products-table">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        Product Name
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        Price
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        Quantity
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        Total
                    </th>
                    <th
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                <tr
                    v-for="product in productSearchResults"
                    :key="product.id"
                    @click="toggleDetails(product.id)"
                    class="cursor-pointer"
                >
                    <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                    >
                        {{ product.name }}
                    </td>
                    <td class="text-gray-900 dark:text-white">
                        ${{ product.price }}
                    </td>
                    <td>
                        <input
                            type="number"
                            v-model="product.quantity"
                            min="0"
                            class="w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 text-gray-900 dark:bg-gray-800 dark:text-white"
                        />
                    </td>
                    <td class="text-gray-900 dark:text-white">
                        ${{ product.quantity * product.price }}
                    </td>
                    <td>
                        <button
                            @click.stop="removeProductFromResults(product.id)"
                            class="text-red-600 hover:text-red-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512"
                                class="h-6 w-6 fill-current"
                            >
                                <path
                                    d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"
                                />
                            </svg>
                        </button>
                    </td>
                </tr>
                <!-- New row for adding a product -->
                <tr>
                    <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                    >
                        <input
                            type="text"
                            v-model="newProduct.name"
                            placeholder="Enter product name"
                            class="w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 text-gray-900 dark:bg-gray-800 dark:text-white"
                        />
                    </td>
                    <td class="text-gray-900 dark:text-white">
                        <input
                            type="text"
                            v-model="newProduct.price"
                            placeholder="Enter price"
                            class="w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 text-gray-900 dark:bg-gray-800 dark:text-white"
                        />
                    </td>
                    <td>
                        <input
                            type="number"
                            v-model="newProduct.quantity"
                            min="0"
                            placeholder="Enter quantity"
                            class="w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 text-gray-900 dark:bg-gray-800 dark:text-white"
                        />
                    </td>
                    <td class="text-gray-900 dark:text-white">
                        ${{ newProduct.quantity * newProduct.price }}
                    </td>
                    <td>
                        <button
                            @click="addNewProduct()"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 576 512"
                                class="h-6 w-6 fill-current"
                            >
                                <path
                                    d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96zM252 160c0 11 9 20 20 20h44v44c0 11 9 20 20 20s20-9 20-20V180h44c11 0 20-9 20-20s-9-20-20-20H356V96c0-11-9-20-20-20s-20 9-20 20v44H272c-11 0-20 9-20 20z"
                                />
                            </svg>
                        </button>
                        <button
                            @click="requestCameraPermissions"
                            class="ml-2 text-green-600 hover:text-green-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"
                                class="h-6 w-6 fill-current"
                            >
                                <path
                                    d="M512 144v288c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V144c0-26.5 21.5-48 48-48h48l34.7-69.4C136.6 12.6 152.5 0 171.4 0h169.3c18.8 0 34.7 12.6 40.6 26.6L416 96h48c26.5 0 48 21.5 48 48zm-128 0H128v288h256V144zm-64 144c0 44.2-35.8 80-80 80s-80-35.8-80-80 35.8-80 80-80 80 35.8 80 80z"
                                />
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="total-sum text-gray-900 dark:text-white">
            Total: ${{ totalSum }}
        </div>
    </div>
    <!-- Camera Modal -->
    <transition name="fade">
        <div v-if="isCameraVisible" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
            <div class="bg-white p-4 rounded-lg">
                <qrcode-stream
                    :constraints="selectedConstraints"
                    :formats="selectedBarcodeFormats"
                    @error="onError"
                    @detect="onDetect"
                    @camera-on="onCameraReady"
                />
                <button @click="toggleCameraVisibility" class="mt-2 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Close</button>
            </div>
        </div>
    </transition>
</template>
<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import { watch } from 'vue';

export default {
    components: {
        QrcodeStream
    },
    props: {
        productSearchResults: Array,
        addProductToOrder: Function,
    },
    setup(props, { emit }) {
        watch(() => props.productSearchResults, (newValue) => {
            emit('update-data', newValue);
        }, {
            deep: true // This ensures the watcher triggers on changes within the array objects
        });

        return {}; // setup function should return an object
    },
    data() {
        return {
            expandedProducts: new Map(),
            isMobile: window.innerWidth <= 640,
            newProduct: {
                name: "",
                price: 0,
                quantity: 1,
            },
            isCameraVisible: false,
            selectedConstraints: { facingMode: 'environment' },
            selectedBarcodeFormats: ['qr_code', 'ean_13'],
        };
    },
    computed: {
        totalSum() {
            return this.productSearchResults.reduce((acc, product) => {
                return acc + (product.price * product.quantity);
            }, 0);
        }
    },
    methods: {
        toggleDetails(productId) {
            const currentState = this.expandedProducts.get(productId) || false;
            this.expandedProducts.set(productId, !currentState);
        },
        addNewProduct() {
            this.addProductToOrder(this.newProduct);
            this.productSearchResults.push({ ...this.newProduct });
            this.resetNewProduct();
        },
        resetNewProduct() {
            this.newProduct = { name: "", price: 0, quantity: 1 };
        },
        removeProductFromResults(productId) {
            const index = this.productSearchResults.findIndex(p => p.id === productId);
            if (index !== -1) {
                this.productSearchResults.splice(index, 1);
            }
        },
        onDetect(detectedCodes) {
            if (detectedCodes.length > 0 && detectedCodes[0].rawValue) {
                const qrData = detectedCodes[0].rawValue;
                this.newProduct.name = qrData;
                console.log(`QR Code Detected: ${qrData}`);
                this.addNewProduct();
                this.toggleCameraVisibility();
            } else {
                console.log("No QR code detected.");
            }
        },
        addProductById(productId) {
            const product = this.productSearchResults.find(p => p.id === productId);
            if (product) {
                this.addProductToOrder(product);
            } else {
                console.error('Product not found');
            }
        },
        toggleCameraVisibility() {
            this.isCameraVisible = !this.isCameraVisible;
        },
        requestCameraPermissions() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then((stream) => {
                        this.toggleCameraVisibility();
                        stream.getTracks().forEach(track => track.stop()); // Stop the stream after getting permission
                    })
                    .catch((error) => {
                        alert('Camera permission is not granted. Please allow access to use this feature.');
                        console.error('Camera permission error:', error);
                    });
            } else {
                alert('Your device does not support camera access or it is not secure (HTTPS required).');
            }
        },
    },
    mounted() {
        const updateMobileStatus = () => {
            this.isMobile = window.innerWidth <= 640;
        };
        window.addEventListener("resize", updateMobileStatus);
        updateMobileStatus();
    },
};
</script>

<style scoped>
.products-table {
    max-height: 400px;
    overflow-y: auto;
}
@media (max-width: 640px) {
    .products-table th:nth-child(2),
    .products-table td:nth-child(2),
    .products-table th:nth-child(3),
    .products-table td:nth-child(3),
    .products-table th:nth-child(4),
    .products-table td:nth-child(4) {
        display: none;
    }
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0;
}
</style>
