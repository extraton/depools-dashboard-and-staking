<template>
  <v-card class="setName">
    <v-card-title>Set depool name (For depool's owner)</v-card-title>
    <v-card-text>
        <p>
          In order to set depool name you should call our special contract.
          <br/>You must use the wallet used to create depool.
          <br/>Fill form to generate call, apply and confirm transfer.
          <br/>You can see name of your depool in 1-2 minutes in table.
          <br/>You can add just name as well as site domain name.
        </p>
        <v-text-field v-model="wallet" label="Multisig address" outlined/>
        <v-text-field v-model="name" maxlength="16" label="Desired Name" outlined/>
        <v-text-field v-model="pathToKeys" label="Path to keys" outlined/>
        <div class="setName__code">
          <code>wget
            https://raw.githubusercontent.com/tonlabs/ton-labs-contracts/master/solidity/safemultisig/SafeMultisigWallet.abi.json</code>
          <code>
            tonos-cli call {{ wallet }} submitTransaction
            '{"dest":"0:bd38e2c38a4177243f3a47c7248ea1c689798b83d77265e6fbd12f954a3ebe05","value":100000000,"bounce":true,"allBalance":false,"payload":"{{
              payload
            }}"}'
            --abi "./SafeMultisigWallet.abi.json" --sign "{{ pathToKeys }}"
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
    wallet: '',
    name: '',
    pathToKeys: '',
    payload: '',
  }),
  watch: {
    async name(value) {
      if (value === '') {
        this.payload = '';
      } else {
        const message = await utils.generateMessage(namesAbi, 'setName', {name: freeton.utils.stringToHex(value)});
        this.payload = message.bodyBase64;
      }
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
