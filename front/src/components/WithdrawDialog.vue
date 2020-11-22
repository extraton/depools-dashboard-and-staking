<template>
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-form v-if="depool" v-model="valid" ref="form">
      <v-card>
        <v-card-title>
          Withdraw
        </v-card-title>
        <v-card-text>
          <v-alert type="warning" border="left" outlined dense>
            Attention!
            <br/>Withdraw takes up to 50 hours.
          </v-alert>
          <v-simple-table>
            <template v-slot:default>
              <tbody>
              <tr>
                <td>Depool address</td>
                <td>
                  <addr :address="depool.address"/>
                </td>
              </tr>
              <tr>
                <td>Available amount</td>
                <td>{{ availableCrystal }}</td>
              </tr>
              </tbody>
            </template>
          </v-simple-table>
          <v-checkbox v-model="isWithdrawAll" label="Withdraw all"/>
          <v-text-field v-model="amount" type="number"
                        :label="`Crystals Amount`"
                        :rules="amountRules"
                        :disabled="isWithdrawAll"
                        style="margin-top:15px"
                        outlined/>
          <div class="red--text">{{ error }}</div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-btn @click="dialog = false" :disabled="loading" text>cancel</v-btn>
          <v-spacer></v-spacer>
          <v-btn @click="withdraw" :disabled="!valid" :loading="loading" color="primary" text>withdraw</v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script>
import freeton from "freeton";
import utils from "@/utils";
import Addr from "@/components/Addr";

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
      integer: value => Number.isInteger(value - 0) || 'Integer only',
      moreZero(value) {
        return value - 0 > 0 || `Must be greater then zero.`
      },
      lessOrEqualAvailable(value) {
        return value - 0 <= t.availableCrystalNoFormat || `Must be greater or equal ${t.availableCrystal}.`
      },
    },
    error: '',
    loading: false,
    isWithdrawAll: false,
  }),
  created() {
    t = this;
  },
  computed: {
    availableCrystal() {
      return utils.convertFromNano(this.depool.stakes.my.total);
    },
    availableCrystalNoFormat() {
      return utils.convertFromNano(this.depool.stakes.my.total, true);
    },
    amountRules() {
      return this.isWithdrawAll ? [] : [this.rules.required, this.rules.integer, this.rules.moreZero, this.rules.lessOrEqualAvailable];
    },
  },
  methods: {
    open() {
      this.error = '';
      this.dialog = true;
    },
    async withdraw() {
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
          'withdrawPart',
          {withdrawValue: utils.convertToNano(this.amount)},
          utils.transactionAdditionalFee
        );
        this.$emit('success');
        this.dialog = false;
      } catch (e) {
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
