import Vue from 'vue'
import Router from 'vue-router'

import GroupLibrary from './views/GroupLibrary.vue'
import AddNew from './views/AddNew.vue'

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
		name: 'addNew'
	}
]

export default new Router({
	mode: 'hash',
	base: window.CACGroupLibrary.appUrlBase,
	routes
})
