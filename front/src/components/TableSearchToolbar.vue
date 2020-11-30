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
                  clearable
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
  created() {
    this.searchValue = undefined !== this.$route.query.s
        ? this.$route.query.s
        : '';
  },
  mounted() {
    this.$emit('search', this.searchValue);
  },
  methods: {
    search() {
      let query = {};
      if (this.searchValue !== '' && this.searchValue !== null) {
        query.s = this.searchValue;
      }
      this.$router.replace({name: this.$route.name, query, hash: this.$route.hash});
      this.$emit('search', this.searchValue);
    }
  }
}
</script>

<style lang="scss">
</style>
