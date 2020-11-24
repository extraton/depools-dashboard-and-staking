<template>
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-form v-if="depool" v-model="valid" ref="form">
      <v-card>
        <v-card-title>
          Stake
        </v-card-title>
        <v-card-text>
          <v-alert type="warning" border="left" outlined dense>
            Warning!
            <br/>The application is in beta testing stage.
            <br/>Available for testing purposes only.
          </v-alert>
          <v-simple-table>
            <template v-slot:default>
              <tbody>
              <tr>
                <td>Depool address</td>
                <td>
                  <addr :address="depool.address" :link="depool.link"/>
                </td>
              </tr>
              <tr>
                <td>Investor profit fee</td>
                <td>{{ depool.params.validatorRewardFraction }}%</td>
              </tr>
              <tr>
                <td>Assurance</td>
                <td>{{ utils.convertFromNano(depool.params.validatorAssurance, 0) }}</td>
              </tr>
              <tr>
                <td>Participants</td>
                <td>{{ depool.stakes.participantsNum }}</td>
              </tr>
              <tr>
                <td>Total Assets</td>
                <td>{{ utils.convertFromNano(depool.stakes.total, 0) }}</td>
              </tr>
              </tbody>
            </template>
          </v-simple-table>
          <v-text-field v-model="amount" type="number"
                        :label="`Crystals Amount (${minStakeCrystalView} minimum)`"
                        :rules="[rules.required, rules.integer, rules.greaterOrEqualMinimum, rules.lessOrEqualTestLimit]"
                        style="margin-top:15px"
                        outlined/>
          <div class="red--text">{{ error }}</div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn @click="dialog = false" :disabled="loading" text>cancel</v-btn>
          <v-spacer></v-spacer>
          <v-btn @click="stake" :disabled="!valid" :loading="loading" color="primary" text>stake</v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script>
import freeton from "freeton";
import utils from "@/utils";
import Addr from "@/components/Addr";
import BigNumber from 'bignumber.js';

let t = null;

export default {
  components: {Addr},
  props: {depool: Object},
  data: () => ({
    utils,
    dialog: false,
    valid: true,
    amount: 10,
    rules: {
      required: value => !!value || 'Required.',
      integer: value => new BigNumber(value).isInteger() || 'Integer only',
      greaterOrEqualMinimum(value) {
        return new BigNumber(value).isGreaterThanOrEqualTo(t.minStakeCrystalInt) || `Must be greater or equal ${t.minStakeCrystalView}.`
      },
      lessOrEqualTestLimit(value) {
        return new BigNumber(value).isLessThanOrEqualTo(new BigNumber(50)) || `Staking limited up to 50 crystals per time while beta testing.`
      },
    },
    error: '',
    loading: false,
  }),
  created() {
    t = this;
  },
  computed: {
    minStakeCrystalInt() {
      return utils.convertFromNanoToInt(this.depool.params.minStake, BigNumber.ROUND_CEIL);
    },
    minStakeCrystalView() {
      return this.minStakeCrystalInt.toFormat(0);
    },
  },
  watch: {
    depool: {
      handler(depool) {
        if (null !== depool) {
          this.amount = this.minStakeCrystalInt.toString();
        }
      },
      deep: true,
    }
  },
  methods: {
    open() {
      this.error = '';
      this.dialog = true;
    },
    async stake() {
      this.error = '';
      this.loading = true;
      try {
        if (!this.$refs.form.validate()) {
          return;
        }

        const provider = new freeton.providers.ExtensionProvider(window.freeton);
        const network = await provider.getNetwork();
        if (network.id !== 1) {
          this.error = 'Please, switch network to main.ton.dev in extraTON extension.';
          return;
        }
        await utils.sendTransactionToDepool(
          provider,
          this.depool.address,
          'addOrdinaryStake',
          {stake: utils.convertToNano(this.amount)},
          (BigInt(utils.convertToNano(this.amount)) + BigInt(utils.transactionAdditionalFee)).toString()
        );
        this.$emit('success');
        this.dialog = false;
      } catch (e) {
        console.error(e);
        if (e.code !== 1000/*Canceled by user*/) {
          this.error = undefined !== e.text ? e.text : 'Unknown error';
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
