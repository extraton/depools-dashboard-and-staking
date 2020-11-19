<template>
  <v-app>
    <v-snackbar v-model="snack.isShow">{{ snack.text }}</v-snackbar>
    <div>
      <v-app-bar>
        <v-icon left>mdi-diamond-stone</v-icon>
        <v-toolbar-title>FreeTON Depool Dashboard</v-toolbar-title>
      </v-app-bar>
    </div>

    <v-card width="85%" style="margin:30px auto">
      <router-view :search="search"/>
    </v-card>
  </v-app>
</template>

<script>
// import freeton from 'freeton';
// import depoolAbi from './../../contracts/DePool.abi.json';
// import { TONClient/*, setWasmOptions*/ } from 'ton-client-web-js';

export default {
  name: 'App',

  components: {},

  data: () => ({
    config: global.config,
    search: '',
    snack: {isShow: false, text: ''},
  }),
  created() {
    this.config.totalNodes = '∞';
    this.$snack.listener = function (text) {
      this.snack.text = text;
      this.snack.isShow = false;
      this.snack.isShow = true;
    }.bind(this);
  },
  methods: {
    // async delegate() {
    //   if (typeof window.freeton === 'undefined') {
    //     throw 'Extension is not available.';
    //   }
    //   let provider = new freeton.providers.ExtensionProvider(window.freeton);
    //   let network = await provider.getNetwork();
    //   if (network.id !== 2) {
    //     throw 'Change network to net.ton.dev.';
    //   }
    //   // const signer = provider.getSigner();
    //
    //   const client = await TONClient.create({
    //     servers: ['net.ton.dev']
    //   });
    //
    //   console.log(client);
    //   //
    //   //
    //   // 'te6ccgEBAQEADgAAGAqsGP0AAAACVAvkAA=='
    //   //
    //   // const contract = new freeton.Contract(signer, depoolAbi, '0:a44d4b1268a6fdb6efbcdf14be3c35bf07322f63e6ed157bdc17c5219518def0');
    //   // const result = await contract.functions.addOrdinaryStake.run({stake: 11});
    //   // console.log(result);
    // },
  }
};
</script>
