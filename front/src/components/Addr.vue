<template>
  <div class="addr">
    <span v-if="name" class="addr__name text-overline" v-html="protectedHtmlName"/>
    <span v-else class="text-overline">
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

    <v-tooltip v-if="link" bottom>
      <template v-slot:activator="{ on, attrs }">
        <v-btn v-bind="attrs" v-on="on" :href="link" target="_blank" icon small>
          <v-icon small>mdi-open-in-new</v-icon>
        </v-btn>
      </template>
      <span>Open in explorer</span>
    </v-tooltip>
  </div>
</template>

<script>
import xss from "anchorme";
import anchorme from "anchorme";

export default {
  props: {
    address: String,
    link: String,
    name: {type: String, default: null},
  },
  computed: {
    protectedHtmlName() {
      return xss(
        anchorme({
          input: this.name,
          options: {
            attributes: {
              target: '_blank'
            },
          }
        })
      );
    }
  }
}
</script>

<style lang="scss">
.addr {
  &__name a{
    text-decoration: none;
  }
}
</style>
