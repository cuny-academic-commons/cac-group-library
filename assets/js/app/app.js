import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import fetch from 'isomorphic-fetch'

import App from './App.vue'
import store from './store.js'
import router from './router.js'
import { sync } from 'vuex-router-sync'

const unsync = sync(store, router)

import VuejsDialog from 'vuejs-dialog'
import VTooltip from 'v-tooltip'
import VueMq from 'vue-mq'

Vue.use(VuejsDialog);
Vue.use(VTooltip)
Vue.use(VueMq, {
	breakpoints: {
		mobile: 600,
		desktop: Infinity
	}
})

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
		router,
		store,
		render: h => h( App ),
	}
);
