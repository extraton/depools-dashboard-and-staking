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
