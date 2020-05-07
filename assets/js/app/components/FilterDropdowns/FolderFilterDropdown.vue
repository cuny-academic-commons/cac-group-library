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
							/*
							folders.push( {
								code: theItem.folders[ i ],
								value: theItem.folders[ i ],
							} )
							*/
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

				return folderObjects
			},

			getCurrentCallback() {
				const currentFolder = decodeURIComponent( this.$store.state.route.query.folder )
				return this.folders().filter( folder => currentFolder === folder.code )
			},

			getItem( itemId ) {
				return this.$store.state.libraryItems[ itemId ]
			},

			setCurrentCallback( payload ) {
				const newQuery = Object.assign( {}, this.$route.query, {
					folder: encodeURIComponent( payload.code ),
					page: 1
				} )

				this.$router.push( {
					path: '/',
					query: newQuery
				} )

				return
				this.$store.commit(
					'setCurrentFolder',
					{
						value: payload.code
					}
				)
			}
		}
	}
</script>
