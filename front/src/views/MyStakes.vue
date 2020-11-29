<template>
  <div class="myStakes">
    <v-overlay :value="overlay">
      <v-progress-circular indeterminate size="64"/>
    </v-overlay>
    <v-dialog v-model="dialogWithdrew" max-width="500">
      <v-card>
        <v-card-title>
          Congratulations
        </v-card-title>
        <v-card-text>
          <p>You have successful requested unstaking process.</p>
          <p>Assets will come back to your wallet within 50 hours.</p>
          <p>Refresh page in 1-2 minutes to see changes.</p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialogWithdrew = false" color="primary" text>ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogWithdrawCanceled" max-width="500">
      <v-card>
        <v-card-title>
          Withdraw canceled
        </v-card-title>
        <v-card-text>
          <p>Withdraw successfully canceled.</p>
          <p>Refresh page in 1-2 minutes to see changes.</p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialogWithdrawCanceled = false" color="primary" text>ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-card v-if="!isExtensionAvailableWithMinimalVersion">
      <v-card-title>
        Install extraTON extension
      </v-card-title>
      <v-card-text>
        <p>In order to stake you need to install extraTON extension with minimal version 0.4.1.</p>
        <p>Go to <a href="https://chrome.google.com/webstore/detail/extraton/hhimbkmlnofjdajamcojlcmgialocllm"
                    target="_blank">Chrome Store</a>.</p>
      </v-card-text>
    </v-card>
    <v-card v-else-if="!isMainNet">
      <v-card-title>
        Wrong network.
      </v-card-title>
      <v-card-text>
        <p>Please, switch network to main.ton.dev in extraTON extension.</p>
      </v-card-text>
    </v-card>

    <template v-else>
      <withdraw-dialog @success="dialogWithdrew = true" ref="withdrawDialog" :depool="withdrawDepool"/>
      <staking-dialog @success="dialogStaked = true" ref="stakingDialog" :depool="withdrawDepool"/>
      <v-data-table
        :headers="headers"
        :items="items"
        :mobile-breakpoint="100"
        :items-per-page="10000"
        :search="search"
        :sort-by="['stakes.total']"
        :sort-desc="[true]"
        :no-data-text="`No one stake found for address ${address} in main.ton.dev.`"
        :loading="loading"
        class="myStakes__table"
        hide-default-footer
      >
        <template v-slot:top>
          <table-search-toolbar @search="find" @added="loadItems"/>
        </template>
        <template slot="item" slot-scope="props">
          <tr>
            <td>
              <addr :address="props.item.address" :name="props.item.name" :link="props.item.link"/>
            </td>
            <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.total) }}</td>
            <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.reward) }}</td>
            <td style="text-align:center">{{ utils.convertFromNano(props.item.stakes.my.withdrawValue) }}</td>
            <td style="text-align:center">{{ props.item.stakes.my.reinvest ? 'staked' : 'withdrawing' }}</td>
            <td class="myStakes__table__actions">
              <div>
                <v-btn @click="stake(props.item.id)" color="secondary" x-small>add stake</v-btn>
              </div>
              <div>
                <v-btn v-if="props.item.stakes.my.reinvest" @click="withdraw(props.item.id)" color="secondary" x-small>
                  withdraw
                </v-btn>
              </div>
              <template v-if="!props.item.stakes.my.reinvest || props.item.stakes.my.withdrawValue - 0 > 0">
                <div>
                  <v-btn @click="ticktock(props.item.id)" x-small>ticktock</v-btn>
                </div>
                <div>
                  <v-btn @click="cancelWithdrawing(props.item.id)" color="warning" x-small>cancel withdrawing</v-btn>
                </div>
              </template>
            </td>
          </tr>
        </template>
      </v-data-table>
    </template>

  </div>
</template>

<script>
import utils from "@/utils";
import freeton from "freeton";
import Addr from "@/components/Addr";
import TableSearchToolbar from "@/components/TableSearchToolbar";
import StakingDialog from "@/components/StakingDialog";
import WithdrawDialog from "@/components/WithdrawDialog";

export default {
  components: {StakingDialog, WithdrawDialog, Addr, TableSearchToolbar},
  data() {
    return {
      config: global.config,
      utils,
      search: '',
      items: [],
      loading: true,
      headers: [
        {text: 'Address', value: 'address', align: 'start', sortable: false,},
        {text: 'Total', value: 'stakes.my.total', align: 'center', sortable: true,},
        {text: 'Reward', value: 'stakes.my.reward', align: 'center', sortable: true,},
        {text: 'Withdraw', value: 'stakes.my.withdrawValue', align: 'center', sortable: true},
        {text: 'Status', value: 'stakes.my.reinvest', align: 'center', sortable: true},
        {sortable: false},
      ],
      isExtensionAvailableWithMinimalVersion: true,
      isMainNet: true,
      address: '',
      dialogWithdrew: false,
      dialogWithdrawCanceled: false,
      dialogStaked: false,
      activeDepoolId: null,
      overlay: false,
    }
  },
  async mounted() {
    setTimeout(async function () {
      this.isExtensionAvailableWithMinimalVersion = await utils.isExtensionAvailableWithMinimalVersion();
      if (this.isExtensionAvailableWithMinimalVersion) {
        const provider = new freeton.providers.ExtensionProvider(window.freeton);
        this.address = (await provider.getSigner()).getWallet().getAddress();
        const network = await provider.getNetwork();
        this.isMainNet = network.id === 1;
        this.init();
      }
    }.bind(this), 1000);
  },
  computed: {
    withdrawDepool() {
      if (null === this.activeDepoolId) {
        return null;
      }
      return this.items.find(o => o.id === this.activeDepoolId);
    }
  },
  methods: {
    init() {
      this.loadItems();
    },
    async loadItems() {
      this.loading = true;
      try {
        const response = await this.$http.get('/api/depools');
        this.items = response.body.filter(function (depool) {
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
      } catch (response) {
        this.$snack.danger({text: 'Error ' + response.status});
      } finally {
        this.loading = false;
      }
    },
    find(query) {
      this.search = query;
    },
    withdraw(id) {
      this.activeDepoolId = id;
      this.$refs.withdrawDialog.open();
    },
    stake(id) {
      this.activeDepoolId = id;
      this.$refs.stakingDialog.open();
    },
    async cancelWithdrawing(depoolId) {
      this.overlay = true;
      try {
        const provider = new freeton.providers.ExtensionProvider(window.freeton);
        const network = await provider.getNetwork();
        if (network.id !== 1) {
          this.$snack.success({text: 'Please, switch network to main.ton.dev in extraTON extension.'})
          return;
        }
        const depool = this.items.find(o => o.id === depoolId);
        await utils.sendTransactionToDepool(
          provider,
          depool.address,
          'cancelWithdrawal',
          {},
          utils.transactionAdditionalFee
        );
        this.dialogWithdrawCanceled = true;
      } catch (e) {
        console.error(e);
        if (e.code !== 1000/*Canceled by user*/) {
          this.$snack.danger({text: undefined !== e.text ? e.text : 'Unknown error'})
        }
      } finally {
        this.overlay = false;
      }
    },
    async ticktock(depoolId) {
      this.overlay = true;
      try {
        const provider = new freeton.providers.ExtensionProvider(window.freeton);
        const network = await provider.getNetwork();
        if (network.id !== 1) {
          this.$snack.success({text: 'Please, switch network to main.ton.dev in extraTON extension.'})
          return;
        }
        const depool = this.items.find(o => o.id === depoolId);
        await utils.sendTransactionToDepool(
          provider,
          depool.address,
          'ticktock',
          {},
          utils.transactionAdditionalFee
        );
        this.$snack.danger({text: 'Ticktock successfully sent.'})
      } catch (e) {
        console.error(e);
        if (e.code !== 1000/*Canceled by user*/) {
          this.$snack.danger({text: undefined !== e.text ? e.text : 'Unknown error'})
        }
      } finally {
        this.overlay = false;
      }
    },
  }
}
</script>

<style lang="scss">
.myStakes {
  &__table {
    &__actions {
      text-align: center;

      div > button {
        margin: 2px 0 !important;
      }
    }
  }
}
</style>
