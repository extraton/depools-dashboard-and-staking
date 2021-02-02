<template>
  <v-card class="setName">
    <v-card-title>Set depool name (For depool's owner)</v-card-title>
    <v-card-text>
      <p>
        In order to set depool name you should call our special contract.
        <br/>You must use the wallet used to create depool.
        <br/>Fill form to generate call, apply and confirm transfer.
        <br/>You can see name of your depool in 3-5 minutes in table.
        <br/>You can add just name as well as site domain name.
      </p>
      <v-text-field v-model="form.wallet" label="Multisig address" outlined/>
      <v-text-field v-model="form.depool" label="Depool address" outlined/>
      <v-text-field v-model="form.name" maxlength="16" label="Desired Name" outlined/>
      <div class="setName__code">
        <code>wget
          https://raw.githubusercontent.com/tonlabs/ton-labs-contracts/master/solidity/safemultisig/SafeMultisigWallet.abi.json</code>
        <code>
          tonos-cli call {{ form.wallet }} submitTransaction
          '{"dest":"0:2c2e4082141c1923137f9172f44863dcfb354655f503ae64e41d0a992cc393b4","value":100000000,"bounce":true,"allBalance":false,"payload":"{{
            payload
          }}"}'
          --abi "./SafeMultisigWallet.abi.json" --sign ""
        </code>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import utils from "@/utils";
import freeton from "freeton";

const namesAbi = require('@/contracts/names.abi.json');

export default {
  data: () => ({
    form: {
      wallet: '',
      depool: '',
      name: '',
    },
    payload: '',
  }),
  watch: {
    form: {
      async handler(form) {
        const depool = form.depool.trim();
        const name = form.name.trim();
        if ('' === depool || '' === name) {
          this.payload = '';
        } else {
          const message = await utils.generateMessage(namesAbi, 'setName', {
            depoolAddress: depool,
            name: freeton.utils.stringToHex(name),
          });
          this.payload = message.bodyBase64;
        }
      },
      deep: true,
    }
  },
}
</script>

<style lang="scss">
.setName {
  &__code code {
    display: block;
    line-break: anywhere;
  }

  &__code code:not(:first-child) {
    margin-top: 10px;
  }
}
</style>
