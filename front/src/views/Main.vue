<template>
  <div class="companyList">
    <v-data-table
        :headers="headers"
        :items="items"
        :mobile-breakpoint="100"
        hide-default-footer
        :loading="listing.loading"
        :items-per-page="10000"
        :search="search"
        :sort-by="['stakes.total']"
        :sort-desc="[true]"
    >
      <template v-slot:top>
      </template>
      <template slot="item" slot-scope="props">
        <tr>
          <td>
            <span class="text-overline">
              {{ props.item.address.substr(0, 8) }}...{{ props.item.address.substr(-6) }}
            </span>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn v-bind="attrs"
                       v-on="on"
                       v-clipboard="props.item.address"
                       @click="$snack.success({text: 'Copied'})"
                       icon small
                >
                  <v-icon small>mdi-content-copy</v-icon>
                </v-btn>
              </template>
              <span>Copy address</span>
            </v-tooltip>
          </td>
          <td style="text-align:center">{{ props.item.params.validatorRewardFraction }}%</td>
          <td style="text-align:center">{{ props.item.stakes.participantsNum }}</td>
          <td style="text-align:center">
            <span>{{ convertFromNano(props.item.stakes.total) }}</span>
            <v-icon color="primary" right small>mdi-diamond-stone</v-icon>
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
</template>

<script>

export default {
  components: {},
  data() {
    return {
      config: global.config,
      search: '',
      items: [],
      listing: null,
      headers: [
        {text: 'Address', value: 'address', align: 'start', sortable: false,},
        {text: 'Investor Fee', value: 'params.validatorRewardFraction', align: 'center', sortable: true,},
        {text: 'Participants', value: 'stakes.participantsNum', align: 'center', sortable: true,},
        {text: 'Total Stake', value: 'stakes.total', align: 'center', sortable: true},
      ],
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
    convertFromNano(amountNano) {
      const amountBigInt = BigInt(amountNano);
      const integer = amountBigInt / BigInt('1000000000');
      return integer.toLocaleString();
    },
  }
}
</script>

<style lang="scss">
</style>
