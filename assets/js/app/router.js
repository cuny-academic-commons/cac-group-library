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

			store.commit( 'resetForms' )
		break

		case 'editFolders' :
			if ( ! window.CACGroupLibrary.canEditFolders ) {
				next( { path: '/' } )
				return
			}
		break
	}

	next()
})

export default router
