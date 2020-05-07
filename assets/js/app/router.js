import Vue from 'vue'
import Router, { next } from 'vue-router'

import routes from './routes.js'
import store from './store.js'

Vue.use( Router )

const router = new Router({
	mode: 'hash',
	base: window.CACGroupLibrary.appUrlBase,
	routes
})

router.beforeEach( function(to, from, next) {
	switch ( to.name ) {
		case 'addNew' :
			if ( ! window.CACGroupLibrary.canCreateNew ) {
				next( { path: '/' } )
				return
			}
		break

		// @todo Not easy to do this because data is asynchronous. Will protect on server for now.
		case 'edit' :
		break
	}

	next()
})

export default router
