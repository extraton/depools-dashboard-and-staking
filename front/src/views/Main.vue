<template>
  <div class="depoolsList">
    <v-dialog v-model="dialogStaked" max-width="500">
      <v-card>
        <v-card-title>
          Congratulations
        </v-card-title>
        <v-card-text>
          <p>You have successful staked!</p>
          <p>You can see result in the page My Stakes in 3-5 minutes.</p>
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
          <p>In order to stake you need to install extraTON extension with minimal version 0.4.1.</p>
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

    <div>
      <main-stat :stat="stat"/>
    </div>
    <div>
      <v-data-table
        @update:sort-by="updateSortBy"
        @update:sort-desc="updateSortDesc"
        :headers="headers"
        :items="items"
        :mobile-breakpoint="100"
        :loading="loading"
        :items-per-page="itemsPerPage"
        :page.sync="page"
        :search="search"
        :sort-by="sortBy"
        :sort-desc="sortDesc"
        class="depoolsList__list"
      >
        <template v-slot:top>
          <table-search-toolbar @search="find" @added="loadItems"/>
        </template>
        <template slot="item" slot-scope="props">
          <tr :class="{'depoolsList__list__row--namedBorder': isNamedBorder(props.index)}">
            <td style="width:120px">
              <addr-copy-button :address="props.item.address"/>
              <addr-explorer-button :link="props.item.link"/>
              <addr-link-button :address="props.item.address"/>
            </td>
            <td style="padding:0">
              <v-icon>mdi-numeric-{{props.item.versionView}}</v-icon>
            </td>
            <td style="padding-left:0">
              <addr :address="props.item.address" :name="props.item.name" :link="props.item.link"/>
            </td>
            <td style="text-align:center;padding:0">
              <stability :values="props.item.stability" :key="`stability-${props.item.id}`"/>
            </td>
            <td style="text-align:center">{{ utils.convertFromNano(props.item.params.minStake) }}</td>
            <td style="text-align:center">{{ utils.convertFromNano(props.item.params.validatorAssurance) }}</td>
            <td style="text-align:center">{{ props.item.params.validatorRewardFraction }}%</td>
            <td style="text-align:center">{{ props.item.stakes.participantsNum }}</td>
            <td style="text-align:center">
              {{ utils.convertFromNano(props.item.stakes.total, 0) }}
              <v-icon color="primary" small>mdi-diamond-stone</v-icon>
            </td>
            <td>
              <span v-if="props.item.params.poolClosed">Depool's closing</span>
              <v-btn v-else @click="stake(props.item.id)" color="primary" :disabled="isStakingDialogOpening" x-small>
                stake now
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>
    </div>
  </div>
</template>

<script>
import utils from "@/utils";
import Addr from "@/components/Addr";
import StakingDialog from "@/components/StakingDialog";
import TableSearchToolbar from "@/components/TableSearchToolbar";
import Stability from "@/components/Stability";
import AddrCopyButton from "@/components/AddrCopyButton";
import AddrExplorerButton from "@/components/AddrExplorerButton";
import AddrLinkButton from "@/components/AddrLinkButton";
import MainStat from "@/components/MainStat";

export default {
  components: {
    MainStat,
    AddrLinkButton, Stability, TableSearchToolbar, StakingDialog, Addr, AddrCopyButton, AddrExplorerButton
  },
  data() {
    return {
      config: global.config,
      utils,
      search: '',
      sortBy: ['isNameSet', 'stakes.total'],
      sortDesc: [true, true],
      items: [],
      itemsPerPage: 50,
      page: 1,
      namedDepoolsAmount: 0,
      stat: null,
      loading: false,
      headers: [
        {sortable: false, filterable: false,},
        {text: 'Ver', value: 'versionView', align: 'start', sortable: true, filterable: false,},
        {text: 'Name/Address', value: 'address', align: 'start', sortable: false,},
        {text: 'Stability', align: 'center', sortable: false, filterable: false,},
        {text: 'Min Stake', value: 'params.minStake', align: 'center', sortable: true, filterable: false,},
        {text: 'Assurance', value: 'params.validatorAssurance', align: 'center', sortable: true, filterable: false,},
        {text: 'Fee', value: 'params.validatorRewardFraction', align: 'center', sortable: true, filterable: false,},
        {text: 'Members', value: 'stakes.participantsNum', align: 'center', sortable: true, filterable: false,},
        {text: 'Assets', value: 'stakes.total', align: 'center', sortable: true, filterable: false,},
        {value: 'name', align: ' d-none', sortable: false,},
        {sortable: false, filterable: false,},
      ],
      dialogInstall: false,
      dialogStaked: false,
      stakingDepoolId: null,
      isStakingDialogOpening: false,
    }
  },
  mounted() {
    this.init();
  },
  computed: {
    stakingDepool() {
      if (null === this.stakingDepoolId) {
        return null;
      }
      return this.items.find(o => o.id === this.stakingDepoolId);
    },
  },
  methods: {
    init() {
      this.loadItems();
    },
    async loadItems() {
      this.loading = true;
      try {
        const response = await this.$http.get('/api/depools');
        this.items = response.body.depools;
        this.stat = response.body.stat;
        this.namedDepoolsAmount = response.body.namedDepoolsAmount;
      } catch (response) {
        this.$snack.danger({text: 'Error ' + response.status});
      } finally {
        this.loading = false;
      }
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
    updateSortBy(value) {
      if (value.length === 1) {//dirty hack
        this.sortBy = ['isNameSet', value[0]];
      }
    },
    updateSortDesc(value) {
      if (value.length === 1) {//dirty hack
        this.sortDesc = [true, value[0]];
      }
    },
    isNamedBorder(i) {
      return i === this.namedDepoolsAmount - 1 && this.page === Math.ceil(this.namedDepoolsAmount / this.itemsPerPage);
    }
  }
}
</script>

<style lang="scss">
.depoolsList {
  &__list {
    margin-top: 15px;

    > .v-data-table__wrapper {
      > table {
        > tbody {
          > tr {
            > td {
              height: 63px !important;
            }
          }
        }
      }
    }

    &__row {
      &--namedBorder td {
        border-bottom: 3px double #ffffff !important;
      }
    }

    .v-data-table-header {
      th:nth-child(2),th:nth-child(3) {
        padding-left: 0 !important;
      }
    }

    .v-data-footer__select {
      visibility: hidden;
    }

    &__infoTable {
      margin: 0 auto;
      width: 100%;
      min-width: 190px;

      td {
        width: 50%;
      }

      .text-caption {
        color: hsla(0, 0%, 100%, .7)
      }

      td:first-child {
        text-align: right;
        padding-right: 3px;
      }

      td:last-child {
        text-align: left;
      }
    }
  }
}
</style>
