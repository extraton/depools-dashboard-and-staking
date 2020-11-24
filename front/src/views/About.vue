<template>
  <v-card class="about">
    <v-card-title>About</v-card-title>
    <v-card-text>
      <div>
        FreeTON Depool Dashboard and Staking is decentralized application (DApp) made by
        <a href="https://extraton.io" target="_blank">extraton.io</a> team.
        <br/>The DApp helps to stake safely in FreeTON network.
        <br/>It's non-commercial, open-source project.
        <br/>In order to use DApp install
        <a href="https://chrome.google.com/webstore/detail/extraton/hhimbkmlnofjdajamcojlcmgialocllm" target="_blank">
          extraTON extension
        </a> to your Google Chrome browser.
        <br/>Currently it's in a beta-testing mode and staking is limited to 50 crystals per one time.
      </div>

      <div class="about__howToUse">
        <h3>How to use</h3>
        See video on youtube.
      </div>

      <div class="about__faq">
        <h3>FAQ</h3>
        <p class="font-italic about__faq__question">Why every request require additional 0.5 crystals?</p>
        <p>Depool needs this amount for internal transactions, but usually 80% of this amount will be refunded right
          after request.</p>

        <p class="font-italic about__faq__question">What is ticktock?</p>
        <p>
          Ticktock is special request to depool makes internal updates.
          <br/>Usually the depool's owner request it when necessary, but sometimes it can help you get your money back
          faster.
        </p>

        <p class="font-italic about__faq__question">What can i do if this DApp will disappear? Will i lose my
          assets?</p>
        <p>
          Staking through this DApp isn't locking you to it. At any time you can use tonos-cli or other tool to manage
          your stakes.
        </p>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {}
</script>

<style lang="scss">
.about {
  .v-card__text > div:not(:first-child) {
    margin-top: 20px;
  }

  &__howToUse {
    display: none;
  }

  &__faq {
    h3 {
      margin-bottom: 10px;
    }

    &__question {
      margin-left: 40px;
    }
  }
}
</style>
