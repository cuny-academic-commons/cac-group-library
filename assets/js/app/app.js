import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import fetch from 'isomorphic-fetch'

import App from './App.vue'
import store from './store.js'
import router from './router.js'

//import VuejsDialogMixin from 'vuejs-dialog/dist/vuejs-dialog-mixin.min.js';
import VuejsDialog from 'vuejs-dialog';
import VTooltip from 'v-tooltip'

Vue.use(VuejsDialog);
Vue.use(VTooltip)

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
		router,
		store,
		render: h => h( App ),
	}
);
