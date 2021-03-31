<template>

  <v-dialog v-model="dialog" width="500">
    <template v-slot:activator="{ on: dialog }">
      <v-tooltip bottom>
        <template v-slot:activator="{ on: tooltip }">
          <v-btn v-on="{ ...tooltip, ...dialog }" icon small>
            <v-icon small>mdi-qrcode</v-icon>
          </v-btn>
        </template>
        <span>QR-code</span>
      </v-tooltip>
    </template>
    <v-card>
      <v-card-title>
        Depool QR-code
      </v-card-title>
      <v-card-text class="text-center">
        <vue-qr :text="address"/>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="dialog = false" color="primary" text>
          Close
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import VueQr from 'vue-qr';
export default {
  components: {VueQr,},
  props: {
    address: String,
  },
  data() {
    return {
      dialog: false,
    }
  },
}
</script>
