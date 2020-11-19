import Vue from 'vue'
import VueRouter from 'vue-router'
import HighLevelPage from "../views/HighLevelPage";
import NotFound from "../views/NotFound";
import Main from "../views/Main";

Vue.use(VueRouter);

let routes = [
  {
    path: '*',
    component: NotFound
  },
  {
    path: '/',
    component: HighLevelPage,
    children: [
      {path: '', name: 'main', component: Main},
    ],
  },
];

const router = new VueRouter({
  mode: 'history',
  base: '/',
  routes
});

export default router;
