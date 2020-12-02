<template>
  <div class="mainStat">
    <v-card>
      <v-card-title>Statistics</v-card-title>
      <v-card-text>
        <div class="mainStat__common">
          <div>
            <div>Depools:</div>
            <div>
              <template v-if="stat">
                {{ stat.depools.total }}
                <sup v-if="stat.depools.new!=0">
                  <template v-if="stat.depools.new>0">+</template>
                  <span>{{ stat.depools.new }}</span>
                </sup>
              </template>
              <v-skeleton-loader v-else type="heading" width="110"/>
            </div>
          </div>
          <div>
            <div>Members:</div>
            <div>
              <template v-if="stat">
                {{ stat.members.total }}
                <sup v-if="stat.members.new!=0">
                  <template v-if="stat.members.new>0">+</template>
                  <span>{{ stat.members.new }}</span>
                </sup>
              </template>
              <v-skeleton-loader v-else type="heading" width="110"/>
            </div>
          </div>
          <div>
            <div>Total Assets:</div>
            <div>
              <template v-if="stat">
                {{ utils.formatNumber(stat.assets.total) }}
                <v-icon color="primary" style="margin-bottom:3px">mdi-diamond-stone</v-icon>
                <sup v-if="stat.assets.new!=0" style="margin-left:3px">
                  <template v-if="stat.assets.new>0">+</template>
                  <span>{{ utils.formatNumber(stat.assets.new) }}</span>
                </sup>
              </template>
              <v-skeleton-loader v-else type="heading" width="225"/>
            </div>
          </div>
        </div>
      </v-card-text>
    </v-card>
    <v-spacer/>
    <v-card>
      <v-card-title>Annual Percentage Yield</v-card-title>
      <v-card-text>
        <div class="ct-chart"></div>
        <v-skeleton-loader v-if="!stat" type="image"/>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import Chartist from 'chartist';
import utils from "@/utils";

export default {
  props: {stat: Object},
  data() {
    return {
      utils,
    }
  },
  watch: {
    stat(val) {
      if (val) {
        // const data = {
        //   labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        //   series: [
        //     [5, 2, 4, 2, 0]
        //   ]
        // };
        const data = {
          labels: this.stat.apy.labels,
          series: [this.stat.apy.series]
        };
        const options = {
          // width: 300,
          height: 200
        };
        new Chartist.Line('.ct-chart', data, options);
      }
    }
  }
}
</script>

<style lang="scss">
.mainStat {
  margin: 0 auto;
  display: flex;
  > div.v-card {
    min-width: 460px;
    height:286px
  }

  @media screen and (max-width: 1050px) {
    flex-direction: column;
    > div:last-child {
      margin-top: 15px;
    }
  }

  .v-skeleton-loader__heading {
    width: 100%;
  }

  &__common {
    display: table;
    margin: 25px auto 0;
    color: #fff;
    font-size: 1.7rem;
    font-weight: 400;
    line-height: 2.5rem;

    > div {
      display: table-row;

      > div {
        display: table-cell;
      }

      > div:first-child {
        text-align: right;
        padding-right: 7px;
      }
    }

    sup {
      font-size: 50%;
      color: #2196f3;
    }
  }

  > .spacer {
    width: 15px;
  }

  > div {
    width: 100%;
  }
}
</style>
