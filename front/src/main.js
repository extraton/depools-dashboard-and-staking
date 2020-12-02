import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify';
import VueResource from 'vue-resource';
import VueClipboards from "vue-clipboards";
import 'roboto-fontface/css/roboto/roboto-fontface.css'
import '@mdi/font/css/materialdesignicons.css'
import "./scss/chartist.scss";

let snack = {
  install(Vue) {
    Vue.prototype.$snack = {
      listener: null,
      success(data) {
        if (null !== this.listener) {
          this.listener(data.text);
        }
      },
      danger(data) {
        return this.success(data);
      }
    }
  }
}

Vue.use(VueResource);
Vue.use(VueClipboards);
Vue.use(snack);

Vue.config.productionTip = false

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
