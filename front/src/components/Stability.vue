<template>
  <div class="stability" ref="q">
    <v-sparkline
      :value="hackedValues"
      :gradient="['#2196f3', '#2196f3', '#f72047']"
      smooth auto-draw
    />
  </div>
</template>

<script>
export default {
  props: {
    values: Array,
  },
  data() {
    return {
      hackedValues: [],
    }
  },
  created() {
    this.hackedValues = [0].concat(this.values);
  },
  mounted() {
    const path = this.$refs.q.getElementsByTagName('path')[0];
    let d = path.getAttribute('d').split(' ');
    d.splice(1, 2)
    path.setAttribute('d', d);
  }
}
</script>

<style lang="scss">
.stability {
}
</style>
