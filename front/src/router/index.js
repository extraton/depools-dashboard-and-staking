import Vue from 'vue'
import VueRouter from 'vue-router'
import HighLevelPage from "../views/HighLevelPage";
import NotFound from "../views/NotFound";
import Main from "../views/Main";
import MyStakes from "../views/MyStakes";
import SetName from "../views/SetName";
import About from "../views/About";

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
      {path: 'my-stakes', name: 'my-stakes', component: MyStakes},
      {path: 'set-name', name: 'set-name', component: SetName},
      {path: 'about', name: 'about', component: About},
    ],
  },
];

const router = new VueRouter({
  mode: 'history',
  base: '/',
  routes
});

export default router;
