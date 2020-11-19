import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify';
import VueResource from 'vue-resource';
import VueClipboards from "vue-clipboards";
import Ewll from './../Main';
import 'roboto-fontface/css/roboto/roboto-fontface.css'
import '@mdi/font/css/materialdesignicons.css'

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
Vue.use(Ewll);

Vue.config.productionTip = false

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
