import Vue from 'vue'
import Router, { next } from 'vue-router'

import GroupLibrary from './views/GroupLibrary.vue'
import AddNew from './views/AddNew.vue'


Vue.use( Router )

function requireAuth() {
}

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
	}
]

const router = new Router({
	mode: 'hash',
	base: window.CACGroupLibrary.appUrlBase,
	routes
})

router.beforeEach( function(to, from, next) {
	if ( to.name === 'addNew' ) {
	console.log(to.name)
	console.log( window.CACGroupLibrary.canCreateNew )
		if ( ! window.CACGroupLibrary.canCreateNew ) {
			next( { path: '/' } )
			return
		}
	}

	next()
})

export default router
