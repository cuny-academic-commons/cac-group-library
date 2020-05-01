import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import VTooltip from 'v-tooltip'
import fetch from 'isomorphic-fetch'

import App from './App.vue'
import router from './router.js'
import store from './store.js'

Vue.use(VTooltip)

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
		router,
		store,
		render: h => h( App ),
	}
);
