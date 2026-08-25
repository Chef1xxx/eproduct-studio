<template>
    <PublicLayout>
        <section class="home-page">
            <h1 class="home-page__title">Каталог товаров</h1>
            <ProductGrid :products="products" />
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import PublicLayout from '@/widgets/layout/PublicLayout.vue';
import ProductGrid from '@/widgets/product-catalog/ProductGrid.vue';

defineProps<{
    products: App.DTO.ProductDto[];
}>();
</script>

<style scoped lang="scss">
.home-page {
    &__title {
        margin: 0 0 1.5rem;
    }
}
</style>