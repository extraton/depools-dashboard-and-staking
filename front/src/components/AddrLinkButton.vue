<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on, attrs }">
      <v-btn v-bind="attrs"
             v-on="on"
             v-clipboard="link"
             @click="$snack.success({text: 'Copied'})"
             icon small
      >
        <v-icon small>mdi-link-variant</v-icon>
      </v-btn>
    </template>
    <span>Copy link</span>
  </v-tooltip>
</template>

<script>
export default {
  props: {
    address: String,
  },
  data() {
    return {
      config: global.config,
    }
  },
  computed: {
    link() {
      return `https://${this.config.domain}${this.$router.resolve({name: this.$route.name, query: {s: this.address}}).href}`;
    }
  }
}
</script>
