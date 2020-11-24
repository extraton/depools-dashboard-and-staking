<template>
  <div>
    <v-dialog v-model="dialogStaked" max-width="500">
      <v-card>
        <v-card-title>
          Congratulations
        </v-card-title>
        <v-card-text>
          <p>You have successful staked!</p>
          <p>You can see result in the page My Stakes in 1-2 minutes.</p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialogStaked = false" color="primary" text>ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogInstall" max-width="500">
      <v-card>
        <v-card-title>
          Install extraTON extension
        </v-card-title>
        <v-card-text>
          <p>In order to stake you need to install extraTON extension with minimal version 0.4.0.</p>
          <p>Go to <a href="https://chrome.google.com/webstore/detail/extraton/hhimbkmlnofjdajamcojlcmgialocllm"
                      target="_blank">Chrome Store</a>.</p>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialogInstall = false" color="primary" text>ok</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <staking-dialog @success="dialogStaked = true" ref="stakingDialog" :depool="stakingDepool"/>

    <v-data-table
        :headers="headers"
        :items="items"
        :mobile-breakpoint="100"
        :loading="listing.loading"
        :items-per-page="10000"
        :search="search"
        :sort-by="['stakes.total']"
        :sort-desc="[true]"
        hide-default-footer
    >
      <template v-slot:top>
        <table-search-toolbar @search="find" @added="loadItems"/>
      </template>
      <template slot="item" slot-scope="props">
        <tr>
          <td>
            <addr :address="props.item.address"/>
          </td>
          <td style="text-align:center">{{ props.item.params.validatorRewardFraction }}%</td>
          <td style="text-align:center">{{ props.item.stakes.participantsNum }}</td>
          <td style="text-align:center">
            <span>{{ utils.convertFromNano(props.item.stakes.total, 0) }}</span>
            <v-icon color="primary" right small>mdi-diamond-stone</v-icon>
          </td>
          <td>
            <v-btn @click="stake(props.item.id)" color="primary" :disabled="isStakingDialogOpening" x-small>
              stake now
            </v-btn>
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
</template>

<script>
import utils from "@/utils";
import Addr from "@/components/Addr";
import StakingDialog from "@/components/StakingDialog";
import TableSearchToolbar from "@/components/TableSearchToolbar";

export default {
  components: {TableSearchToolbar, StakingDialog, Addr},
  data() {
    return {
      config: global.config,
      utils,
      search: '',
      items: [],
      listing: null,
      headers: [
        {text: 'Address', value: 'address', align: 'start', sortable: false,},
        {text: 'Investor Fee', value: 'params.validatorRewardFraction', align: 'center', sortable: true,},
        {text: 'Participants', value: 'stakes.participantsNum', align: 'center', sortable: true,},
        {text: 'Total Assets', value: 'stakes.total', align: 'center', sortable: true},
        {sortable: false},
      ],
      dialogInstall: false,
      dialogStaked: false,
      stakingDepoolId: null,
      isStakingDialogOpening: false,
    }
  },
  created() {
    this.listing = this.$ewll.initListingForm(this, {
      url: '/crud/depool',
      sort: {id: 'desc'},
      success: function (response) {
        this.items = response.body.items;
      }.bind(this),
    });
  },
  mounted() {
    this.init()
  },
  computed: {
    stakingDepool() {
      if (null === this.stakingDepoolId) {
        return null;
      }
      return this.items.find(o => o.id === this.stakingDepoolId);
    }
  },
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
    async stake(id) {
      this.isStakingDialogOpening = true;
      try {
        if (!await utils.isExtensionAvailableWithMinimalVersion()) {
          this.dialogInstall = true;
          return;
        }
        this.stakingDepoolId = id;
        this.$refs.stakingDialog.open();
      } finally {
        this.isStakingDialogOpening = false;
      }
    },
  }
}
</script>

<style lang="scss">
</style>
