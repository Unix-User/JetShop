<template>
    <div class="products-table">
        <table :class="tableClasses">
            <thead>
                <tr>
                    <th v-for="column in columns" :key="column.id"
                        scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider"
                    >
                        {{ column.label }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-800 dark:text-gray-200 uppercase tracking-wider">
                        Actions (Remove)
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800">
                <tr v-for="product in products" :key="product.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
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
                        ${{ product.total }}
                    </td>
                    <td>
                        <button
                            @click="emitRemoveProduct(product.id)"
                            class="text-red-600 hover:text-red-800"
                            aria-label="Remove product"
                        >
                            Remove
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    props: {
        products: Array,
        columns: Array,
        darkMode: Boolean
    },
    emits: ['remove-product'],
    methods: {
        emitRemoveProduct(productId) {
            this.$emit('remove-product', productId);
        }
    },
    computed: {
        tableClasses() {
            return {
                'w-full': true,
                'divide-y': true,
                'divide-gray-200': !this.darkMode,
                'dark:divide-gray-700': this.darkMode,
                'bg-gray-200': !this.darkMode,
                'dark:bg-gray-700': this.darkMode
            };
        }
    }
};
</script>

<style scoped>
.products-table {
    max-height: 400px;
    overflow-y: auto;
}
@media (max-width: 640px) {
    .products-table th, .products-table td {
        display: block; /* Change to block or another suitable display type */
    }
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0;
}
</style>

