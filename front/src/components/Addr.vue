<template>
  <div class="addr">
    <div v-if="buttons">
      <addr-copy-button :address="address"/>
    </div>

    <div>
      <span v-if="name" class="addr__name text-subtitle-1" v-html="protectedHtmlName"/>
      <span v-else class="text-overline">
      {{ address.substr(0, 8) }}...{{ address.substr(-6) }}
    </span>
    </div>

    <div v-if="buttons">
      <addr-explorer-button :link="link"/>
    </div>
  </div>
</template>

<script>
import xss from "anchorme";
import anchorme from "anchorme";
import AddrCopyButton from "@/components/AddrCopyButton";
import AddrExplorerButton from "@/components/AddrExplorerButton";

export default {
  components: {AddrExplorerButton, AddrCopyButton},
  props: {
    address: String,
    link: String,
    name: {type: String, default: null},
    buttons: {type: Boolean, default: false}
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
  display: flex;
  align-items: center;

  &__name a {
    text-decoration: none;
  }
}
</style>
