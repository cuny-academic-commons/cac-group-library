<template>
	<FilterDropdown
		name='folder'
		title='Select Folder'
		:opts="folders()"
		:getCurrentCallback="getCurrentCallback"
		:setCurrentCallback="setCurrentCallback"
	/>
</template>

<script>
	import FilterDropdown from './FilterDropdown.vue'

	export default {
		components: {
			FilterDropdown
		},

		computed: {
			canEditFolders() {
				return this.$store.state.canEditFolders
			},

			foldersOfGroup() {
				return this.$store.state.foldersOfGroup
			},

			itemIds() {
				return this.$store.state.libraryItemIds
			},
		},

		methods: {
			folders() {
				const { itemIds, getItem } = this
				const vm = this

				let folders = []

				itemIds.map(
					function( itemId ) {
						const theItem = getItem( itemId )
						for ( var i in theItem.folders ) {
							folders.push( theItem.folders[ i ] )
						}
					}
				)

				let uniqueFolders = folders.filter(
					function( value, index, self ) {
						return self.indexOf( value ) === index
					}
				)

				uniqueFolders.sort(
					function( a, b ) {
						return a.localeCompare( b )
					}
				)

				let folderObjects = uniqueFolders.map(
					function( folder ) {
						return {
							code: folder,
							label: folder,
						}
					}
				)

				const anyFolder = { code: 'any', label: 'Any folder' }
				folderObjects.unshift( anyFolder )

				if ( this.canEditFolders ) {
					const editFolders = { code: '_edit', label: 'Edit folders' }
					folderObjects.push( editFolders )
				}

				return folderObjects
			},

			getCurrentCallback() {
				const currentFolder = this.$store.state.route.query.hasOwnProperty( 'folder' ) ? decodeURIComponent( this.$store.state.route.query.folder ) : 'any'
				return this.folders().filter( folder => currentFolder === folder.code )
			},

			getItem( itemId ) {
				return this.$store.state.libraryItems[ itemId ]
			},

			setCurrentCallback( payload ) {
				if ( '_edit' === payload.code ) {
					this.$router.push( {
						path: '/editFolders'
					} )
				} else {
					const newQuery = Object.assign( {}, this.$route.query, {
						folder: encodeURIComponent( payload.code ),
						page: 1
					} )

					this.$router.push( {
						path: '/',
						query: newQuery
					} )
				}
			}
		}
	}
</script>
