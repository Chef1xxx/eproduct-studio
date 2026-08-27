<template>
    <DataTable :value="products" class="seller-products-table">
        <Column header="Фото">
            <template #body="{ data }">
                <div class="seller-products-table__thumb">
                    <img v-if="data.image_path" :src="data.image_path" :alt="data.name" />
                    <span v-else>Нет фото</span>
                </div>
            </template>
        </Column>
        <Column field="name" header="Название" />
        <Column header="Категория">
            <template #body="{ data }">
                {{ data.category?.name ?? '—' }}
            </template>
        </Column>
        <Column header="Цена">
            <template #body="{ data }">
                {{ data.price }} ₽
            </template>
        </Column>
        <Column field="created_at" header="Создан" />
    </DataTable>
</template>

<script setup lang="ts">
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

defineProps<{
    products: App.DTO.ProductDto[];
}>();
</script>

<style scoped lang="scss">
.seller-products-table {
    &__thumb {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        background: #e4e7eb;
        font-size: 0.7rem;
        color: #829ab1;
        overflow: hidden;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }
}
</style>