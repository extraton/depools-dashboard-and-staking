<template>
  <div>
    <v-overlay :value="overlay">
      <v-progress-circular indeterminate size="64"/>
    </v-overlay>
    <v-dialog v-model="dialogSuccess" max-width="500">
      <v-card>
        <v-card-title>
          Congratulations
        </v-card-title>
        <v-card-text>
          <p>You have successful requested unstaking process.</p>
          <p>Assets will come back to your wallet within one day.</p>
          <p>Status of your stake in this table may persist some time, don't worry.</p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialogSuccess = false" color="primary" text>ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-card v-if="!isExtensionAvailable">
      <v-card-title>
        Install extraTON extension
      </v-card-title>
      <v-card-text>
        <p>In order to stake you need to install extraTON extension.</p>
        <p>Go to <a href="https://chrome.google.com/webstore/detail/extraton/hhimbkmlnofjdajamcojlcmgialocllm"
                    target="_blank">Chrome Store</a>.</p>
      </v-card-text>
    </v-card>
    <v-card v-else-if="null === isMainNet" style="height:30px" loading/>
    <v-card v-else-if="!isMainNet">
      <v-card-title>
        Wrong network.
      </v-card-title>
      <v-card-text>
        <p>Please, change network to main.ton.dev in extraTON extension.</p>
      </v-card-text>
    </v-card>
    <v-data-table
        v-else
        :headers="headers"
        :items="items"
        :mobile-breakpoint="100"
        hide-default-footer
        :items-per-page="10000"
        :search="search"
        :sort-by="['stakes.total']"
        :sort-desc="[true]"
        :no-data-text="`No one stake found for address ${address} in main.ton.dev.`"
    >
      <template v-slot:top>
        <table-search-toolbar @search="find" @added="loadItems"/>
      </template>
      <template slot="item" slot-scope="props">
        <tr>
          <td>
            <addr :address="props.item.address"/>
          </td>
          <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.total) }}</td>
          <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.reward) }}</td>
          <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.withdrawValue) }}</td>
          <td style="text-align:center">{{ props.item.stakes.my.reinvest ? 'yes' : 'no' }}</td>
          <td>
            <v-btn @click="withdrawAll(props.item.id)" color="secondary" x-small>withdraw all</v-btn>
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import utils from "@/utils";
import freeton from "freeton";
import Addr from "@/components/Addr";
import {TONClient} from "ton-client-web-js";

const depoolAbi = require('@/contracts/DePool.abi.json');
import TableSearchToolbar from "@/components/TableSearchToolbar";

export default {
  components: {Addr, TableSearchToolbar},
  data() {
    return {
      config: global.config,
      utils,
      search: '',
      items: [],
      listing: null,
      headers: [
        {text: 'Address', value: 'address', align: 'start', sortable: false,},
        {text: 'Total', value: 'stakes.my.total', align: 'center', sortable: true,},
        {text: 'Reward', value: 'stakes.my.reward', align: 'center', sortable: true,},
        {text: 'Withdraw', value: 'stakes.my.withdrawValue', align: 'center', sortable: true},
        {text: 'Reinvest', value: 'stakes.my.reinvest', align: 'center', sortable: true},
        {sortable: false},
      ],
      isExtensionAvailable: false,
      isMainNet: null,
      address: '',
      dialogSuccess: false,
      overlay: false,
    }
  },
  created() {
    this.listing = this.$ewll.initListingForm(this, {
      url: '/crud/depool',
      sort: {id: 'desc'},
      success: function (response) {
        this.items = response.body.items.filter(function (depool) {
          for (const stake of depool.stakes.items) {
            if (stake.address === this.address) {
              let item = depool;
              item.stakes.my = stake.info;
              this.items.push(item);
              return true;
            }
          }
          return false;
        }.bind(this));
      }.bind(this),
    });
  },
  async mounted() {
    this.isExtensionAvailable = typeof window.freeton !== 'undefined';
    const provider = new freeton.providers.ExtensionProvider(window.freeton);
    this.address = (await provider.getSigner()).getWallet().getAddress();
    const network = await provider.getNetwork();
    this.isMainNet = network.id === 1;
    this.init()
  },
  computed: {},
  methods: {
    init() {
      this.loadItems();
    },
    loadItems() {
      this.listing.submit();
    },
    find(query) {
      this.search = query;
    },
    async withdrawAll(depoolId) {
      try {
        this.overlay = true;
        const provider = new freeton.providers.ExtensionProvider(window.freeton);
        const network = await provider.getNetwork();
        if (network.id !== 1) {
          this.$snack.danger({text: 'Please, change network to main.ton.dev.'})
          return;
        }
        const client = await TONClient.create({servers: []});
        const message = await client.contracts.createRunBody({
          abi: depoolAbi,
          function: 'withdrawAll',
          params: {},
          internal: true
        });
        const signer = await provider.getSigner();
        const wallet = signer.getWallet();
        const depool = this.items.find(o => o.id === depoolId);
        await wallet.transfer(depool.address, '500000000', true, message.bodyBase64);
        this.dialogSuccess = true;
      } catch (e) {
        console.error(e);
        if (e.code !== 1000/*Canceled by user*/) {
          const error = undefined !== e.text ? e.text : 'Unknown error';
          this.$snack.danger({text: error})
        }
      } finally {
        this.overlay = false;
      }
    }
  }
}
</script>

<style lang="scss">
</style>
