<template>
    <PublicLayout>
        <section class="seller-page">
            <div class="seller-page__head">
                <h1 class="seller-page__title">Мои товары</h1>
                <Link href="/seller/products/create">
                    <Button label="Добавить товар" />
                </Link>
            </div>

            <Message v-if="flashSuccess" severity="success" :closable="false" class="seller-page__flash">
                {{ flashSuccess }}
            </Message>

            <p v-if="products.length === 0" class="seller-page__empty">
                Пока нет товаров. Нажмите «Добавить товар».
            </p>

            <SellerProductsTable v-else :products="products" />
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Message from 'primevue/message';
import PublicLayout from '@/widgets/layout/PublicLayout.vue';
import SellerProductsTable from '@/widgets/seller-products/SellerProductsTable.vue';

defineProps<{
    products: App.DTO.ProductDto[];
}>();

const page = usePage();

const flashSuccess = computed(() => {
    const flash = page.props.flash as { success?: string | null } | undefined;
    return flash?.success ?? null;
});
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

    &__flash {
        margin-bottom: 1rem;
    }

    &__empty {
        color: #486581;
    }
}
</style>