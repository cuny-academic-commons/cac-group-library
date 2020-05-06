import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import VTooltip from 'v-tooltip'
import fetch from 'isomorphic-fetch'

import App from './App.vue'
import store from './store.js'
import router from './router.js'

Vue.use(VTooltip)

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
		router,
		store,
		render: h => h( App ),
	}
);
