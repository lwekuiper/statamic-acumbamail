<template>
    <div class="acumbamail-merge-fields-fieldtype-wrapper">
        <v-select
            append-to-body
            v-model="value"
            :clearable="true"
            :options="fields"
            :reduce="(option) => option.id"
            :placeholder="__('Choose...')"
            :searchable="true"
            :taggable="true"
            @input="$emit('input', $event)"
        />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            fields: [],
            listId: null,
        }
    },

    mounted() {
        this.$watch(
            () => this.$store.state.publish?.[this.storeName]?.values?.list_ids,
            (listIds) => {
                const firstList = Array.isArray(listIds) ? listIds[0] : null;
                if (firstList && firstList !== this.listId) {
                    this.listId = firstList;
                    this.refreshFields(firstList);
                }
            },
            { immediate: true, deep: true }
        );
    },

    methods: {
        refreshFields(listId) {
            this.$axios
                .get(cp_url(`/acumbamail/merge-fields/${listId}`))
                .then(response => {
                    this.fields = response.data;
                })
                .catch(() => { this.fields = []; });
        }
    }
};
</script>
