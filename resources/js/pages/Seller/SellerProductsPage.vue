<template>
    <PublicLayout>
        <section class="seller-page">
            <div class="seller-page__head">
                <h1 class="seller-page__title">Мои товары</h1>
                <Button label="Добавить товар" disabled />
            </div>

            <p v-if="products.length === 0" class="seller-page__empty">
                Пока нет товаров. CRUD появится на следующем этапе.
            </p>

            <SellerProductsTable v-else :products="products" />
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import Button from 'primevue/button';
import PublicLayout from '@/widgets/layout/PublicLayout.vue';
import SellerProductsTable from '@/widgets/seller-products/SellerProductsTable.vue';

defineProps<{
    products: App.DTO.ProductDto[];
}>();
</script>

<style scoped lang="scss">
.seller-page {
    &__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    &__title {
        margin: 0;
    }

    &__empty {
        color: #486581;
    }
}
</style>