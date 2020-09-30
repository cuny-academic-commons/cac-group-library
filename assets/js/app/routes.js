import GroupLibrary from './views/GroupLibrary.vue'
import AddNew from './views/AddNew.vue'
import Edit from './views/Edit.vue'
import EditFolders from './views/EditFolders.vue'

export default [
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
	},
	{
		path: '/editFolders/',
		component: EditFolders,
		name: 'editFolders',
	}
]

