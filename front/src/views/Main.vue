<template>
  <div class="depoolsList">
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
          <p>In order to stake you need to install extraTON extension with version 0.4.0.</p>
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
      :loading="loading"
      :items-per-page="50"
      :search="search"
      :sort-by="[sort[0]]"
      :sort-desc="[sort[1]]"
      class="depoolsList__list"
    >
      <template v-slot:top>
        <table-search-toolbar @search="find" @added="loadItems">
          <v-select v-model="sort" :items="sortItems" label="Sort By" style="margin-left:10px" hide-details>
            <template v-slot:item="{item}">
              <v-icon v-if="item.value[1]" left>mdi-arrow-down</v-icon>
              <v-icon v-else left>mdi-arrow-up</v-icon>
              {{ item.text }}
            </template>
            <template v-slot:selection="{item}">
              <v-icon v-if="item.value[1]" left>mdi-arrow-down</v-icon>
              <v-icon v-else left>mdi-arrow-up</v-icon>
              {{ item.text }}
            </template>
          </v-select>
        </table-search-toolbar>
      </template>
      <template slot="item" slot-scope="props">
        <tr>
          <td>
            <addr :address="props.item.address" :name="props.item.name" :link="props.item.link"/>
          </td>
          <td style="text-align:center;padding:0">
            <stability :values="props.item.stability" :key="`stability-${props.item.id}`"/>
          </td>
          <td>
            <table class="depoolsList__list__infoTable">
              <tr>
                <td class="text-caption">Total Assets:</td>
                <td>
                  {{ utils.convertFromNano(props.item.stakes.total, 0) }}
                  <v-icon color="primary" small>mdi-diamond-stone</v-icon>
                </td>
              </tr>
              <tr>
                <td class="text-caption">Participants:</td>
                <td>{{ props.item.stakes.participantsNum }}</td>
              </tr>
              <tr>
                <td class="text-caption">Assurance:</td>
                <td>
                  {{ utils.convertFromNano(props.item.params.validatorAssurance) }}
                  <v-icon color="primary" small>mdi-diamond-stone</v-icon>
                </td>
              </tr>
              <tr>
                <td class="text-caption">Fee:</td>
                <td>{{ props.item.params.validatorRewardFraction }}%</td>
              </tr>
            </table>
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
import Stability from "@/components/Stability";

export default {
  components: {Stability, TableSearchToolbar, StakingDialog, Addr},
  data() {
    return {
      config: global.config,
      utils,
      search: '',
      items: [],
      loading: false,
      headers: [
        {text: 'Name/Address', value: 'address', align: 'start', sortable: false,},
        {value: 'name', align: ' d-none', sortable: false,},
        {value: 'stakes.total', align: ' d-none', sortable: true,},
        {text: 'Stability', align: 'center', sortable: false,},
        {text: 'Info', align: 'center', sortable: false,},
        {sortable: false},
      ],
      sort: null,
      sortItems: [
        {text: 'Total Assets', value: ['stakes.total', true]},
        {text: 'Total Assets', value: ['stakes.total', false]},
        {text: 'Participants', value: ['stakes.participantsNum', true]},
        {text: 'Participants', value: ['stakes.participantsNum', false]},
        {text: 'Fee', value: ['params.validatorRewardFraction', true]},
        {text: 'Fee', value: ['params.validatorRewardFraction', false]},
        {text: 'Assurance', value: ['params.validatorAssurance', true]},
        {text: 'Assurance', value: ['params.validatorAssurance', false]},
      ],
      dialogInstall: false,
      dialogStaked: false,
      stakingDepoolId: null,
      isStakingDialogOpening: false,
    }
  },
  created() {
    this.sort = this.sortItems[0].value;
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
    async loadItems() {
      this.loading = true;
      try {
        const response = await this.$http.get('/api/depools');
        this.items = response.body;
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
  }
}
</script>

<style lang="scss">
.depoolsList {
  &__list {
    .v-data-footer__select {
      visibility: hidden;
    }

    &__infoTable {
      margin: 0 auto;

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
