import Vue from 'vue'
import Router, { next } from 'vue-router'

import store from './store.js'
import GroupLibrary from './views/GroupLibrary.vue'
import AddNew from './views/AddNew.vue'
import Edit from './views/Edit.vue'

Vue.use( Router )

const routes = [
	{
		path: '/',
		component: GroupLibrary,
		name: 'home'
	},
	{
		path: '/new/',
		component: AddNew,
		name: 'addNew',
	},
	{
		path: '/edit/:itemId',
		component: Edit,
		name: 'edit',
	}
]

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
