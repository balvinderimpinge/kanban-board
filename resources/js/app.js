require('./bootstrap');

import Vue from 'vue';
import VModal from 'vue-js-modal';

Vue.use(VModal);
Vue.component('home-board', require('./components/Board.vue').default);

const app = new Vue({
    el: "#app"
});
