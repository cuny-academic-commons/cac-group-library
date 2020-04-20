import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import fetch from 'isomorphic-fetch'

import App from './App.vue'
import router from './router.js'
import store from './store.js'

if ( ! global._babelPolyfill ) {
	//  require( '@babel/polyfill' )
}

window.cacGroupLibrary = new Vue(
	{
		el: '#cac-group-library',
		router,
		store,
		render: h => h( App ),
	}
);
