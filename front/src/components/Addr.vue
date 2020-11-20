<template>
  <div>

    <span class="text-overline">
      {{ address.substr(0, 8) }}...{{ address.substr(-6) }}
    </span>
    <v-tooltip bottom>
      <template v-slot:activator="{ on, attrs }">
        <v-btn v-bind="attrs"
               v-on="on"
               v-clipboard="address"
               @click="$snack.success({text: 'Copied'})"
               icon small
        >
          <v-icon small>mdi-content-copy</v-icon>
        </v-btn>
      </template>
      <span>Copy address</span>
    </v-tooltip>
  </div>
</template>

<script>
export default {
  props: {address: String},
  data: () => ({}),
}
</script>
