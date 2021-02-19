<template>
  <v-app>
    <v-snackbar v-model="snack.isShow">{{ snack.text }}</v-snackbar>
    <div>
      <v-app-bar>
        <v-icon left>mdi-diamond-stone</v-icon>
        <v-toolbar-title>FreeTON Staking</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn href="https://t.me/extraton" target="_blank" text outlined>
          <v-icon left>mdi-telegram</v-icon>
          <span>Support</span>
        </v-btn>
        <template v-slot:extension>
          <v-tabs align-with-title>
            <v-tabs-slider color="#272727"/>
            <v-tab :to="{name: 'main'}" exact>Depools List</v-tab>
            <v-tab :to="{name: 'my-stakes'}">My Stakes</v-tab>
            <v-tab :to="{name: 'set-name'}">Set Depool Name</v-tab>
            <v-tab :to="{name: 'about'}">About</v-tab>
          </v-tabs>
        </template>
      </v-app-bar>
    </div>

    <div class="content">
      <v-alert type="error">
        Project Closed!
        <br/>You can use this tool to withdraw stakes from depools until 1st april 2021.
        <br/>After that you need to use tonos-cli application by ton-labs.
        <br/>See details in official <a href="https://extraton.io" target="_blank">site</a>.
      </v-alert>
      <router-view :search="search"/>
    </div>
  </v-app>
</template>

<script>
export default {
  name: 'App',

  components: {},

  data: () => ({
    config: global.config,
    search: '',
    snack: {isShow: false, text: ''},
  }),
  created() {
    this.config.totalNodes = '∞';
    this.$snack.listener = function (text) {
      this.snack.text = text;
      this.snack.isShow = false;
      this.snack.isShow = true;
    }.bind(this);
  },
};
</script>

<style>
.content {
  width: 85%;
  margin: 30px auto;
}

@media screen and (max-width: 1100px) {
  .content {
    width: 95%;
  }
}
</style>
