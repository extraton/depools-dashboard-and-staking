<template>
    <v-toolbar flat>
        <v-text-field v-model="searchValue"
                      @input="search"
                      v-on:keyup.enter="search"
                      hide-details
                      append-icon="mdi-magnify"
                      label="Search"
                      style="width:100%"
                      single-line
        />
      <slot/>
    </v-toolbar>
</template>

<script>
export default {
    components: {},
    props: {},
    data() {
        return {
            config: global.config,
            searchValue: '',
        }
    },
    methods: {
        search() {
            this.$emit('search', this.searchValue);
        }
    }
}
</script>

<style lang="scss">
</style>
